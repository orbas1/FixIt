<?php

namespace App\Http\Controllers\Frontend;

use Exception;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\Setting;
use Kreait\Firebase\Factory;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    protected $auth;

    protected $role;

    public function __construct()
    {
        $settings = Setting::first();

        $firebaseCredentials = $settings->values['firebase']['service_json'] ?? null;
        if (is_array($firebaseCredentials)) {
            $firebase = (new Factory)
            ->withServiceAccount($firebaseCredentials)
            ->createAuth();

            $this->auth = $firebase;
        $googleCredentials = $settings->values['social_login'] ?? null;

        if ($googleCredentials && is_array($googleCredentials)) {
            config([
                'services.google.client_id' => $googleCredentials['client_id'] ?? '',
                'services.google.client_secret' => $googleCredentials['client_secret'] ?? '',
                'services.google.redirect' => $googleCredentials['redirect_url'] ?? ''
            ]);
        }
        }
    }

    public function redirectToProvider($provider)
    {
        $validProviders = ['google'];

        if (!in_array($provider, $validProviders)) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        $googleCredentials = Setting::first()->values['social_login'] ?? null;

        $redirectUrl = $googleCredentials['redirect_url'] ?? null;

        if (!$redirectUrl) {
            return response()->json(['error' => 'Redirect URL not found in settings'], 400);
        }

        return Socialite::driver($provider)->redirect();
    }


    public function handleProviderCallback($provider)
    {
        try {
            // Use stateless() if the application does not manage sessions.
            $socialUser = Socialite::driver($provider)->stateless()->user();

            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'status' => true,
                ]);

                $role = Role::where('name', RoleEnum::CONSUMER)->first();
                if ($role) {
                    $user->assignRole($role);
                }
            }

            // Log the user in
            auth()->login($user);

            try {
                $uid = (string) $user->id;
                $customToken = $this->auth->createCustomToken($uid);
                $tokenString = $customToken->toString();

                $firebaseUser = $this->auth->signInWithCustomToken($tokenString);

                return redirect()->route('frontend.home')->with([
                    'idToken' => $firebaseUser->idToken(),
                    'refreshToken' => $firebaseUser->refreshToken(),
                ]);
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return redirect()->route('login')->withErrors(['msg' => 'Invalid State. Please try again.']);
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors(['msg' => 'Login failed. Please try again.']);
        }
    }
}

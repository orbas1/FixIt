<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\Helpers;
use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use App\Events\CreateOtpEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Frontend\LoginRequest;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('frontend.auth.login');
    }

    public function verifyLogin($request)
    {
        return User::where([['email', $request->email], ['status', true]])->first();
    }

    public function login(LoginRequest $request)
    {
        $user = $this->verifyLogin($request);
        if (!$user) {
            return redirect()?->back()?->with(['error' => 'There is no account linked to the given email.']);
        }

        if (!$user->status) {
            return redirect()?->back()?->with(['error' =>  "You cannot log in with a disabled account."]);
        }

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            if ($user?->role?->name != 'user') {
                return redirect()?->back()?->with(['error' => 'Login allowed only for users role.']);
            }

            return to_route('frontend.home');
        }

        return redirect()?->back()?->with(['error' => 'The entered credentials are incorrect, Please try Again!']);
    }

    public function forgot()
    {
        return view('frontend.auth.forgot');
    }

    public function sendOTP(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
        ]);

        $input = $request->input('email_or_phone');
        if ($this->filterEmailValidate($input)) {
            $user = User::where('email', $input)->whereNull('deleted_at')->first();
            $email = $input;
        } else {
            $user = User::where('phone', $input)->whereNull('deleted_at')->first();
            $phone = $input;
        }

        if (!$user) {
            return redirect()->back()->with(['error' => 'There is no account linked to the given details.']);
        }

        if (!$user->status) {
            return redirect()?->back()?->with(['error' =>  "You cannot log in with a disabled account."]);
        }

        if ($user?->role?->name != 'user') {
            return redirect()?->back()?->with(['error' => 'Forgot allowed only for users role.']);
        }

        $otp = $this->generateOtp($input);
        if (isset($email)) {
            Mail::to($email)->send(new ForgotPassword($otp));
        }

        session()->put('email_or_phone', $email ?? $phone);

        return to_route('frontend.confirm.index');
    }

    public function filterEmailValidate($input)
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    public function generateOtp($input)
    {
        $email = null;
        $phone = null;
        if ($this->filterEmailValidate($input)) {
            $email = $input;
        } else {
            $phone = $input;
        }

        $token = hash_hmac('sha256', Str::random(40), "my hash string");
        $otp = rand(111111, 999999);
        DB::table('password_resets')->insert([
            'email' => $email,
            'otp' => $otp,
            'token'=> $token,
            'phone' => $phone,
            'created_at' => Carbon::now(),
        ]);

        return $otp;
    }
    public function confirm()
    {
        $token=DB::table('password_resets')->where('email', session( 'email_or_phone'))->value('token');
        return view('frontend.auth.confirm',['token'=>$token]);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|string',
        ]);

        $password_resets = DB::table('password_resets')
            ->where('otp', $request->otp)
            ->where('created_at', '>', Carbon::now()->subHours(1))->first();

        if ($password_resets) {
            if ($this->filterEmailValidate(session('email_or_phone'))) {
                if ($password_resets->email != session('email_or_phone')) {
                    return redirect()->back()->with(['error' => 'Invaild OTP for provided email.']);
                }
            } else {
                if ($password_resets->phone != session('email_or_phone')) {
                    return redirect()->back()->with(['error' => 'Invaild OTP for provided phone.']);
                }
            }

            $token = $password_resets->token;
            return view('frontend.auth.reset', ['password_resets' => $password_resets, 'token' => $token]);

        }

        return redirect()->back()->with(['error' => 'Invaild OTP']);
    }

    public function resetPassword(Request $request)
    {
        $token=DB::table('password_resets')->where('email', session( 'email_or_phone'))->value('token');
        DB::table('password_resets')->where('token', $token)->delete();

        $user=User::where('email', session( 'email_or_phone'))->first();
        $user->update([
            'password'=> Hash::make($request->password),
        ]);

        return to_route('frontend.login.index');
    }

    public function checkLogin(Request $request)
    {
        if (auth()->check()) {
            return response()->json(['logged_in' => true]);
        } else {
            return response()->json(['logged_in' => false]);
        }
    }

    public function loginWithNumber()
    {

        $defaultSmsGateway = Helpers::getDefaultSMSGateway();
        if($defaultSmsGateway === 'firebase') {
            return view('frontend.auth.firebase-login-with-number');
        }

        return view('frontend.auth.login-with-number');
    }
    public function createUserOrOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::where('phone', $request->phone)->first();
        $otp = rand(111111, 999999);
        event(new CreateOtpEvent($request->phone, $otp));
        if ($user) {
            DB::table('password_resets')->insert([
                'email' => $user->email,
                'otp' => $otp,
                'phone' => $request->phone,
                'created_at' => Carbon::now(),
            ]);
            return redirect()->route('frontend.login.otp', ['phone' => $request->phone, 'code' => $request->code]);
        } else {
            $user = User::create([
                'phone' => $request->phone,
                'code' => $request->code,
                'status' => true,
            ]);
            DB::table('password_resets')->insert([
                'email' => null,
                'otp' => $otp,
                'phone' => $request->phone,
                'created_at' => Carbon::now(),
            ]);
            return redirect()->route('frontend.login.otp', ['phone' => $request->phone, 'code' => $request->code]);
        }
    }

    public function showOtp(Request $request)
    {
        if (!$request->has('phone') || !$request->has('code')) {
            return redirect()->route('login.mobile')->withErrors(['phone' => 'Phone number is required.']);
        }

        return view('frontend.auth.otp', ['phone' => $request->phone, 'code' => $request->code]);
    }

    public function verifyOtpNumber(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'otp' => 'required|integer',
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $passwordReset = DB::table('password_resets')
        ->where('phone', $request->phone)
        ->where('otp', $request->otp)
        ->where('created_at', '>', Carbon::now()->subHours(1))
        ->first();

        if ($passwordReset) {
            $user = User::where('phone', $request->phone)->first();

            if ($user) {
                Auth::login($user);
                DB::table('password_resets')->where('phone', $request->phone)->delete();
                return redirect()->route('frontend.account.profile.index');
            }
        }
        return redirect()->back()->withErrors(['otp' => 'Invalid OTP'])->withInput();
    }

    public function verifyFirebaseOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 400);
        }

        try {
            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                $user = User::create([
                    'phone' => $request->phone,
                    'code' => $request->code,
                ]);
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'redirect' => route('frontend.account.profile.index')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function normalizePhoneNumber(string $internationalNumber): array
    {
        $cleaned = preg_replace('/[^0-9]/', '', $internationalNumber);

        $countryCode = '';
        $localNumber = $cleaned;

        if (strpos($cleaned, '1') === 0) {
            $countryCode = substr($cleaned, 0, 1);
            $localNumber = substr($cleaned, 1);
        } elseif (strpos($cleaned, '44') === 0) {
            $countryCode = substr($cleaned, 0, 2);
            $localNumber = substr($cleaned, 2);
        } elseif (strpos($cleaned, '91') === 0) {
            $countryCode = substr($cleaned, 0, 2);
            $localNumber = substr($cleaned, 2);
        }

        return [
            'full' => '+' . $cleaned,
            'local' => $localNumber,
            'country_code' => $countryCode
        ];
    }
}

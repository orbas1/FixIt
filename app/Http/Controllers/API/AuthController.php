<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\Address;
use App\Models\Company;
use App\Enums\RoleEnum;
use App\Events\CreateUserEvent;
use App\Events\CreateProviderEvent;
use App\Helpers\Helpers;
use App\Mail\ForgotPassword;
use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\SocialLoginRequest;
use App\Models\ProviderWallet;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|required|min:6|unique:users',
                'code' => 'required',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|',
                'fcm_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'fcm_token' => $request->fcm_token,
                'password' => Hash::make($request->password),
                'code' => $request->code,
                'phone' => $request->phone,
                'status' => true,
            ]);

            $user->assignRole(RoleEnum::CONSUMER);
            event(new CreateUserEvent($user));

            DB::commit();

            return [
                'access_token' => $user->createToken('auth_token')->plainTextToken,
                'success' => true,
            ];
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    // public function registerProvider(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $company = null;
    //         $validator = Validator::make($request->all(), [
    //             'type' => 'required|string',
    //             'name' => 'required|string|max:255',
    //             'email' => ['required','string','email', Rule::unique('users', 'email')->whereNull('deleted_at')],
    //             'phone' => ['required','numeric',Rule::unique('users', 'phone')->whereNull('deleted_at')],
    //             'code' => 'required',
    //             'password' => 'required|string|min:8|confirmed',
    //             'password_confirmation' => 'required',
    //             'experience_interval' => 'required|string',
    //             'experience_duration' => 'required|integer',
    //             'known_languages' => 'required',
    //             'company_name' => 'required_if:type,company|string|max:25',
    //             'company_email' => ['required_if:type,company','email', Rule::unique('companies', 'email')->whereNull('deleted_at')],
    //             'company_phone' => ['required_if:type,company','numeric',Rule::unique('companies', 'phone')->whereNull('deleted_at')],
    //             'company_code' => 'required_if:type,company',
    //             'company_address.latitude' => 'required_if:type,company|string',
    //             'company_address.longitude' => 'required_if:type,company|string',
    //             'company_address.address' => 'required_if:type,company|string',
    //             'company_address.area' => 'required_if:type,company|string',
    //             'company_address.country_id' => 'required_if:type,company|string',
    //             'company_address.state_id' => 'required_if:type,company|string',
    //             'company_address.city' => 'required_if:type,company|string',
    //             'company_address.postal_code' => 'required_if:type,company|string',
    //             'company_address.is_primary' => 'required_if:type,company|string',
    //             'zoneIds*' => 'nullable,required,exists:zones,id,deleted_at,NULL',
    //         ]);

    //         if ($validator->fails()) {
    //             throw new Exception($validator->messages()->first(), 422);
    //         }

    //         if ($request->type === 'company') {
    //             $company = Company::create([
    //                 'name' => $request->company_name,
    //                 'email' => $request->company_email,
    //                 'phone' => $request->company_phone,
    //                 'code' => $request->company_code,
    //                 'description' => $request->description,
    //             ]);

    //             if ($request->hasFile('company_logo')) {
    //                 $companyLogo = $request->file('company_logo');
    //                 $company->addMedia($companyLogo)->toMediaCollection('company_logo');
    //             }
    //         }

    //         $provider = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'fcm_token' => $request->fcm_token,
    //             'password' => Hash::make($request->password),
    //             'code' => $request->code,
    //             'phone' => $request->phone,
    //             'experience_interval' => $request->experience_interval,
    //             'experience_duration' => $request->experience_duration,
    //             'status' => true,
    //             'company_id' => $company->id ?? null,
    //             'type' => $request->type,
    //         ]);
    //         $provider->assignRole(RoleEnum::PROVIDER);

    //         if (isset($request->known_languages)) {
    //             $provider->knownLanguages()->sync($request->known_languages);
    //         }

    //         $providerWallet = ProviderWallet::firstOrCreate(['provider_id' => $provider->id]);
    //         if ($request->type === 'company' || $request->type === 'freelancer') {
    //             $userDocument = UserDocument::create([
    //                 'user_id' => $provider->id,
    //                 'document_id' => $request->document_id,
    //                 'status' => 'pending',
    //                 'identity_no' => $request->identity_no,
    //                 'notes' => $request->notes ?? '',
    //             ]);

    //             if ($request->document_images) {
    //                 $images = $request->file('document_images');
    //                 foreach ($images as $image) {
    //                     $userDocument->addMedia($image)->toMediaCollection('document_images');
    //                 }
    //                 $userDocument->media;
    //             }
    //         }

    //         if ($request->company_address) {
    //             $address = Address::create([
    //                 'company_id' => $company->id,
    //                 'latitude' => $request->company_address['latitude'],
    //                 'longitude' => $request->company_address['longitude'],
    //                 'address' => $request->company_address['address'],
    //                 'area' => $request->company_address['area'],
    //                 'country_id' => $request->company_address['country_id'],
    //                 'state_id' => $request->company_address['state_id'],
    //                 'city' => $request->company_address['city'],
    //                 'postal_code' => $request->company_address['postal_code'],
    //                 'is_primary' => $request->company_address['is_primary'],
    //             ]);
    //         }

    //         if (isset($request->zoneIds)) {
    //             $provider->zones()->attach($request->zoneIds);
    //             $provider->zones;
    //         }

    //         if (isset($request->known_languages)) {
    //             $provider->knownLanguages()->sync($request->known_languages);
    //         }

    //         DB::commit();
    //         return [
    //             'access_token' => $provider->createToken('auth_token')->plainTextToken,
    //             'type' => $request->type,
    //             'success' => true,
    //         ];
    //     } catch (Exception $e) {

    //         DB::rollback();
    //         throw new ExceptionHandler($e->getMessage(), $e->getCode());
    //     }
    // }
    public function registerProvider(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string',
                'name' => 'required|string|max:255',
                'email' => ['required','string','email', Rule::unique('users', 'email')->whereNull('deleted_at')],
                'phone' => ['required','numeric', Rule::unique('users', 'phone')->whereNull('deleted_at')],
                'role' => 'required|in:provider,serviceman',
                'code' => 'required',
                'provider_id' => 'required_if:role,serviceman|exists:users,id,deleted_at,NULL',
                'zoneIds*' => 'required_if:role,provider,exists:zones,id,deleted_at,NULL',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $provider = User::create([
                'type' => $request->type,
                'name' => $request->name,
                'email' => $request->email,
                'fcm_token' => $request->fcm_token,
                'password' => Hash::make($request->password),
                'code' => $request->code,
                'phone' => $request->phone,
                'provider_id' => $request->provider_id,
                'status' => true,
            ]);

            if($request->role == RoleEnum::PROVIDER){
                $provider->assignRole(RoleEnum::PROVIDER);
            } else {
                $provider->assignRole(RoleEnum::SERVICEMAN);
            }

            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $provider->addMedia($profileImage)->toMediaCollection('profile_image');
            }

            if (isset($request->zoneIds)) {
                $provider->zones()->attach($request->zoneIds);
                $provider->zones;
            }

            DB::commit();
            event(new CreateProviderEvent($provider));
            return [
                'access_token' => $provider->createToken('auth_token')->plainTextToken,
                'type' => $request->type,
                'success' => true,
            ];
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function login(Request $request)
    {
        try {
            $user = $this->verifyLogin($request);

            if (!Hash::check($request->password, $user->password)) {
                throw new Exception(__('passwords.incorrect_password'), 400);
            }

            if ($user->hasMfaEnabled()) {
                $challenge = $user->startMfaChallenge([
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                    'fcm_token' => $request->fcm_token,
                ]);

                return response()->json([
                    'success' => true,
                    'requires_mfa' => true,
                    'challenge_id' => $challenge->getKey(),
                    'expires_at' => optional($challenge->expires_at)->toIso8601String(),
                    'methods' => ['totp', 'recovery_code'],
                    'recovery_codes_remaining' => $user->availableRecoveryCodesCount(),
                    'message' => __('Two-factor authentication required.'),
                ], 202);
            }

            if ($request->fcm_token) {
                $user->forceFill(['fcm_token' => $request->fcm_token])->save();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'access_token' => $token,
                'success' => true,
            ];

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }


    public function socialLogin(SocialLoginRequest $request)
    {
        $loginMethod = $request->input('login_type');
        $user = (object) $request->input('user');

        DB::beginTransaction();
        try {
            $user = $this->createOrGetUser($loginMethod, $user);
            if ($request->fcm_token) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }

            DB::commit();

            if ($user->status) {
                return response()->json([
                    'success' => true,
                    'access_token' => $user->createToken('Sanctom')->plainTextToken,
                ], 200);
            }

            throw new Exception(__('auth.user_deactivated'), 403);
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    private function createOrGetUser($loginMethod, $user)
    {
        if ($loginMethod === 'phone') {
            $phone = $user->phone;
            $code = $user->code;

            $existingUser = User::where('phone', $phone)->first();

            if ($existingUser) {
                return $existingUser;
            }

            $newUser = User::create([
                'status' => true,
                'phone' => $phone,
                'code' => $code,
            ]);

        } else {
            $email = $user->email;
            $name = $user->name;

            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                return $existingUser;
            }

            $newUser = User::create([
                'status' => true,
                'email' => $email ?? null,
                'name' => $name ?? null,
            ]);
        }

        $userRole = Role::where('name', RoleEnum::CONSUMER)->first();
        if ($userRole) {
            $newUser->assignRole($userRole);
        }

        return $newUser;

    }

    public function verifyLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->messages()->first(), 422);
        }

        $user = User::where([['email', $request->email], ['status', true]])->first();
        
        if (!$user) {
            throw new Exception(__('validation.user_not_exists'), 400);
        }

        return $user;
    }

    public function logout(Request $request)
    {
        try {

            $token = PersonalAccessToken::findToken($request->bearerToken());
            if (!$token) {
                throw new Exception(__('auth.token_invalid'), 400);
            }

            return [
                'message' => __('auth.logged_out'),
                'success' => true,
            ];
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function forgotPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'email|exists:users',
                'phone' => 'exists:users',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $otp = $this->generateOtp($request);
            if (isset($request->email)) {
                Mail::to($request->email)->send(new ForgotPassword($otp));
            }

            return [
                'message' => __('auth.sent_verification_code_msg'),
                'success' => true,
            ];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'dial_code' => 'required',
                'phone' => 'required'
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $otp = rand(111111, 999999);
            $sendTo = ('+'.$request->dial_code.$request->phone);
            $message = "This is your otp:$otp";

            $sendOTP = Helpers::sendSMS($sendTo, $message);
            if(isset($sendOTP->account_sid)){
                DB::table('password_resets')->insert([
                    'otp' => $otp,
                    'phone' => $request->phone,
                    'created_at' => Carbon::now(),
                ]);
                return [
                    'message' => __('auth.otp_sent'),
                    'success' => true,
                ];
            } else {
                return [
                    'message' => $sendOTP->message ?? "Invalid Response",
                    'success' => false,
                ];
            }


        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifySendOtp(Request $request)
    {
        try {
            if(Helpers::getDefaultSMSGateway() !== 'firebase') {
                $validator = Validator::make($request->all(), [
                    'otp' => 'required',
                    'phone' => 'required',
                ]);

                if ($validator->fails()) {
                    throw new Exception($validator->messages()->first(), 422);
                }

                $verify = DB::table('password_resets')
                    ->where('otp', $request->otp)
                    ->where('phone', $request->phone)
                    ->where('created_at', '>', Carbon::now()->subHours(1))
                    ->first();

                if (!$verify) {
                    throw new Exception(__('auth.invalid_otp_or_phone'), 400);
                }

                $user = User::firstOrCreate(['phone' => $verify->phone], [
                    'email' => $verify->email,
                    'code' => $request->code,
                    'status' => true,
                ]);

                if (!$user) {
                    throw new Exception(__('auth.user_not_exists'), 404);
                }

                if (!$user->status) {
                    throw new Exception(__('auth.user_inactive'), 400);
                }

                DB::table('password_resets')->where('otp', $request->otp)
                    ->where(function ($query) use ($request) {
                        $query->where('phone', $request->phone);
                    })
                    ->where('created_at', '>', Carbon::now()->subHours(1))
                    ->delete();

                DB::commit();

                return [
                    'access_token' => $user->createToken('auth_token')->plainTextToken,
                    'user' => $user,
                    'success' => true,
                ];

            } else {
                $validator = Validator::make($request->all(), [
                    'phone' => 'required|string',
                    'firebase_uid' => 'required|string',
                ]);

                if ($validator->fails()) {
                    throw new Exception($validator->messages()->first(), 422);
                }


                    $user = User::where('phone', $request->phone)->first();
                    if (!$user) {
                        throw new Exception(__('auth.user_not_exists'), 404);
                    }

                    if (empty($user->firebase_uid)) {
                        $user->firebase_uid = $request->firebase_uid;
                        $user->save();
                    }

                    return [
                        'access_token' => $user->createToken('auth_token')->plainTextToken,
                        'user' => $user,
                        'success' => true,
                    ];
            }


        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function generateOtp($request)
    {
        $otp = rand(111111, 999999);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'otp' => $otp,
            'phone' => $request->phone,
            'created_at' => Carbon::now(),
        ]);

        return $otp;
    }

    public function updatePassword(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'otp' => 'required',
                'email' => 'required|email|max:255|exists:users',
                'password' => 'required|min:8',
                'password_confirmation' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = DB::table('password_resets')
                ->where('otp', $request->otp)
                ->where(function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->first();

            if (!$user) {
                throw new Exception(__('auth.invalid_email_phone_or_token'), 400);
            }

            User::where(function ($query) use ($request) {
                $query->where('email', $request->email);
            })->update(['password' => Hash::make($request->password)]);

            DB::table('password_resets')->where('otp', $request->otp)
                ->where(function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->delete();

            DB::commit();

            return [
                'message' => __('auth.password_has_been_changed'),
                'success' => true,
            ];

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'otp' => 'required',
                'email' => 'exists:users|email',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $verify = DB::table('password_resets')
                ->where('otp', $request->otp)
                ->where('email', $request->email)
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->first();

            if (!$verify) {
                throw new Exception(__('auth.invalid_otp_or_email'), 400);
            }

            $user = User::firstOrCreate(['email' => $verify->email], [
                'email' => $verify->email,
                'code' => $request->code,
                'status' => true,
            ]);

            if (!$user) {
                throw new Exception(__('auth.user_not_exists'), 404);
            }

            if (!$user->status) {
                throw new Exception(__('auth.user_inactive'), 400);
            }

            return [
                'access_token' => $user->createToken('auth_token')->plainTextToken,
                'user' => $user,
                'success' => true,
            ];

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}

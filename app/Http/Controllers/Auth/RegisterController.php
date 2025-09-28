<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Events\CreateProviderEvent;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/backend/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data,  [
            'type' => 'required|string',
            'name' => 'required|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,deleted_at,id',
            'phone' => 'required|unique:users,phone,deleted_at,id',
            'experience_interval' => 'required|string',
            'experience_duration' => 'required|numeric',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
            'company_name' => 'required_if:type,company',
            'image' => 'file',
            'company_logo' => 'required_if:type,company,file',
            'company_email' => 'required_if:type,company|unique:companies,email,deleted_at,id',
            'company_code' => 'required_if:type,company',
            'company_phone' => 'required_if:type,company|unique:companies,phone,deleted_at,id',
            'alternative_name' => 'max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city' => 'required|string',
            'area' => 'required|string',
            'postal_code' => 'required',
            'address' => 'required|string',
            'zones*' => ['nullable', 'required', 'exists:zones,id,deleted_at,NULL'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        DB::beginTransaction();
        try {

            // Company creation
            if ($data['type'] === UserTypeEnum::COMPANY) {
                $company = Company::create([
                    'name' => $data['company_name'],
                    'email' => $data['company_email'],
                    'code' => $data['company_code'],
                    'phone' => $data['company_phone'],
                    'description' => $data['company_description'],
                ]);

                if (isset($data['company_logo']) && $data['company_logo']?->isValid()) {
                    $company->addMedia($data['company_logo'])->toMediaCollection('company_logo');
                }
            }

            // Provider creation
            $provider = User::create([
                'company_id' => $company->id ?? null,
                'experience_interval' => $data['experience_interval'],
                'experience_duration' => $data['experience_duration'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'code' => $data['code'],
                'status' => $data['status'],
                'type' => $data['type'],
                'password' => Hash::make($data['password']),
                'description' => $data['description'],
            ]);

            // Assign role
            $role = Role::where('name', RoleEnum::PROVIDER)->first();
            if ($data['role']) {
                $role = Role::findOrFail($data['role']);
            }

            $provider->assignRole($role);

            // Handle known languages
            if (isset($data['known_languages'])) {
                $provider->knownLanguages()->attach($data['known_languages']);
            }

            // Handle company logo image
            if (isset($data['image']) && $data['image']?->isValid()) {
                $company->addMedia($data['image'])->toMediaCollection('image');
            }

            // Handle zones
            if (isset($data['zones'])) {
                $provider->zones()->attach($data['zones']);
            }

            // Create address
            Address::create([
                'user_id' => $provider->id,
                'type' => $data['address_type'],
                'postal_code' => $data['postal_code'],
                'country_id' => $data['country_id'],
                'state_id' => $data['state_id'],
                'city' => $data['city'],
                'code' => $data['alternative_code'],
                'address' => $data['address'],
                'area' => $data['area'],
                'alternative_name' => $data['alternative_name'],
                'alternative_phone' => $data['alternative_phone'],
                'is_primary' => true,
            ]);

            // Commit the transaction
            DB::commit();

            // Log the user in
            // Auth::login($provider);

            // Trigger event
            event(new CreateProviderEvent($provider));

            // Redirect to appropriate page
            // return redirect()->route('backend.dashboard')->with('message', 'Provider Registered Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CategoryType;
use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Events\CreateProviderEvent;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\BecomeProviderRequest;
use App\Models\Address;
use App\Models\Category;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BecomeProviderController extends Controller
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function index()
    {
        $categories = Category::where('category_type', CategoryType::SERVICE)->where('status', true)->pluck('title','id');
        return view('auth.register',compact('categories'));
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function store(BecomeProviderRequest $request)
    {
        DB::beginTransaction();
        try {

            $data = $request?->all();
            if($data['role'] === 'serviceman'){
                $provider_id = $data['provider_id'];
            } else {
                $provider_id = null;
            }

            $settings = Helpers::getSettings();
            $autoApprove = $settings['activation']['provider_auto_approve'] ?? 0;
            
            // Provider creation
            $provider = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'code' => $data['code'],
                'type' => $data['type'],
                'provider_id' => $provider_id,
                'password' => Hash::make($data['password']),
                'status' => $autoApprove == 1 ? 1 : 0,
            ]);

            // Assign role
            if($data['role'] === RoleEnum::PROVIDER){
                $role = Role::where('name', RoleEnum::PROVIDER)->first();
                $provider->assignRole($role);
            } else {
                $role = Role::where('name', RoleEnum::SERVICEMAN)->first();
                $provider->assignRole($role);
            }

            // Handle known languages
            if (isset($data['known_languages'])) {
                $provider->knownLanguages()->attach($data['known_languages']);
            }

            // Handle company logo image
            if (isset($data['image']) && $data['image']?->isValid()) {
                $provider->addMedia($data['image'])->toMediaCollection('image');
            }

            // Handle zones
            if (isset($data['zones'])) {
                $provider->zones()->attach($data['zones']);
            }

            event(new CreateProviderEvent($provider));
            DB::commit();

            // Log the user in
            return redirect()->route('backend.dashboard')->with('message', 'Provider Registered Successfully.');

        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function getProvidersByCategory(Request $request)
    {
        $categoryId = $request->category_id;
        $providers = User::whereHas('services.categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        })->pluck('name', 'id');

        return response()->json(['providers' => $providers]);
    }
}

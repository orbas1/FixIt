<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Module;
use App\Enums\RoleEnum;
use App\Models\Address;
use App\Models\Company;
use App\Models\BankDetail;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'roles' => [
                'actions' => [
                    'index' => 'backend.role.index',
                    'create' => 'backend.role.create',
                    'edit' => 'backend.role.edit',
                    'destroy' => 'backend.role.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'bank_details' => [
                'actions' => [
                    'index' => 'backend.bank_detail.index',
                    'create' => 'backend.bank_detail.create',
                    'edit' => 'backend.bank_detail.edit',
                    'destroy' => 'backend.bank_detail.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'create', 'edit'],
                    RoleEnum::SERVICEMAN => ['index', 'create', 'edit'],
                    RoleEnum::CONSUMER => ['index', 'create', 'edit'],
                ],
            ],
            'service_categories' => [
                'actions' => [
                    'index' => 'backend.service_category.index',
                    'create' => 'backend.service_category.create',
                    'edit' => 'backend.service_category.edit',
                    'destroy' => 'backend.service_category.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'blog_categories' => [
                'actions' => [
                    'index' => 'backend.blog_category.index',
                    'create' => 'backend.blog_category.create',
                    'edit' => 'backend.blog_category.edit',
                    'destroy' => 'backend.blog_category.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'services' => [
                'actions' => [
                    'index' => 'backend.service.index',
                    'create' => 'backend.service.create',
                    'edit' => 'backend.service.edit',
                    'destroy' => 'backend.service.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'plans' => [
                'actions' => [
                    'index' => 'backend.plan.index',
                    'create' => 'backend.plan.create',
                    'edit' => 'backend.plan.edit',
                    'destroy' => 'backend.plan.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index'],
                ],
            ],
            'service_packages' => [
                'actions' => [
                    'index' => 'backend.service-package.index',
                    'create' => 'backend.service-package.create',
                    'edit' => 'backend.service-package.edit',
                    'destroy' => 'backend.service-package.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'bookings' => [
                'actions' => [
                    'index' => 'backend.booking.index',
                    'create' => 'backend.booking.create',
                    'edit' => 'backend.booking.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit'],
                    RoleEnum::PROVIDER => ['index', 'edit'],
                    RoleEnum::SERVICEMAN => ['index', 'edit'],
                    RoleEnum::CONSUMER => ['index', 'create', 'edit'],
                ],
            ],
            'custom_offers' => [
                'actions' => [
                    'index'   => 'backend.custom_offer.index',
                    'create'  => 'backend.custom_offer.create',
                    'edit'    => 'backend.custom_offer.edit',
                    'destroy'   => 'backend.custom_offer.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'create', 'edit'],
                    RoleEnum::CONSUMER => ['index','edit', 'create']
                ]
            ],
            'providers' => [
                'actions' => [
                    'index' => 'backend.provider.index',
                    'create' => 'backend.provider.create',
                    'edit' => 'backend.provider.edit',
                    'destroy' => 'backend.provider.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::CONSUMER => ['index'],
                ],
            ],
            'provider_wallets' => [
                'actions' => [
                    'index' => 'backend.provider_wallet.index',
                    'credit' => 'backend.provider_wallet.credit',
                    'debit' => 'backend.provider_wallet.debit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'credit', 'debit'],
                    RoleEnum::PROVIDER => ['index'],
                ],
            ],
            'serviceman_wallets' => [
                'actions' => [
                    'index' => 'backend.serviceman_wallet.index',
                    'credit' => 'backend.serviceman_wallet.credit',
                    'debit' => 'backend.serviceman_wallet.debit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'credit', 'debit'],
                    RoleEnum::SERVICEMAN => ['index'],
                ],
            ],
            'commission_histories' => [
                'actions' => [
                    'index' => 'backend.commission_history.index',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index'],
                    RoleEnum::PROVIDER => ['index'],
                ],
            ],
            'withdraw_requests' => [
                'actions' => [
                    'index' => 'backend.withdraw_request.index',
                    'create' => 'backend.withdraw_request.create',
                    'action' => 'backend.withdraw_request.action',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'action'],
                    RoleEnum::PROVIDER => ['index', 'create'],
                ],
            ],
            'serviceman_withdraw_requests' => [
                'actions' => [
                    'index' => 'backend.serviceman_withdraw_request.index',
                    'create' => 'backend.serviceman_withdraw_request.create',
                    'action' => 'backend.serviceman_withdraw_request.action',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'action'],
                    RoleEnum::PROVIDER => ['index', 'action'],
                    RoleEnum::SERVICEMAN => ['index', 'create'],
                ],
            ],
            'servicemen' => [
                'actions' => [
                    'index' => 'backend.serviceman.index',
                    'create' => 'backend.serviceman.create',
                    'edit' => 'backend.serviceman.edit',
                    'destroy' => 'backend.serviceman.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'coupons' => [
                'actions' => [
                    'index' => 'backend.coupon.index',
                    'create' => 'backend.coupon.create',
                    'edit' => 'backend.coupon.edit',
                    'destroy' => 'backend.coupon.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'backups' => [
                'actions' => [
                    'index'   => 'backend.backup.index',
                    'create'  => 'backend.backup.create',
                    'edit'    => 'backend.backup.edit',
                    'trash'   => 'backend.backup.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ]
            ],
            'system_tools' => [
                'actions' => [
                    'index'   => 'backend.system_tool.index',
                    'create'  => 'backend.system_tool.create',
                    'edit'    => 'backend.system_tool.edit',
                    'trash'   => 'backend.system_tool.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ]
            ],
            'wallets' => [
                'actions' => [
                    'index' => 'backend.wallet.index',
                    'credit' => 'backend.wallet.credit',
                    'debit' => 'backend.wallet.debit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'credit', 'debit'],
                ],
            ],
            'sliders' => [
                'actions' => [
                    'index' => 'backend.slider.index',
                    'create' => 'backend.slider.create',
                    'edit' => 'backend.slider.edit',
                    'destroy' => 'backend.slider.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'reviews' => [
                'actions' => [
                    'index' => 'backend.review.index',
                    'create' => 'backend.review.create',
                    'edit' => 'backend.review.edit',
                    'destroy' => 'backend.review.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'destroy', 'edit'],
                    RoleEnum::PROVIDER => ['index'],
                    RoleEnum::CONSUMER => ['index', 'create', 'destroy', 'edit'],
                ],
            ],
            'earnings' => [
                'actions' => [
                    'index' => 'backend.earning.index',
                    'create' => 'backend.earning.create',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create'],
                ],
            ],
            'taxes' => [
                'actions' => [
                    'index' => 'backend.tax.index',
                    'create' => 'backend.tax.create',
                    'edit' => 'backend.tax.edit',
                    'destroy' => 'backend.tax.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'users' => [
                'actions' => [
                    'index' => 'backend.user.index',
                    'create' => 'backend.user.create',
                    'edit' => 'backend.user.edit',
                    'destroy' => 'backend.user.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'customers' => [
                'actions' => [
                    'index' => 'backend.customer.index',
                    'create' => 'backend.customer.create',
                    'edit' => 'backend.customer.edit',
                    'destroy' => 'backend.customer.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'payment_transactions' => [
                'actions' => [
                    'index' => 'backend.payment_transaction.index',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index'],
                ],
            ],
            'documents' => [
                'actions' => [
                    'index' => 'backend.document.index',
                    'create' => 'backend.document.create',
                    'edit' => 'backend.document.edit',
                    'destroy' => 'backend.document.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'currencies' => [
                'actions' => [
                    'index' => 'backend.currency.index',
                    'create' => 'backend.currency.create',
                    'edit' => 'backend.currency.edit',
                    'destroy' => 'backend.currency.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'tags' => [
                'actions' => [
                    'index' => 'backend.tag.index',
                    'create' => 'backend.tag.create',
                    'edit' => 'backend.tag.edit',
                    'destroy' => 'backend.tag.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'blogs' => [
                'actions' => [
                    'index' => 'backend.blog.index',
                    'create' => 'backend.blog.create',
                    'edit' => 'backend.blog.edit',
                    'destroy' => 'backend.blog.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::CONSUMER => ['index'],
                ],
            ],
            'pages' => [
                'actions' => [
                    'index' => 'backend.page.index',
                    'create' => 'backend.page.create',
                    'edit' => 'backend.page.edit',
                    'destroy' => 'backend.page.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::CONSUMER => ['index'],
                ],
            ],
            'provider_time_slots' => [
                'actions' => [
                    'index' => 'backend.provider_time_slot.index',
                    'create' => 'backend.provider_time_slot.create',
                    'edit' => 'backend.provider_time_slot.edit',
                    'destroy' => 'backend.provider_time_slot.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'provider_documents' => [
                'actions' => [
                    'index' => 'backend.provider_document.index',
                    'create' => 'backend.provider_document.create',
                    'edit' => 'backend.provider_document.edit',
                    'destroy' => 'backend.provider_document.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'banners' => [
                'actions' => [
                    'index' => 'backend.banner.index',
                    'create' => 'backend.banner.create',
                    'edit' => 'backend.banner.edit',
                    'destroy' => 'backend.banner.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index'],
                    RoleEnum::CONSUMER => ['index'],
                ],
            ],
            'advertisements' => [
                'actions' => [
                    'index' => 'backend.advertisement.index',
                    'create' => 'backend.advertisement.create',
                    'edit' => 'backend.advertisement.edit',
                    'destroy' => 'backend.advertisement.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::CONSUMER => ['index'],
                ],
            ],
            'settings' => [
                'actions' => [
                    'index' => 'backend.setting.index',
                    'create' => 'backend.setting.create',
                    'edit' => 'backend.setting.edit',
                    'destroy' => 'backend.setting.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'payment_methods' => [
                'actions' => [
                    'index' => 'backend.payment_method.index',
                    'create' => 'backend.payment_method.create',
                    'edit' => 'backend.payment_method.edit',
                    'destroy' => 'backend.payment_method.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'sms_gateways' => [
                'actions' => [
                    'index' => 'backend.sms_gateway.index',
                    'edit' => 'backend.sms_gateway.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'edit'],
                ],
            ],
            'languages' => [
                'actions' => [
                    'index' => 'backend.language.index',
                    'create' => 'backend.language.create',
                    'edit' => 'backend.language.edit',
                    'destroy' => 'backend.language.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'push_notifications' => [
                'actions' => [
                    'index' => 'backend.push_notification.index',
                    'create' => 'backend.push_notification.create',
                    'edit' => 'backend.push_notification.edit',
                    'destroy' => 'backend.push_notification.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'edit'],
                    RoleEnum::CONSUMER => ['index','create']
                ]
            ],
            'bids' => [
                'actions' => [
                    'index'   => 'backend.bid.index',
                    'create'  => 'backend.bid.create',
                    'edit'    => 'backend.bid.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit'],
                    RoleEnum::PROVIDER => ['index','create'],
                    RoleEnum::CONSUMER => ['index','edit']
                ]
            ],
            'zones' => [
                'actions' => [
                    'index' => 'backend.zone.index',
                    'create' => 'backend.zone.create',
                    'edit' => 'backend.zone.edit',
                    'destroy' => 'backend.zone.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                ],
            ],
            'home_pages' => [
                'actions' => [
                    'index' => 'backend.home_page.index',
                    'edit' => 'backend.home_page.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index','edit'],
                ]
            ],
            'customizations' => [
                'actions' => [
                    'index' => 'backend.customization.index',
                    'edit' => 'backend.customization.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index','edit'],
                ]
            ],
            'testimonials' => [
                'actions' => [
                    'index' => 'backend.testimonial.index',
                    'create' => 'backend.testimonial.create',
                    'edit' => 'backend.testimonial.edit',
                    'destroy' => 'backend.testimonial.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN =>['index', 'create', 'edit', 'destroy'],
                ]
            ],
            'theme_options' => [
                'actions' => [
                    'index' => 'backend.theme_option.index',
                    'edit' => 'backend.theme_option.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index','edit'],
                ]
            ],
            'news_letters' => [
                'actions' => [
                    'index' => 'backend.news_letter.index',
                    'create' => 'backend.news_letter.create',
                    'edit' => 'backend.news_letter.edit',
                    'destroy' => 'backend.news_letter.destroy'
                ],
                'roles'=>[
                    RoleEnum::ADMIN => ['index','create','edit','destroy']
                ]
            ],

            'sms_templates' => [
                'actions' => [
                    'index'   =>  'backend.sms_template.index',
                    'edit'    =>  'backend.sms_template.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index','edit'],
                ]
            ],
            'email_templates' => [
                'actions' => [
                    'index'   =>  'backend.email_template.index',
                    'edit'    =>  'backend.email_template.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index','edit'],
                ]
            ],
            'push_notification_templates' => [
                'actions' => [
                    'index'   =>  'backend.push_notification_template.index',
                    'edit'    =>  'backend.push_notification_template.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index','edit'],
                ]
            ],
            'service_request' => [
                'actions' => [
                    'index'   => 'backend.service_request.index',
                    'create'  => 'backend.service_request.create',
                    'edit'    => 'backend.service_request.edit',
                    'destroy'   => 'backend.service_request.destroy',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'edit'],
                    RoleEnum::CONSUMER => ['index','create', 'destroy']
                ]
            ],
            'custom_sms_gateways' => [
                'actions' => [
                    'index'   =>  'backend.custom_sms_gateway.index',
                    'edit'    =>  'backend.custom_sms_gateway.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'edit'],
                    RoleEnum::CONSUMER => ['index'],
                ]
            ],
            'provider_dashboard' => [
                'actions' => [
                    'index'   =>  'backend.provider_dashboard.index',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index'],
                ]
            ],
            'consumer_dashboard' => [
                'actions' => [
                    'index'   =>  'backend.consumer_dashboard.index',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index'],
                ]
            ],
            'servicemen_dashboard' => [
                'actions' => [
                    'index'   =>  'backend.servicemen_dashboard.index',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index'],
                ]
            ],
            'unverified_user' => [
                'actions' => [
                    'index'   =>  'backend.unverified_user.index',
                    'edit'   =>  'backend.unverified_user.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index','edit'],
                ]
            ],
            'serviceman_locations' => [
                'actions' => [
                    'index' => 'backend.serviceman_location.index',
                    'create' => 'backend.serviceman_location.create',
                    'edit' => 'backend.serviceman_location.edit',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit'],
                    RoleEnum::PROVIDER => ['index', 'create', 'edit'],
                ]
            ],
            'reports' => [
                'actions' => [
                    'index' => 'backend.report.index',
                    'create' => 'backend.report.create',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create'],
                ],
            ],
        ];
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $sequence = 0;
        $providerPermissions = [];
        $servicemanPermissions = [];
        $userPermissions = [];
        foreach ($modules as $key => $value) {
            Module::create(['name' => $key, 'actions' => $value['actions']]);
            foreach ($value['actions'] as $action => $permission) {
                if (! Permission::where('name', $permission)->first()) {
                    $permission = Permission::create(['name' => $permission]);
                }

                foreach ($value['roles'] as $role => $allowed_actions) {
                    if ($role == RoleEnum::PROVIDER) {
                        if (in_array($action, $allowed_actions)) {
                            $providerPermissions[] = $permission;
                        }
                    }

                    if ($role == RoleEnum::SERVICEMAN) {
                        if (in_array($action, $allowed_actions)) {
                            $servicemanPermissions[] = $permission;
                        }
                    }

                    if ($role == RoleEnum::CONSUMER) {
                        if (in_array($action, $allowed_actions)) {
                            $userPermissions[] = $permission;
                        }
                    }
                }
            }
        }

        $adminRole = Role::create([
            'name' => RoleEnum::ADMIN,
            'system_reserve' => true,
        ]);
        $adminRole->givePermissionTo(Permission::all());
        $admin = User::factory()->create([
            'name' => RoleEnum::ADMIN,
            'email' => 'admin@example.com',
            'password' => Hash::make('123456789'),
            'system_reserve' => true,
            'status' => true,
        ]);
        $admin->assignRole($adminRole);
        $userRole = Role::create([
            'name' => RoleEnum::CONSUMER,
            'system_reserve' => true,
        ]);
        $userRole->givePermissionTo($userPermissions);
        $consumer = User::factory()->create([
            'name' => 'Thomas Taylor',
            'email' => 'user@example.com',
            'password' => Hash::make('123456789'),
            'system_reserve' => true,
            'status' => true,
        ]);
        $consumer->assignRole($userRole);
        $providerRole = Role::create([
            'name' => RoleEnum::PROVIDER,
            'system_reserve' => true,
        ]);

        $providerRole->givePermissionTo($providerPermissions);
        $company = Company::create([
            'name' => 'ComfortCraft',
            'email' => 'comfort.carft@example.com',
            'code' => '91',
            'phone' => '123456789',
            'description' => 'provider company description',
        ]);

        $companyLogoPath = public_path('public/admin/images/example-company-logo.jpg');
        if (File::exists($companyLogoPath)) {
            $company->addMedia($companyLogoPath)->toMediaCollection('company_logo');
        }

        $provider = User::factory()->create([
            'name' => 'Robert Davis',
            'email' => 'provider@example.com',
            'password' => Hash::make('123456789'),
            'system_reserve' => true,
            'status' => true,
            'type' => 'company',
            'experience_interval' => 'years',
            'experience_duration' => '2',
            'company_id' => $company->id,
        ]);

        $provider->assignRole($providerRole);

        $providerImagePath = public_path('public/admin/images/example-provider.png');
        if (File::exists($providerImagePath)) {
            $provider->addMedia($providerImagePath)->toMediaCollection('image');
        }

        $address = Address::create([
            'user_id' => $provider->id,
            'type' => 'work',
            'postal_code' => '3950002',
            'country_id' => 840,
            'state_id' => 3787,
            'city' => 'Gourmetville',
            'code' => 1,
            'address' => '123 Culinary Lane, New York 10001',
            'area' => '123 Culinary Lane',
            'is_primary' => true,
        ]);

        $bankDetail = BankDetail::create([
            'user_id' => $provider->id,
            'bank_name' => 'Central Bank USA',
            'holder_name' => $provider?->name,
            'account_number' => '895645220034',
            'branch_name' => 'Midtown',
            'ifsc_code' => 'CBU12345CODE',
            'swift_code' => 'SWIFT12345CODE',
        ]);

        $servicemanRole = Role::create([
            'name' => RoleEnum::SERVICEMAN,
            'system_reserve' => true,
        ]);
        $servicemanRole->givePermissionTo($servicemanPermissions);
        $serviceman = User::factory()->create([
            'name' => 'Michael Smith',
            'email' => 'seviceman@example.com',
            'password' => Hash::make('123456789'),
            'system_reserve' => true,
            'status' => true,
            'created_by' => $provider->id,
        ]);
        $serviceman->assignRole($servicemanRole);
    }
}

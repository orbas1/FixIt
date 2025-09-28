<?php

namespace Modules\Subscription\Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;
use Modules\Subscription\Enums\RoleEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SubscriptionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
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
            'subscriptions' => [
                'actions' => [
                    'index' => 'subscription.index',
                    'create' => 'subscription.create',
                    'edit' => 'subscription.edit',
                    'destroy' => 'subscription.destroy',
                    'purchase' => 'subscription.purchase',
                    'cancel' => 'subscription.cancel',
                ],
                'roles' => [
                    RoleEnum::ADMIN => ['index', 'create', 'edit', 'destroy'],
                    RoleEnum::PROVIDER => ['index', 'purchase', 'cancel'],
                ],
            ],
        ];

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $sequence = 0;
        $vendorPermissions = [];
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
                            $handymanPermissions[] = $permission;
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

    }
}

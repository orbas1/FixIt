<?php

namespace Modules\Subscription\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Subscription\Entities\Plan;

class PlanPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('backend.plan.index')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('backend.plan.create')) {

            return true;
        }
    }

    public function update(User $user, Plan $plan)
    {
        if ($user->can('backend.plan.edit') &&

            $user->id == $plan->created_by) {

            return true;
        }
    }

    public function delete(User $user, Plan $plan)
    {
        if ($user->can('backend.plan.destroy') &&

            $user->id == $plan->created_by) {

            return true;
        }
    }
}

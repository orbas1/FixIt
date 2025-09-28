<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Backup;
use App\Enums\RoleEnum;
use Illuminate\Auth\Access\HandlesAuthorization;

class BackupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */

    public function viewAny(User $user)
    {
        if ($user->can('backend.backup.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Backup  $backup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Backup $backup)
    {
        if ($user->can('backend.backup.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        if ($user->can('backend.backup.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Backup  $backup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Backup $backup)
    {
        if ($user->can('backend.backup.edit') && ($user->role->name != RoleEnum::PROVIDER || $user->id == $backup->created_by)) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Backup  $backup
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Backup $backup)
    {
        if ($user->can('backend.backup.destroy') && $user->id == $backup->created_by) {
            return true;
        }
    }
}

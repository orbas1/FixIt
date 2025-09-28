<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ThemeOption;

class ThemeOptionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        if ($user->can('backend.theme_option.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ThemeOption $themeOption)
    {
        if ($user->can('backend.theme_option.index')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        if ($user->can('backend.theme_option.create')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ThemeOption $themeOption)
    {
        if ($user->can('backend.theme_option.edit')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user,  ThemeOption $themeOption)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user,  ThemeOption $themeOption)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user,  ThemeOption $themeOption)
    {
        //
    }
}

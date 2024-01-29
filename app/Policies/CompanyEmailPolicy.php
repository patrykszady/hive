<?php

namespace App\Policies;

use App\Models\CompanyEmail;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompanyEmailPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if($user->vendor->user_role == 'Admin'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompanyEmail $companyEmail): bool
    {
        if($user->vendor->user_role == 'Admin'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompanyEmail $companyEmail): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompanyEmail $companyEmail): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CompanyEmail $companyEmail): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CompanyEmail $companyEmail): bool
    {
        //
    }
}

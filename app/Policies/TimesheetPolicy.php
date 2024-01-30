<?php

namespace App\Policies;

use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TimesheetPolicy
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
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Timesheet $timesheet)
    {
        // dd($timesheet->user->vendors()->where('vendors.id', 1)->first()->pivot->user_id);
        // dd($timesheet->user->id);

        //or user_role = Member
        //$user->vendor->user_role == 'Admin'

        // dd($user->vendors()->where('vendors.id', $timesheet->vendor_id)->first()->pivot->user_id);
        if($timesheet->user_id == $user->id || $user->vendor->user_role == 'Admin'){
            return true;
        }else{
            return false;
        }
        // if($timesheet->user_id == $user->id){
        //     return true;
        // }else{
        //     return false;
        // }
    }

    public function viewPayment(User $user)
    {
        if($user->vendor->user_role == 'Admin'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Timesheet $timesheet)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Timesheet $timesheet)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Timesheet $timesheet)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Timesheet  $timesheet
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Timesheet $timesheet)
    {
        //
    }
}

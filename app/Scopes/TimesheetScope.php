<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TimesheetScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        //if Admin..all Expenses ... if Member...only expenses the User Paid For....?
        if($user->vendor->user_role == 'Admin'){
            $builder->where('vendor_id', $user->primary_vendor_id);
        }elseif($user->vendor->user_role == 'Member'){
            $builder->where('vendor_id', $user->primary_vendor_id)->where('user_id', $user->id);
        }
    }
}

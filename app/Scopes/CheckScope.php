<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CheckScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        //if Check has Paid Employee TImesheets...they shoud show in the Employees Checks?
        if($user->vendor->user_role == 'Admin'){
            $builder->where('belongs_to_vendor_id', $user->primary_vendor_id);
        }elseif($user->vendor->user_role == 'Member'){
            $builder->where('belongs_to_vendor_id', $user->primary_vendor_id)->where('user_id', $user->id);
        }
    }
}
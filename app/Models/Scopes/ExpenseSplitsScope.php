<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ExpenseSplitsScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        if(is_null($user)){
            $builder;
        }else{
            //if Admin..all Expenses ... if Member...only expenses the User Paid For....?
            if($user->vendor->user_role == 'Admin'){
                $builder->where('belongs_to_vendor_id', auth()->user()->primary_vendor_id);
            }elseif($user->vendor->user_role == 'Member'){
                $builder->where('belongs_to_vendor_id', $user->primary_vendor_id);
            }else{
                $builder;
            }
        }
    }
}

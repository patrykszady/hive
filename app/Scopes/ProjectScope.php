<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use Carbon\Carbon;

class ProjectScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        $user_vendor_pivot = $user->vendor->auth_user_role->first()->pivot;

        $builder->withWhereHas('vendors', function ($query) use ($user) {
            $query->where('vendor_id', $user->vendor->id);
        });

        //Admin
        if($user_vendor_pivot->role_id == 1){
            //shows all projects
            //where client/vendor
            // $builder->where('belongs_to_vendor_id', $user->primary_vendor_id);
            $builder;
        //Member
        }elseif($user_vendor_pivot->role_id == 2){
            //03-15-2023  and any active projects despite how long ago they were created...
            $projects_start_date = Carbon::parse($user_vendor_pivot->start_date)->subMonths(6)->format('Y-m-d');
            // $projects_end_date = Carbon::parse($user->vendor->auth_user_role->first()->pivot->end_date);

            //only show projects since employment started ..minus 6 months (why 6 months?)
            //whereBetween start and end dates
            // $builder->whereBetween('created_at', [$projects_start_date, $projects_end_date]);
            $builder->where('created_at', '>', $projects_start_date);
        }
    }
}

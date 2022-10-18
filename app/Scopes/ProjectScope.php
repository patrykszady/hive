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
        $role = auth()->user()->vendor->user_role;
        if($role == 'Admin'){
            //shows all projects
        }elseif($role == 'Member'){
            $projects_start_date = Carbon::parse(auth()->user()->vendor->auth_user_role->first()->pivot->start_date)->subMonths(6);

            //only show projects since employment started ..minus 1 year (why 1 year?)
            $builder->where('created_at', '>', $projects_start_date->format('Y-m-d'));
        }
    }
}
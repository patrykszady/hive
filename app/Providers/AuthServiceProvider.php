<?php

namespace App\Providers;

// use App\Models\Expense;
// use App\Policies\UserPolicy;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    // protected $policies = [
    //     Expense::class => ExpensePolicy::class,
    // ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        //Larvel -9 .. not needed in Laravel 10+
        // $this->registerPolicies();
    }
}

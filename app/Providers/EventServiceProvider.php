<?php

namespace App\Providers;

use App\Models\Bid;
use App\Models\Client;
use App\Models\Expense;
use App\Models\EstimateLineItem;
use App\Models\LineItem;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\UserVendor;

use App\Observers\BidObserver;
use App\Observers\ClientObserver;
use App\Observers\ExpenseObserver;
use App\Observers\EstimateLineItemObserver;
use App\Observers\LineItemObserver;
use App\Observers\VendorObserver;
use App\Observers\ProjectObserver;
use App\Observers\UserVendorObserver;


use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Bid::observe(BidObserver::class);
        Client::observe(ClientObserver::class);
        Expense::observe(ExpenseObserver::class);
        EstimateLineItem::observe(EstimateLineItemObserver::class);
        LineItem::observe(LineItemObserver::class);
        Project::observe(ProjectObserver::class);
        UserVendor::observe(UserVendorObserver::class);
        Vendor::observe(VendorObserver::class);
    }
}

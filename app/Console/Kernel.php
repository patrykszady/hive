<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // only in Production not in Development enviroment ... EVERYTHING EMAIL RELATED GOES HERE
        if(env('APP_ENV') == 'production'){
            $schedule->call('\App\Http\Controllers\ReceiptController@receipt_email')->everyMinute();  
            $schedule->call('\App\Http\Controllers\TransactionController@plaid_transactions_scheduled')->hourly();
            $schedule->call('\App\Http\Controllers\TransactionController@add_check_deposit_to_transactions')->everyTenMinutes();
            $schedule->call('\App\Http\Controllers\TransactionController@add_vendor_to_transactions')->everyTenMinutes();
            $schedule->call('\App\Http\Controllers\TransactionController@add_expense_to_transactions')->everyTenMinutes();
            $schedule->call('\App\Http\Controllers\TransactionController@add_check_id_to_transactions')->twiceDaily(7, 19);
            $schedule->call('\App\Http\Controllers\TransactionController@add_payments_to_transaction')->everyTenMinutes();
            // $schedule->call('\App\Http\Controllers\TransactionController@find_credit_payments_on_debit')->everyTenMinutes();
        }
        //Transactions bidaily/hourly
        // $schedule->call('\App\Http\Controllers\TransactionController@plaid_item_error_update')->hourly();

        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

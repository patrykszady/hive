<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\MoveController;
use App\Http\Controllers\TransactionController;

use App\Http\Livewire\Banks\BankShow;
use App\Http\Livewire\Banks\BankIndex;

use App\Http\Livewire\Users\UsersShow;

use App\Http\Livewire\Vendors\VendorsIndex;
use App\Http\Livewire\Vendors\VendorsForm;
use App\Http\Livewire\Vendors\VendorPaymentForm;
use App\Http\Livewire\Vendors\VendorsShow;

use App\Http\Livewire\Clients\ClientsIndex;
use App\Http\Livewire\Clients\ClientsShow;

use App\Http\Livewire\Timesheets\TimesheetsIndex;
use App\Http\Livewire\Timesheets\TimesheetsForm;
use App\Http\Livewire\Timesheets\TimesheetShow;
use App\Http\Livewire\Timesheets\TimesheetPaymentForm;
use App\Http\Livewire\Timesheets\TimesheetPaymentIndex;

use App\Http\Livewire\Hours\HoursForm;

use App\Http\Livewire\Users\AdminLoginAsUser;

use App\Http\Livewire\Entry\VendorSelection;

use App\Http\Livewire\Transactions\MatchVendor;

use App\Http\Livewire\Expenses\ExpenseIndex;
use App\Http\Livewire\Expenses\ExpensesEdit;
use App\Http\Livewire\Expenses\ExpensesForm;
use App\Http\Livewire\Expenses\ExpensesShow;

use App\Http\Livewire\Checks\ChecksShow;
use App\Http\Livewire\Checks\ChecksIndex;

use App\Http\Livewire\Projects\ProjectsIndex;
use App\Http\Livewire\Projects\ProjectsShow;

use App\Http\Livewire\Payments\PaymentsIndex;
use App\Http\Livewire\Payments\PaymentsForm;

use App\Http\Livewire\Bids\BidsForm;

use App\Http\Livewire\Dashboard\DashboardShow;

//if guests go to '/', if logged in to to dashboard (or to /vendor_selection if not set and User has multiple)
Route::middleware('guest')->group(function(){
    Route::get('/', function () {
        return view('welcome');
    });
});

Route::get('/move', [MoveController::class, 'move'])->name('move');

//SOLVED: 3-29-2022 :it passes auth BUT FAILS user.vendor middleware, send to /vendor_selection if passes both..send to /dashboard
Route::get('/vendor_selection', VendorSelection::class)->middleware('auth')->name('vendor_selection');

//1-5-2022 not working..almost
Route::get('expenses/original_receipts/{receipt}', [ReceiptController::class, 'original_receipt'])->name('expenses.original_receipt');
Route::get('receipts/receipt_email', [ReceiptController::class, 'receipt_email'])->name('receipt_email');

Route::get('new_orc_status', [ReceiptController::class, 'new_orc_status'])->name('new_orc_status');

// Route::middleware('can:admin')->group(function () {
//     Route::resource('admin/posts', AdminPostController::class)->except('show');
// });

Route::get('plaid_transactions_scheduled', [TransactionController::class, 'plaid_transactions_scheduled']);
Route::get('add_vendor_to_transactions', [TransactionController::class, 'add_vendor_to_transactions']);
Route::get('add_expense_to_transactions', [TransactionController::class, 'add_expense_to_transactions']);


Route::middleware(['auth', 'user.vendor'])->group(function(){
    //DASHBOARD/ PRIMARY VENDOR
    Route::get('/dashboard', DashboardShow::class)->name('dashboard');

    //USERS
    // Route::get('/users/{user}', UsersShow::class)->name('users.show');
        //Log In As User for Admins (User id # 1 right now only)
        //Only User #1 / Patryk can access this route / middleware 
        Route::get('/users/admin_login_as_user', AdminLoginAsUser::class)->name('admin_login_as_user');    
    
    //EXPENSES
    Route::get('/expenses', ExpenseIndex::class)->name('expenses.index');
    Route::get('/expenses/create', ExpensesForm::class)->name('expenses.create');
    Route::get('/expenses/{expense}', ExpensesShow::class)->name('expenses.show');
    Route::get('/expenses/{expense}/edit', ExpensesForm::class)->name('expenses.edit');
    Route::get('/expenses/{expense}/udpate', ExpensesForm::class)->name('expenses.update');
    // Route::resource('expenses', ExpenseController::class);

    //VENDORS
    Route::get('/vendors', VendorsIndex::class)->name('vendors.index');
    Route::get('/vendors/create', VendorsForm::class)->name('vendors.create');
    Route::get('/vendors/{vendor}', VendorsShow::class)->name('vendors.show');
    Route::get('/vendors/{vendor}/edit', VendorsForm::class)->name('vendors.edit');
    Route::get('/vendors/{vendor}/payment', VendorPaymentForm::class)->name('vendors.payment');

    //BANKS
    Route::get('/banks', BankIndex::class)->name('banks.index');
    Route::get('/banks/{bank}', BankShow::class)->name('banks.show');

    //CHECKS
    Route::get('/checks', ChecksIndex::class)->name('checks.index');
    Route::get('/checks/{check}', ChecksShow::class)->name('checks.show');

    //CLIENTS
    Route::get('/clients', ClientsIndex::class)->name('clients.index');
    Route::get('/clients/{client}', ClientsShow::class)->name('clients.show');

    //PROJECTS
    Route::get('/projects', ProjectsIndex::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectsShow::class)->name('projects.show');

    //TIMESHEETS
    Route::get('/timesheets', TimesheetsIndex::class)->name('timesheets.index');
    Route::get('/timesheets/create/{hour}', TimesheetsForm::class)->name('timesheets.create');
    Route::get('/timesheets/payment/{user}', TimesheetPaymentForm::class)->name('timesheets.payment');
    Route::get('/timesheets/payments', TimesheetPaymentIndex::class)->name('timesheets.payments');
    Route::get('/timesheets/{timesheet}', TimesheetShow::class)->name('timesheets.show');

    //TRANSACTIONS
    Route::get('/transactions/match_vendor', MatchVendor::class)->name('transactions.match_vendor');

    //HOURS
    Route::get('/hours/create', HoursForm::class)->name('hours.create');

    //PAYMENTS
    Route::get('/payments', PaymentsIndex::class)->name('payments.index');
    Route::get('/payments/create/{client}', PaymentsForm::class)->name('payments.create');

    //BIDS
    Route::get('/bids/create/{project}', BidsForm::class)->name('bids.create');
});

require __DIR__.'/auth.php';



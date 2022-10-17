<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseSplits;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Check;
use App\Models\CompanyEmail;
use App\Models\Distribution;
use App\Models\ExpenseReceipts;
use App\Models\Hour;
use App\Models\Timesheet;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Receipt;
use App\Models\ReceiptAccount;
use App\Models\Bid;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

ini_set('max_execution_time', 600900000);

class MoveController extends Controller
{
    public function move()
    {
        ini_set('max_execution_time', 600900000);
        //open old database
        $move_database = DB::connection('move_mysql');

        
        //NEED TO BE LOGGED IN FOR [BIDS TYPE, PROJECT STATUS COMBINE]
        //BIDS TYPE
    //   $bids = Bid::withoutGlobalScopes()->orderBy('created_at', 'ASC')->get();
        
    //   foreach($bids->groupBy('project_id') as $bids_project){
    //     foreach($bids_project->groupBy('vendor_id') as $bids_project_vendor){
    //         foreach($bids_project_vendor as $key => $bid){
    //             if($key == 0){
    //                 //nothing, BID TYPE already = 1
    //             }else{
    //                 if($bid->amount == "0.00" || $bid->amount == "0.0"){
    //                     //destroy record
    //                     $bid->delete();
    //                 }else{
    //                     $bid->type = 2; //change order
    //                     $bid->save();
    //                 }
    //             }
    //         }
    //     }
    //   }

    //   dd('past bids type');

      //PROJECT STATUS COMBINE ..ONLY DO UNDER LOGGED IN USER FOR VENDOR_ID 1 / GS CONSTRUCTION
    //   $project_statuses = ProjectStatus::withoutGlobalScopes()->orderBy('created_at', 'ASC')->get();
        
    //   foreach($project_statuses->groupBy('project_id') as $project_status_order){
    //       //keep ->last .... delete all others...    
    //       if($project_status_order->count() > 1){
    //           $order_ids = $project_status_order->pluck('id');
    //           $last = $project_status_order->last()->id;
    //           ProjectStatus::destroy(array_diff($order_ids->toArray(), array($last)));
    //       }
    //   }

    //   dd('past project status combine');



// ------------------------------------------
// ------------------------------------------
// ------------------------------------------

//         //BIDS
//         $bids = $move_database->select('select * from bids');
//         foreach($bids as $bid){
//             Bid::create([
//                 'id' => $bid->id,
//                 'project_id' => $bid->project_id,
//                 'vendor_id' => $bid->vendor_id,
//                 'amount' => $bid->amount,
//                 'type' => 1,
//                 'created_at' => $bid->created_at,
//                 'updated_at' => $bid->updated_at,
//             ]);
//         }

//         //PAYMENTS
//         $payments = $move_database->select('select * from client_payments');
//         foreach($payments as $payment){
//             if($payment->belongs_to_vendor_id == 1){
//                 // $payment->parent_client_payment_id = $payment->id ? NULL : $payment->parent_client_payment_id
//                 if($payment->parent_client_payment_id != $payment->id){
//                     $parent_client_id = $payment->parent_client_payment_id;
//                 }else{
//                     $parent_client_id = NULL;
//                 }

//                 Payment::create([
//                     'id' => $payment->id,
//                     'project_id' => $payment->project_id,
//                     'amount' => $payment->amount,
//                     'date' => $payment->date,
//                     'reference' => $payment->reference,
//                     'transaction_id' => $payment->transaction_id,
//                     'belongs_to_vendor_id' => $payment->belongs_to_vendor_id,
//                     'parent_client_payment_id' => $parent_client_id,
//                     'note' => $payment->note,
//                     'created_by_user_id' => $payment->created_by_user_id,
//                     'created_at' => $payment->created_at,
//                     'updated_at' => $payment->updated_at,
//                 ]);
//             }
//         }

//         //USERS
//         $users = $move_database->select('select * from users');
//         foreach($users as $user){
//             User::create([
//                 'id' => $user->id,
//                 'first_name' => $user->first_name,
//                 'last_name' => $user->last_name,
//                 'cell_phone' => $user->phone_number,
//                 'email' => $user->email,
//                 'email_verified_at' => now(),
//                 'primary_vendor_id' => NULL,
//                 'password' => $user->password,
//                 'remember_token' => NULL,
//                 'created_at' => $user->created_at,
//                 'updated_at' => $user->updated_at,
//             ]);
//         }

//         //VENDORS
//         $vendors = $move_database->select('select * from vendors');
//         foreach($vendors as $vendor){
//             // dd($vendor);
//             if($vendor->biz_type == 1){
//                 $vendor_type = 'Sub';
//             }elseif($vendor->biz_type == 2){
//                 $vendor_type = 'Retail';
//             }elseif($vendor->biz_type == 3){
//                 $vendor_type = 'DBA';
//             }elseif($vendor->biz_type == 4){
//                 $vendor_type = 'W9';
//             }else{
//                 $vendor_type = '';
//             }

//             Vendor::create([
//                 'id' => $vendor->id,
//                 'business_name' => $vendor->business_name,
//                 'business_type' => $vendor_type,
//                 'address' => $vendor->address,
//                 'address_2' => $vendor->address_2,
//                 'city' => $vendor->city,
//                 'state' => $vendor->state,
//                 'zip_code' => $vendor->zip_code,
//                 'business_phone' => $vendor->biz_phone,
//                 'business_email' => $vendor->email,
//                 // 'cliff_registration' => NULL,
//                 'created_at' => $vendor->created_at,
//                 'updated_at' => $vendor->updated_at,
//             ]);
//         }

//         //USER_VENDOR 
//         $user_vendors = $move_database->select('select * from user_vendor');
//         foreach($user_vendors as $user_vendor){           
//             $vendor = Vendor::withoutGlobalScopes()->with('users')->find($user_vendor->vendor_id);

//             if(is_null($vendor)){
//                 Log::channel('move_channel')->info(['user_vendor', $user_vendor]);
//                 continue;
//             }else{
//                 $vendor->users()->attach($user_vendor->user_id, array(
//                     'role_id' => $user_vendor->role_id,
//                     'via_vendor_id' => $user_vendor->via_vendor_id,
//                     'start_date' => $user_vendor->start_date == '0000-00-00' ? NULL : $user_vendor->start_date,
//                     'end_date' => $user_vendor->end_date == '0000-00-00' ? NULL : $user_vendor->end_date,
//                     'is_employed' => $user_vendor->is_employed,
//                     // 'hourly_rate' => NULL,
//                     'created_at' => $user_vendor->created_at,
//                     'updated_at' => $user_vendor->updated_at,
//                 ));
//                 continue;
//             }
//         }   
        
//         //VENDORS_VENDOR 
//         $vendor_vendors = $move_database->select('select * from vendors_belong_to_vendor');
//         foreach($vendor_vendors as $vendor_vendor){  

//             $vendor = Vendor::withoutGlobalScopes()->find($vendor_vendor->belongs_to_vendor_id);

//             if(is_null($vendor)){
//                 Log::channel('move_channel')->info(['vendor_vendors', $vendor_vendor]);
//                 continue;
//             }else{
//                 $vendor->vendors()->attach($vendor_vendor->vendor_id, array(
//                     'created_at' => $vendor_vendor->created_at,
//                     'updated_at' => $vendor_vendor->updated_at,
//                 ));
//                 continue;
//             }
//         }   

//         //RECEIPT ACCOUNTS
//         $receipt_accounts = $move_database->select('select * from receipt_accounts');
//         foreach($receipt_accounts as $account){
//             ReceiptAccount::create([
//                 'id' => $account->id,
//                 'vendor_id' => $account->vendor_id, 
//                 'belongs_to_vendor_id' => $account->belongs_to_vendor_id,
//                 'project_id' => $account->project_id,
//                 'distribution_id' => $account->distribution_id,
//                 'options' => $account->options,
//                 'instructions' => NULL,
//                 'created_at' => $account->created_at,
//                 'updated_at' => $account->updated_at,
//             ]);
//         }

//         //RECEIPTS
//         $receipts = $move_database->select('select * from receipts');
//         foreach($receipts as $receipt){
//             Receipt::create([
//                 'id' => $receipt->id,
//                 'vendor_id' => $receipt->vendor_id, 
//                 'from_type' => $receipt->from_type,
//                 'from_address' => $receipt->from_address,
//                 'from_subject' => $receipt->from_subject,
//                 'options' => $receipt->options,
//                 'receipt_width' => $receipt->receipt_width,
//                 'receipt_type' => $receipt->receipt_type,
//                 'created_at' => $receipt->created_at,
//                 'updated_at' => $receipt->updated_at,
//             ]);
//         }

//         //PROJECTS
//         $projects = $move_database->select('select * from projects');
//         foreach($projects as $project){
//             Project::create([
//                 'id' => $project->id,
//                 'project_name' => $project->project_name,
//                 'client_id' => $project->client_id,
//                 'belongs_to_vendor_id' => $project->vendor_id,
//                 'note' => $project->note,
//                 'do_not_include' => $project->do_not_include,   
//                 'address' => $project->address,
//                 'address_2' => $project->address_2 == NULL || '' ? NULL : $project->address_2,
//                 'city' => $project->city,
//                 'state' => $project->state,
//                 'zip_code' => $project->zip_code,               
//                 'created_at' => $project->created_at,
//                 'updated_at' => $project->updated_at,
//             ]);
//         }

//         //TIMESHEETS
//         $timesheets = $move_database->select('select * from hours');
//         foreach($timesheets as $timesheet){
//             Timesheet::create([
//                 'id' => $timesheet->id,
//                 'date' => $timesheet->date,
//                 'user_id' => $timesheet->user_id,
//                 'vendor_id' => $timesheet->vendor_id,
//                 'project_id' => $timesheet->project_id,
//                 'hours' => $timesheet->hours,
//                 'amount' => $timesheet->amount,                
//                 'paid_by' => $timesheet->paid_by,
//                 'check_id' => $timesheet->check_id,
//                 'hourly' => $timesheet->hourly,
//                 'invoice' => $timesheet->invoice,
//                 'note' => $timesheet->note,
//                 'created_by_user_id' => $timesheet->created_by_user_id,                
//                 'created_at' => $timesheet->created_at,
//                 'updated_at' => $timesheet->updated_at,
//                 'deleted_at' => $timesheet->deleted_at,
//             ]);
//         }

//         //HOURS
//         $hours = $move_database->select('select * from hours_daily');
//         foreach($hours as $hour){
//             Hour::create([
//                 'id' => $hour->id,
//                 'date' => $hour->date,
//                 'hours' => $hour->hours,
//                 'project_id' => $hour->project_id,
//                 'user_id' => $hour->user_id,
//                 'vendor_id' => $hour->vendor_id,
//                 'timesheet_id' => $hour->hour_id,
//                 'created_by_user_id' => $hour->created_by_user_id,
//                 'note' => $hour->note,
//                 'created_at' => $hour->created_at,
//                 'updated_at' => $hour->updated_at,
//                 'deleted_at' => NULL,
//             ]);
//         }

// dd('DONE MOVING 1');

//         //EXPENSE RECEIPTS
//         $expense_receipts = $move_database->select('select * from expense_receipts_data');
//         foreach($expense_receipts as $expense_receipt){
//             ExpenseReceipts::create([
//                 'id' => $expense_receipt->id,
//                 'expense_id' => $expense_receipt->expense_id,
//                 'receipt_html' => $expense_receipt->receipt_html,
//                 'receipt_filename' => $expense_receipt->receipt == NULL || '' ? 'AAA NULL' : $expense_receipt->receipt,
//                 'created_at' => $expense_receipt->created_at,
//                 'updated_at' => $expense_receipt->updated_at,
//             ]);
//         }

//         //DISTRIBUTIONS
//         $distributions = $move_database->select('select * from distributions');
//         foreach($distributions as $distribution){
//             Distribution::create([
//                 'id' => $distribution->id,
//                 'name' => $distribution->name,
//                 'vendor_id' => $distribution->vendor_id,
//                 'user_id' => $distribution->user_id,
//                 'created_at' => $distribution->created_at,
//                 'updated_at' => $distribution->updated_at,
//             ]);
//         }

//         //COMPANY EMAILS
//         $company_emails = $move_database->select('select * from company_emails');
//         foreach($company_emails as $company_email){
//             CompanyEmail::create([
//                 'id' => $company_email->id,
//                 'vendor_id' => $company_email->vendor_id,
//                 'email' => $company_email->email,
//                 'created_at' => $company_email->created_at,
//                 'updated_at' => $company_email->updated_at,
//             ]);
//         }

//         //CLIENTS
//         $clients = $move_database->select('select * from clients');
//         foreach($clients as $client){
//             Client::create([
//                 'id' => $client->id,
//                 'business_name' => $client->business_name == NULL || '' ? NULL : $client->business_name,
//                 'address' => $client->address,
//                 'address_2' => $client->address_2 == NULL || '' ? NULL : $client->address_2,
//                 'city' => $client->city,
//                 'state' => $client->state,
//                 'zip_code' => $client->zip_code,
//                 'home_phone' => $client->home_phone,
//                 'source' => $client->source,
//                 'created_at' => $client->created_at,
//                 'updated_at' => $client->updated_at,
//             ]);
//         }

//         //CLIENT_USER
//         $client_users = $move_database->select('select * from client_user');
//         foreach($client_users as $client_user){
//             $client = Client::find($client_user->client_id);

//             if(is_null($client)){
//                 Log::channel('move_channel')->info(['client_user', $client_user]);
//                 continue;
//             }else{
//                 $client->users()->attach($client_user->user_id);
//                 continue;
//             }
//         }       

//         //CHECKS
//         $checks = $move_database->select('select * from checks');
//         foreach($checks as $check){
//             if($check->check == 2020202){
//                 $check_type = 'Cash';
//                 $check_number = NULL;
//             }elseif($check->check == 1010101){
//                 $check_type = 'Transfer';
//                 $check_number = NULL;
//             }else{
//                 $check_type = 'Check';
//                 $check_number = $check->check;
//             }

//             Check::create([
//                 'id' => $check->id,
//                 'check_type' => $check_type,
//                 'check_number' => $check_number,
//                 'date' => $check->date,
//                 'bank_account_id' => $check->bank_account_id,
//                 'user_id' => $check->user_id,
//                 'vendor_id' => $check->vendor_id,
//                 'belongs_to_vendor_id' => $check->belongs_to_vendor_id,
//                 'created_by_user_id' => $check->created_by_user_id,
//                 'created_at' => $check->created_at,
//                 'updated_at' => $check->updated_at,
//                 'deleted_at' => $check->deleted_at,
//             ]);
//         }

//         //TRANSACTIONS
//         $transactions = $move_database->select('select * from transactions');
//         foreach($transactions as $transaction){
//             Transaction::create([
//                 'id' => $transaction->id,
//                 'transaction_date' => $transaction->transaction_date,
//                 'posted_date' => $transaction->posted_date,
//                 'amount' => $transaction->amount,
//                 'bank_account_id' => $transaction->plaid_account_id,
//                 'vendor_id' => $transaction->vendor_id,
//                 'expense_id' => $transaction->expense_id,
//                 'check_id' => $transaction->check_id,
//                 'check_number' => $transaction->check_number,
//                 'deposit' => $transaction->deposit,
//                 'plaid_merchant_name' => $transaction->plaid_merchant_name,
//                 'plaid_merchant_description' => $transaction->plaid_merchant_name,
//                 'plaid_transaction_id' => $transaction->plaid_transaction_id,
//                 'created_at' => $transaction->created_at,
//                 'updated_at' => $transaction->updated_at,
//                 'deleted_at' => $transaction->deleted_at,
//             ]);
//         }

// dd('DONE MOVING 2');

//         //BANKS
//         $banks = $move_database->select('select * from banks');
//         foreach($banks as $bank){
//             Bank::create([
//                 'id' => $bank->id,
//                 'name' => $bank->bank_name,
//                 'plaid_access_token' => $bank->access_token == 'null' ? NULL : $bank->access_token,
//                 'plaid_item_id' => $bank->item_id,
//                 'vendor_id' => $bank->vendor_id,
//                 'plaid_ins_id' => $bank->plaid_ins_id,
//                 'plaid_options' => $bank->plaid_options,
//                 'created_at' => $bank->created_at,
//                 'updated_at' => $bank->updated_at,
//                 // 'deleted_at' => $bank->access_token == 'null' ? $bank->updated_at : NULL,
//             ]);
//         }

//         //BANK_ACCOUNTS
//         $bank_accounts = $move_database->select('select * from bank_accounts');
//         foreach($bank_accounts as $bank_account){
//             if($bank_account->type == 1){
//                 $account_status = 'Checking';
//             }elseif($bank_account->type == 2){
//                 $account_status = 'Savings';
//             }elseif($bank_account->type == 3){
//                 $account_status = 'Credit';
//             }

            
//             BankAccount::create([
//                 'id' => $bank_account->id,
//                 'vendor_id' => $bank_account->vendor_id,
//                 'bank_id' => $bank_account->bank_id,
//                 'account_number' => $bank_account->account_number,
//                 'plaid_account_id' => $bank_account->plaid_account_id,
//                 'type' => $account_status,
//                 'created_at' => $bank_account->created_at,
//                 'updated_at' => $bank_account->updated_at,
//                 'deleted_at' => NULL,
//             ]);
//         }

//         // EXPENSES
//         $expenses = $move_database->select('select * from expenses');
//         foreach($expenses as $expense){
//             //if $move_database->split_parent_expense_id = create new SplitExpense entry ..ids irrelevant?
//             if($expense->split_parent_expense_id){
//                 ExpenseSplits::create([
//                     'expense_id' => $expense->split_parent_expense_id,
//                     'amount' => $expense->amount,
//                     'project_id' => $expense->project_id,
//                     'distribution_id' => $expense->distribution_id,
//                     'belongs_to_vendor_id' => $expense->belongs_to_vendor_id,
//                     'reimbursment' => $expense->reimbursment == "0" ? NULL : $expense->reimbursment,
//                     'note' => $expense->note,
//                     'created_by_user_id' => $expense->created_by_user_id,
//                     'created_at' => $expense->created_at,
//                     'updated_at' => $expense->updated_at,
//                     'deleted_at' => $expense->deleted_at,
//                 ]);
//             }else{
//                 $new_expense = 
//                 Expense::create([
//                     'id' => $expense->id,
//                     'date' => $expense->expense_date,
//                     'amount' => $expense->amount,
//                     'project_id' => $expense->project_id,
//                     'distribution_id' => $expense->distribution_id,
//                     'vendor_id' => $expense->vendor_id,
//                     'paid_by' => $expense->paid_by,
//                     'belongs_to_vendor_id' => $expense->belongs_to_vendor_id,
//                     'invoice' => $expense->invoice,
//                     'parent_expense_id' => $expense->parent_expense_id,
//                     'check_id' => $expense->check_id,
//                     'reimbursment' => $expense->reimbursment == "0" ? NULL : $expense->reimbursment,
//                     'note' => $expense->note,
//                     'created_by_user_id' => $expense->created_by_user_id,
//                     'created_at' => $expense->created_at,
//                     'updated_at' => $expense->updated_at,
//                     'deleted_at' => $expense->deleted_at,
//                 ]);
//             }
//         }

//         //PROJECT STATUS
//         $project_status = $move_database->select('select * from projectstatuses');
//         foreach($project_status as $status){
//             if($status->vendor_id == 1){
//                 if($status->title_id == 1){
//                     $status_title = 'Estimate';
//                 }elseif($status->title_id == 2){
//                     $status_title = 'Awaiting Response';
//                 }elseif($status->title_id == 3){
//                     $status_title = 'Project Prep';
//                 }elseif($status->title_id == 4){
//                     $status_title = 'Scheduled';
//                 }elseif($status->title_id == 5){
//                     $status_title = 'Active';
//                 }elseif($status->title_id == 6){
//                     $status_title = 'Complete';
//                 }elseif($status->title_id == 7){
//                     $status_title = 'Canceled';
//                 }elseif($status->title_id == 8){
//                     $status_title = 'VIEW ONLY';
//                 }
    
//                 ProjectStatus::create([
//                     // 'id' => $status->id,
//                     'project_id' => $status->project_id,
//                     'belongs_to_vendor_id' => $status->vendor_id,
//                     'title' => $status_title,    
//                     'created_at' => $status->created_at,
//                     'updated_at' => $status->updated_at,
//                 ]);
//             }
//         }  

// dd('DONE MOVING 3');

    }
}

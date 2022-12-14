<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bank;
use App\Models\Check;
use App\Models\Expense;
use App\Models\Vendor;
use App\Models\Payment;
use App\Models\Distribution;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\ReceiptAccount;
use App\Models\VendorTransaction;

use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    //TEST ONLY //FOR DEVELOPER EXECUTION ONLY
    //only needed for test purposes...transactions update from Plaid.com webhooks
    //For use when Plaid API isn't acting as expected and can always be executed manually...

    // public function plaid_transactions_scheduled()
    // {        
    //     $banks = Bank::withoutGlobalScopes()->whereNotNull('plaid_access_token')->get();

    //     foreach($banks as $bank){
    //         $data = array(
    //             "client_id" => env('PLAID_CLIENT_ID'),
    //             "secret" => env('PLAID_SECRET'),
    //             "access_token" => $bank->plaid_access_token,
    //             "webhook_type" => 'TRANSACTIONS',
    //             "webhook_code" => 'DEFAULT_UPDATE', //TRANSACTIONS_REMOVED
    //             "new_transactions"=> 899
    //         );
  
    //         $this->plaid_transactions($bank, $data);
    //     }
    //     // return Log::channel('plaid_institution_info')->info('finished plaid_transactions_scheduled');
    // }

    //public function plaid_webhooks(Request $request)
    public function plaid_transactions_refresh()
    {
        $banks = Bank::withoutGlobalScopes()->whereNotNull('plaid_access_token')->get();

        foreach($banks as $bank){
            $new_data = array(
                "client_id" => env('PLAID_CLIENT_ID'),
                "secret" => env('PLAID_SECRET'),
                "access_token" => $bank->plaid_access_token,
            );
    
            $new_data = json_encode($new_data);
            //initialize session
            $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/transactions/refresh");
            //set options
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                ));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $new_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //execute session
            $result = curl_exec($ch);
            //close session
            curl_close($ch);
    
            $result = json_decode($result, true);
    
            dd($result);
        }
    }

    public function plaid_transactions_sync()
    {
        $banks = Bank::withoutGlobalScopes()->whereNotNull('plaid_access_token')->get();
        $bank_accounts = BankAccount::all();
        $transactions = Transaction::whereDate('transaction_date', '>=', '2021-01-01')->get();

        foreach($banks as $bank){
            $this->plaid_transactions_sync_bank($bank, $bank_accounts, $transactions);
        }
    }

    public function plaid_transactions_sync_bank(Bank $bank, $bank_accounts, $transactions)
    {
        ini_set('max_execution_time', '48000');

        $new_data = array(
            "client_id" => env('PLAID_CLIENT_ID'),
            "secret" => env('PLAID_SECRET'),
            "access_token" => $bank->plaid_access_token,
            "cursor" => isset($bank->plaid_options->next_cursor) ? $bank->plaid_options->next_cursor : NULL,
            "count" => 200,
        );

        $new_data = json_encode($new_data);
        //initialize session
        $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/transactions/sync");
        //set options
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $new_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //execute session
        $result = curl_exec($ch);
        //close session
        curl_close($ch);

        $result = json_decode($result, true);

        $next_cursor = array("next_cursor" => $result["next_cursor"],);
        $bank->plaid_options = json_encode(array_merge(json_decode(json_encode($bank->plaid_options), true), $next_cursor));
        $bank->save();

        if($result['has_more'] == true){
            $this->plaid_transactions_sync_bank($bank, $bank_accounts, $transactions);
        }else{
            Log::channel('plaid_adds')->info($result);
            //added
            foreach($result['added'] as $new_transaction){
                // //make sure transaction_id does not exist yet.. if it does..update..
                if($transactions->where('plaid_transaction_id', $new_transaction['pending_transaction_id'])->first()){
                    $transaction = $transactions->where('plaid_transaction_id', $new_transaction['pending_transaction_id'])->first();
                }elseif($transactions->where('plaid_transaction_id', $new_transaction['transaction_id'])->first()){
                    $transaction = $transactions->where('plaid_transaction_id', $new_transaction['transaction_id'])->first();
                }else{
                    $transaction = new Transaction;
                }

                //dates
                if($new_transaction['pending'] == TRUE){
                    $transaction->posted_date = NULL;
                }else{
                    $transaction->posted_date = $new_transaction['date'];
                }                      

                if($new_transaction['authorized_date'] == NULL){
                    $transaction->transaction_date = $new_transaction['date'];
                }else{
                    $transaction->transaction_date = $new_transaction['authorized_date'];
                }

                //if $transaction['merchant_name'] empty, use $new_transaction['name']
                if(isset($new_transaction['merchant_name'])){
                    $transaction->plaid_merchant_name = $new_transaction['merchant_name'];
                }else{
                    // $transaction->plaid_merchant_name = $new_transaction['name'];
                    $transaction->plaid_merchant_name = NULL;
                }

                $transaction->amount = $new_transaction['amount'];
                $transaction->plaid_merchant_description = $new_transaction['name'];
                $transaction->plaid_transaction_id = $new_transaction['transaction_id'];
                $transaction->bank_account_id = $bank_accounts->where('plaid_account_id', $new_transaction['account_id'])->first()->id;
                $transaction->save();
            }

            //modified
            foreach($result['modified'] as $new_transaction){
                //make sure transaction_id does not exist yet.. if it does..update..
                if($transactions->where('plaid_transaction_id', $new_transaction['pending_transaction_id'])->first()){
                    $transaction = $transactions->where('plaid_transaction_id', $new_transaction['pending_transaction_id'])->first();
                }elseif($transactions->where('plaid_transaction_id', $new_transaction['transaction_id'])->first()){
                    $transaction = $transactions->where('plaid_transaction_id', $new_transaction['transaction_id'])->first();
                }

                
                //if database $transaction->check_number isset, make it null in case its 0000
                // if(isset($transaction->check_number)){
                //     $transaction->check_number = NULL;
                // }
                
                if($transaction['check_id'] == NULL){
                    // $transaction->check_number = $new_transaction['check_number'];
                    $transaction->check_number = NULL;
                    $transaction->save();

                    if($new_transaction['check_number'] != NULL){
                        $transaction->check_number = $new_transaction['check_number'];
                    }
                }

                //dates
                if($new_transaction['pending'] == TRUE){
                    $transaction->posted_date = NULL;
                }else{
                    $transaction->posted_date = $new_transaction['date'];
                }                      

                if($new_transaction['authorized_date'] == NULL){
                    $transaction->transaction_date = $new_transaction['date'];
                }else{
                    $transaction->transaction_date = $new_transaction['authorized_date'];
                }

                //if $transaction['merchant_name'] empty, use $new_transaction['name']
                if(isset($new_transaction['merchant_name'])){
                    $transaction->plaid_merchant_name = $new_transaction['merchant_name'];
                }else{
                    // $transaction->plaid_merchant_name = $new_transaction['name'];
                    $transaction->plaid_merchant_name = NULL;
                }

                $transaction->amount = $new_transaction['amount'];
                $transaction->plaid_merchant_description = $new_transaction['name'];
                $transaction->plaid_transaction_id = $new_transaction['transaction_id'];
                $transaction->bank_account_id = $bank_accounts->where('plaid_account_id', $new_transaction['account_id'])->first()->id;
                $transaction->save();
            }

            //removed
            foreach($result['removed'] as $new_transaction){
                //make sure transaction_id does not exist yet.. if it does..update..
                $transaction = $transactions->where('plaid_transaction_id', $new_transaction['transaction_id'])->first();
                $transaction->deleted_at = now();
                $transaction->save();
            }
        }
    }

    //DIAGNOSE PLAID TRANSACTIONS
    public function plaid_transactions_get()
    {
        $new_data = array(
            "client_id"=> env('PLAID_CLIENT_ID'),
            "secret"=> env('PLAID_SECRET'),
            //bank access token
            "access_token"=> "access-production-ee3181e2-45b1-430a-a202-8d881aa1ff7c",
            "options" => array(
                "count"=> 90,
                "offset"=> 0
            ),
        );

        $new_data['start_date'] = Carbon::now()->subDays(45)->toDateString(); 
        $new_data['end_date'] = Carbon::now()->toDateString();

        $new_data = json_encode($new_data);

        //initialize session
        $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/transactions/get");
        //set options
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $new_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //execute session
        $result = curl_exec($ch);
        //close session
        curl_close($ch);

        $result = json_decode($result, true);

        $transactions = collect($result['transactions']);

        dd($transactions->where('transaction_id', '3J6RZZyw8Jh9oYN4pwJvizOqPdYkorTK584np'));
    }

    // public function plaid_transactions(Bank $bank, $data)
    // {
    //     for($i = 0; $i < $data['new_transactions'] + 100; $i+=100){
    //         $new_data = array(
    //             "client_id"=> env('PLAID_CLIENT_ID'),
    //             "secret"=> env('PLAID_SECRET'),
    //             "access_token"=> $bank->plaid_access_token,
    //             "options" => array(
    //                 "count"=> 90,
    //                 "offset"=> $i
    //             ),
    //         );

    //         if($data['webhook_type'] == 'TRANSACTIONS'){
    //             if($data['webhook_code'] == 'HISTORICAL_UPDATE'){

    //             }elseif($data['webhook_code'] == 'DEFAULT_UPDATE'){
    //                 //4-11-2020: unless new vendor (45 days), use last Plaid Update Date for this Bank as start date
    //                 // $bank_add_date = Carbon::create($bank->vendor->cliff_registration->vendor_registration_date);

    //                 // if($bank_add_date->lessThan(today()->subDays(14))){
    //                 //     $new_data['start_date'] = Carbon::now()->subDays(45)->toDateString();  
    //                 // }else{
    //                 //     $new_data['start_date'] = $bank_add_date->toDateString();
    //                 // }

    //                 $new_data['start_date'] = Carbon::now()->subDays(45)->toDateString(); 
    //                 $new_data['end_date'] = Carbon::now()->toDateString();
    //             }elseif($data['webhook_code'] == 'TRANSACTIONS_REMOVED'){
    //                 dd('in transactions_removed');
    //                 //remove these transactions (soft)
    //                 // Log::channel('plaid')->info($data);
    //                 // foreach($data['removed_transactions'] as $transaction_plaid_id){
    //                 //     $transaction = Transaction::withoutGlobalScopes()->where('plaid_transaction_id', $transaction_plaid_id)->first()->delete();
    //                 // }
    //             }
    //         }

    //         $new_data = json_encode($new_data);

    //         //initialize session
    //         $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/transactions/get");
    //         //set options
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //             'Content-Type: application/json',
    //             ));
    //         curl_setopt($ch, CURLOPT_POST, true);
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $new_data);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         //execute session
    //         $result = curl_exec($ch);
    //         //close session
    //         curl_close($ch);

    //         $result = json_decode($result, true);

    //         // dd($result);
         
    //         //if institution is in error, continue loop
    //         if(!isset($result['transactions'])){
    //             //04-11-2022 SAVE INSTITITION ERROR TO LOG
    //             continue;
    //         }

    //         //TEST ONLY! COMMENT OUT FOR PRODUCTION next 3 lines
    //         // $transactions_per_bank_account = collect($result['transactions'])->where('account_id', 'oyBJqz36ROUqK7vbwwXEfVnnmyLnnLHB5aje0');
    //         // dd($transactions_per_bank_account);
    //         // $result['transactions'] = array($result['transactions'][7]);
    //         // dd($result['transactions']);
    //         // $transactions_found = collect($result['transactions']);
    //         // dd($transactions_found->first()->account_id);

    //         foreach($result['transactions'] as $key => $transaction)
    //         {
    //             //4-11-2022 -- only get transactions where the BankAccount matches instead of doing all these loops below to filter. $result['transactions'] should be an eloquent collection
    //             $bank_account = BankAccount::withoutGlobalScopes()->where('plaid_account_id', $transaction['account_id'])->first();

    //             if(is_null($bank_account)){
    //                 //6-4-2022 LOG
    //                 continue;
    //             }

    //             //$same_accounts = Bank::where('vendor_id', $bank->vendor_id)->where('plaid_ins_id', $result['item']['institution_id'])->pluck('id');
    //             $same_accounts = BankAccount::withoutGlobalScopes()->where('vendor_id', $bank->vendor_id)->where('bank_id', $bank->id)->pluck('id');

    //             $start_date = Carbon::parse($transaction['date'])->subDays(10)->format('Y-m-d');
    //             $end_date = Carbon::parse($transaction['date'])->addDays(10)->format('Y-m-d');

    //             $duplicate_transaction_id = Transaction::withoutGlobalScopes()->whereNotNull('plaid_transaction_id')->where('plaid_transaction_id', $transaction['transaction_id'])->first();
    //             // dd($duplicate_transaction_id);
    //             //if plaid_transaction_id not found... try to find the Plaid Transaction another way...
    //             if(is_null($duplicate_transaction_id)){
    //                 //get all transactions with same amount, simuilar date, and same Ins_id, exclude $this->bank_account->id
    //                 $pending_transaction = Transaction::withoutGlobalScopes()->whereNotNull('plaid_transaction_id')->where('plaid_transaction_id', $transaction['pending_transaction_id'])->first();

    //                 //if $transaction['merchant_name'] empty, use $transaction['name']
    //                 if(isset($transaction['merchant_name'])){
    //                     $transaction_plaid_merchant_name = $transaction['merchant_name'];
    //                 }else{
    //                     $transaction_plaid_merchant_name = $transaction['name'];
    //                 }
                    
    //                 $transaction_plaid_merchant_desc = $transaction['name'];

    //                 if(!is_null($pending_transaction)){
    //                     $transaction_save = $pending_transaction;
    //                     $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
    //                     if($transaction['pending'] == true){
    //                         $transaction_save->posted_date = NULL;
    //                     }else{
    //                         $transaction_save->posted_date = $transaction['date'];
    //                     }                      

    //                     if($transaction['authorized_date'] == null){
    //                         $transaction_save->transaction_date = $transaction['date'];
    //                     }else{
    //                         $transaction_save->transaction_date = $transaction['authorized_date'];
    //                     }

    //                     //plaid_transaction_id
    //                     $transaction_save->amount = $transaction['amount'];
    //                     $transaction_save->plaid_merchant_name = $transaction_plaid_merchant_name;
    //                     $transaction_save->plaid_merchant_description = $transaction_plaid_merchant_desc;
    //                     $transaction_save->save();
    //                 }else{
    //                     $transactions_search_and_database = collect($result['transactions'])->where('amount', $transaction['amount'])->pluck('transaction_id');

    //                     // dd($transactions_search_and_database);
    //                     $transactions_same_plaid_inst = Transaction::whereNotIn('plaid_transaction_id', $transactions_search_and_database)->whereIn('bank_account_id', $same_accounts)->where('amount', $transaction['amount'])->whereBetween('transaction_date', [$start_date, $end_date])->get();
    //                     //whereNotIn('id', $transactions_search_and_database)
    //                     // ->where('plaid_merchant_name', $transaction['name'])
    //                     // ->where('plaid_transaction_id', '!=', $transaction['transaction_id'])
    //                     // dd($transactions_same_plaid_inst);

    //                     //no other transactions matching...save a new Transaction
    //                     if($transactions_same_plaid_inst->isEmpty()){
    //                         // dd('if');
    //                         // dd($result);
    //                         $transaction_save = new Transaction;

    //                         if($transaction['pending'] == true){
    //                             $transaction_save->posted_date = NULL;
    //                         }else{
    //                             $transaction_save->posted_date = $transaction['date'];
    //                         }                      

    //                         if($transaction['authorized_date'] == null){
    //                             $transaction_save->transaction_date = $transaction['date'];
    //                         }else{
    //                             $transaction_save->transaction_date = $transaction['authorized_date'];
    //                         }

    //                         $transaction_save->amount = $transaction['amount'];
    //                         $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
    //                         $transaction_save->bank_account_id = $bank_account->id;
    //                         // $transaction_save->bank_id = $bank->id;

    //                         $transaction_save->plaid_merchant_name = $transaction_plaid_merchant_name;
    //                         $transaction_save->plaid_merchant_description = $transaction_plaid_merchant_desc;
    //                         $transaction_save->save();
    //                     }else{
    //                         // dd($transaction);
    //                         // dd($transactions_same_plaid_inst);
    //                         //if 1 or none or mupliple found
    //                         if($transactions_same_plaid_inst->count() >= 1){
    //                             foreach($transactions_same_plaid_inst as $row_duplicate){
    //                                 $row_duplicate->date_diff = $row_duplicate->transaction_date->floatDiffInDays($transaction['date']);    
    //                             }

    //                             $duplicate_row = $transactions_same_plaid_inst->sortBy('date_diff')->first();
    //                             // dd($duplicate_row);
    //                             $transaction_save = Transaction::findOrFail($duplicate_row->id);
    //                             // dd(Transaction::where('id', $duplicate_row->id)->first());
    //                             $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
    //                             $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
    //                             $transaction_save->posted_date = $transaction['date'];
    //                             if($transaction['authorized_date'] == null){
    //                                 $transaction_save->transaction_date = $transaction['date'];
    //                             }
    //                             // else{
    //                             //     $transaction_save->transaction_date = $transaction['authorized_date'];
    //                             // }
    //                             $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
    //                             $transaction_save->plaid_merchant_name = $transaction_plaid_merchant_name;
    //                             $transaction_save->plaid_merchant_description = $transaction_plaid_merchant_desc;
    //                             $transaction_save->save();
    //                             //if $transactions_same_plaid_inst->count() == more than 1 do more diagnostics..?
    //                         }else{
    //                             // dd('else else');
    //                         }
    //                     }
    //                 }
    //             }else{
    //                 //check if the existing transaction id has nay changed info?
    //                 //pending_transaction_id
    //                 //dd(['in else else', $transaction]);
    //             }
    //             //otherwise if there's a dupliate, check if it's posted. if not posted yet, continue, if posted, save 'posted_date'
    //         }
    //     } //for loop  
    // }

    public function add_vendor_to_transactions()
    {     
        $transaction_bank_accounts = BankAccount::withoutGlobalScopes()->where('vendor_id', 1)->pluck('id')->toArray();
        $transactions = Transaction::TransactionsSinVendor()->whereIn('bank_account_id', $transaction_bank_accounts)->get()->groupBy('plaid_merchant_description');
        $vendors = Vendor::withoutGlobalScopes()->where('business_type', 'Retail')->get();

        foreach($transactions as $merchant_name => $merchant_transactions){
            //find vendor where vendor->business_name is contained in $merchant_name
            // $vendor_match = preg_grep("/^" . $merchant_name . "/i", $vendors->pluck('business_name')->toArray());

            // dd(Transaction::where('id', 17124)->first()->bank_account->vendor);
            $vendor_match = $vendors->where('business_name', $merchant_name)->first();

            if($vendor_match){           
                foreach($merchant_transactions as $key => $transaction){
                    $transaction->vendor_id = $vendor_match->id;
                    $transaction->save();
                }
                //USED IN MULTIPLE OF PLACES MatchVendor@store, ExpesnesForm@createExpenseFromTransaction, below in CHECK VendorTransaction code in this function as well
                //add vendor if vendor is not part of the currently logged in vendor

                //->vendor->vendors->contains($transaction->vendor_id)
                // dd($transaction->bank_account->withoutGlobalScopes()->vendor->withoutGlobalScopes());
                if(!$transaction->bank_account->vendor->vendors->contains($transaction->vendor_id)){
                    $transaction->bank_account->vendor->vendors()->attach($transaction->vendor_id);
                }
            }
        }

        //CHECK VendorTransaction table
        $vendor_transactions = VendorTransaction::whereNull('deposit_check')->get();

        foreach($vendor_transactions as $vendor_transaction){
            //get all BankAccount where bank_account_id 
            //get plaid_inst_id of bank_account_ids on transactions table

            //Alter $transactions variable/results based on the if statement below

            // dd($vendor_transaction);
            foreach($transactions as $vendor_name => $plaid_name_transactions){
                // dd($vendor_name);
                // dd($plaid_name_transactions);
                // if($vendor_transaction->plaid_inst_id){
                //     //6-11-2022 way too code heavy!!!...!!!
                //     $vendor_inst_id = $plaid_name_transactions->first()->bank_account->bank->plaid_ins_id;

                //     if($vendor_inst_id == $vendor_transaction->plaid_inst_id){
                //         dd(Transaction::TransactionsSinVendor()->get());
                //         dd('foreach if if');
                //     }else{
                //         // dd($transactions);
                //         dd('foreach if else');
                //         //else if not bank specific use ALL $transactions ... aka no need for this eles..
                //     }
                // }else{
                //     dd('NOT specific bank/inst Transactions/All Transactions');
                // }

                // $vendor_desc = $plaid_name_transactions->first()->plaid_merchant_description;
                $vendor_name = $vendor_name . ' ' . $plaid_name_transactions->first()->plaid_merchant_name;
                //decode json on VendorTrasaction Model
                $preg = json_decode($vendor_transaction->options);
                preg_match('/'. $vendor_transaction->desc . $preg, $vendor_name, $matches, PREG_UNMATCHED_AS_NULL);

                if(!empty($matches)){
                    foreach($plaid_name_transactions as $key => $transaction){
                        $transaction->vendor_id = $vendor_transaction->vendor_id;
                        $transaction->save();
                        
                        //USED IN MULTIPLE OF PLACES MatchVendor@store, above in original Vendor find code in this function as well
                        //add vendor if vendor is not part of the currently logged in vendor
                        if(!$transaction->bank_account->vendor->vendors->contains($transaction->vendor_id)){
                            $transaction->bank_account->vendor->vendors()->attach($transaction->vendor_id);
                        }
                    }
                }
            }
        }
    }

    public function add_check_deposit_to_transactions()
    {
        $institutions = VendorTransaction::whereNotNull('plaid_inst_id')->groupBy('plaid_inst_id')->pluck('plaid_inst_id');

        // dd($institutions);
        //split by institution
        foreach($institutions as $institution){
            //06/29/2021 NEED TO SHARE THIS WITH TrancationController@store_csv_array.. same code x2 
            $institution_bank_ids = Bank::withoutGlobalScopes()->where('plaid_ins_id', $institution)->pluck('id');
            $institution_bank_ids = BankAccount::whereIn('bank_id', $institution_bank_ids)->pluck('id');

            // dd($institution_bank_ids);
            $deposit_check_types = VendorTransaction::groupBy('deposit_check')->where('plaid_inst_id', $institution)->pluck('deposit_check');

            // dd($deposit_check_types);
            //split by check_type of each institution (multiple of bank_ids)
            foreach($deposit_check_types as $deposit_check_type){
                //same for type 2 and 3 (check and transfer)
                $transaction_check_desc = VendorTransaction::where('deposit_check', $deposit_check_type)->where('plaid_inst_id', $institution)->pluck('desc');

                $transactions = Transaction::
                    where('expense_id', NULL)
                    ->where('vendor_id', NULL)
                    ->where('check_number', NULL) 
                    ->where('check_id', NULL)                      
                    ->where('deposit', NULL)
                    ->whereNotNull('transaction_date')
                    ->whereIn('bank_account_id', $institution_bank_ids)
                    //Same where clause used $this->createVendorTransactions 6/10/2021
                    ->where(function ($query) use($transaction_check_desc) {
                         for ($i = 0; $i < count($transaction_check_desc); $i++){
                            //  dd($transaction_check_desc[$i]);
                             //first or whitespace(need to implement 6/10/2021) before query only 6/10/21..instead of preg loop
                            $query->orWhere('plaid_merchant_description', 'like', '%' . $transaction_check_desc[$i] . '%');
                            //'like', '%' . $transaction_check_desc[$i]
                         }      
                    })
                    ->get();

                // dd($transactions);

                foreach($transactions as $transaction){
                    //preg here after $transactions are gathered or should it be before?...trying to do this in the LIKE statement above instead 6/10/2021
                    //NEED A WAY TO INCLUDE BILL PAY (6) IN THIS CODE

                    //CHECK
                    if($deposit_check_type == 2){
                        // dd($deposit_check_type);
                        //if transaction_desc = "CHECK" and no number...it saves as check_number "0"..need to change.. but we account for this in $this->add_check_id_to_transactions 06/23/2021
                        $re = '/\d{3,}/';
                        $str = $transaction->plaid_merchant_description;
                        preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);
      
                        if(isset($matches[0][0])){
                            $check_number = $matches[0][0];
                            $transaction->check_number = $check_number;
                            
                            if($check_number != "0000"){
                                $transaction->check_number = $check_number;
                            }                          
                        }

                    //TRANSFER
                    }elseif($deposit_check_type == 3){
                        $transaction->check_number = '1010101';

                    //DEPOSIT
                    }elseif($deposit_check_type == 1){
                        $transaction->deposit = 1; //yes, transaction has a deposit

                    //CASH
                    }elseif($deposit_check_type == 4){
                        $transaction->check_number = '2020202';
                    }else{
                        continue;
                    }

                    $transaction->save();
                } 
            }
        }
    }

    public function add_expense_to_transactions()
    {
        //OLD: $cliff_vendors = Vendor::where('cliff_registration->vendor_registration_complete', 'true')->get();
        $cliff_vendors = Vendor::withoutGlobalScopes()->where('business_type', 'Sub')->where('id', 1)->get();

        foreach($cliff_vendors as $cliff_vendor){
            $cliff_vendor_bank_account_ids = $cliff_vendor->bank_accounts->pluck('id');

            $expenses = Expense::withoutGlobalScopes()
                ->with('transactions')
                ->with('receipts')
                ->whereNull('deleted_at')
                ->where('belongs_to_vendor_id', $cliff_vendor->id)
                ->whereNotNull('vendor_id')
                //where transacitons->sum != $expense(item)->sum  \\ whereNull checked_at (transactions add up to expense)
                ->whereDate('date', '>=', Carbon::now()->subMonths(3))
                // ->whereBetween('date', [$start_date, $end_date])
                ->get();

            foreach($expenses as $expense){
                $start_date = $expense->date->subDays(3)->format('Y-m-d');
                $end_date = $expense->date->addDays(7)->format('Y-m-d');

                //4-20-2021 transaction->amount cannot be more than expense->amount? 
                $transaction_amount_outstanding = $expense->amount - $expense->transactions->sum('amount');

                if($transaction_amount_outstanding == 0){
                    continue;
                }

                //6/1/2021 is the amount negative or positive? combine into 1 .. 
                if(substr($transaction_amount_outstanding,0,1) == '-'){
                    //amount is negative
                    $transactions = Transaction::
                        whereIn('bank_account_id', $cliff_vendor_bank_account_ids)
                        ->whereNull('expense_id')
                        ->where('vendor_id', $expense->vendor_id)
                        ->where('check_number', NULL)
                        ->where('amount', 'like', '-%')
                        // ->where('amount', '<=', $transaction_amount_outstanding)
                        ->whereBetween('transaction_date', [$start_date, $end_date])
                        ->get();
                }else{
                    //amount is positive...
                    $transactions = Transaction::
                        whereIn('bank_account_id', $cliff_vendor_bank_account_ids)
                        ->whereNull('expense_id')
                        ->where('vendor_id', $expense->vendor_id)
                        ->whereNull('check_number')
                        ->where('amount', '<=', $transaction_amount_outstanding)
                        ->whereBetween('transaction_date', [$start_date, $end_date])
                        // ->where('id', 12660)
                        // ->orderBy('transaction_date', 'desc')
                        ->get();
                }

                //track which transaction/s/combos we have tried
                foreach($transactions as $transaction){
                    if($transaction->amount == $expense->amount){
                        // $transaction = $transaction->getOriginal();
                        // dd($transaction);
                        $transaction->expense()->associate($expense);
                        $transaction->save();

                        continue 2;
                    }

                    if(!$expense->receipts->isEmpty()){
                        $receipt_text = $expense->receipts->first()->receipt_html;
                        $re = '/(-|-\$|\()?((\d{1,3})([,])(\d{1,3})([.,]))\d{1,2}|(-|-\$|\()?(\d{1,3})([.,])\d{1,2}/m';
                        // $re = '/(-|-\$|\()?(\d{1,3})([.])\d{1,2}/m'; 4/30/21
                        $str = $receipt_text;
                        preg_match_all($re, $str, $matches, PREG_OFFSET_CAPTURE);

                        $result = $str;
                        $results[] = $str;

                        $expense_text_amounts = [];
                        foreach($matches[0] as $key => $match){
                            //count backwards 3, if character is comma, change to dot.
                            if(substr($match[0], -3, 1) == ','){
                                //change this comma to decimal
                                $match[0][-3] = '.';
                            }

                            $match[0] = str_replace(',', '', $match[0]);
                            $match[0] = str_replace('(', '-', $match[0]);
                            $match[0] = preg_replace('/\$/', '', $match[0]);
               
                            $expense_text_amounts[] = number_format($match[0], 2, '.', '');
                        }

                        if(in_array($transaction->amount, $expense_text_amounts)){
                            $transaction->expense()->associate($expense);
                            $transaction->save();

                            continue 2;
                        }else{
                            //add to database `expense_transaction'... this transaction was not found in the text of this expense and should be excluded from $transactions query above
                        }
                    }
                } //foreach $transactions
            } //foreach $expenses
        }    
    }

    public function add_check_id_to_transactions()
    {
        $checks = Check::withoutGlobalScopes()
                ->whereDoesntHave('transactions')
                ->where('check_type', '!=', 'Cash')
                ->orderBy('date', 'DESC')
                ->where('date', '>', '2019-01-01')
                // ->where('id', 2286)
                ->get();

        foreach($checks as $check){
            // elseif($check->check_type == 'Cash'){
            //     $check_number = '2020202';
            // }
            if($check->check_type == 'Transfer'){
                $check_number = '1010101';
            }elseif($check->check_type == 'Check'){
                $check_number = $check->check_number;
            }else{
                continue;
            }          

            if($check->check_type == 'Check'){
                $transactions = Transaction::withoutGlobalScopes()
                    ->whereNull('deleted_at')
                    ->whereNull('check_id')
                    ->where('check_number', $check_number)
                    ->whereBetween('transaction_date', [
                            $check->date->format('Y-m-d'), 
                            $check->date->addDays(385)->format('Y-m-d')
                            ])
                    ->orderBy('id', 'DESC')
                    ->get();
            }elseif($check->check_type == 'Transfer'){
                //if $check_number = Transfer
                $transactions = Transaction::withoutGlobalScopes()
                    ->whereNull('deleted_at')
                    ->whereNull('check_id')
                    ->where('check_number', $check_number)
                    ->whereBetween('transaction_date', [
                            $check->date->subDays(30)->format('Y-m-d'), 
                            $check->date->addDays(365)->format('Y-m-d')
                            ])
                    ->where('amount', $check->amount)
                    ->orderBy('id', 'DESC')
                    ->get();
            }else{
                Log::channel('add_check_id_to_transactions')->info($check);
                continue;
            }            

            if($transactions->count() == 1){
                //if check_number matches, that's the one
                //if not BUT if amount matches, that's the one
                $transactions->first()->check()->associate($check)->save();
            }else{
                Log::channel('add_check_id_to_transactions')->info($check);
            }
        }  
    }

    public function REMVOE_FOR_TEXT_ONLY_add_check_id_to_transactions()
    {
        //NOTES: 
            //using withoutGlobalScopes() in this function. Each of these queries MUST be accompanied by plaid_account_id to make sure vendor-specific data is compared.
            //1/18/2021 mutated values will break the code. Always $check->getRawOriginal('check') any mutated values....OR work that into Model code. Usually fails if the mudated value logic required Auth::user()
        $transactions = Transaction::withoutGlobalScopes()->whereNull('deleted_at')->whereNotNull('check_number')->whereNull('check_id')->orderBy('id', 'DESC')->get();

        foreach($transactions as $transaction){
            //bank_account no longer used ? bank_account id 8
            if(is_null($transaction->bank_account)){
                continue;
            }

            //need a way to match checks and transactions, ignoring amount...opposite of the Else statement below that finds them by amount only.
            //get all $transaction->plaid_account_ids
            $plaid_ins_id = Bank::withoutGlobalScopes()->find($transaction->bank_account->bank_id)->plaid_ins_id;
            $banks = Bank::withoutGlobalScopes()->where('plaid_ins_id', $plaid_ins_id)->pluck('id');
            $bank_accounts = BankAccount::withoutGlobalScopes()->whereIn('bank_id', $banks)->pluck('id');
            
            if($transaction->check_number == 1010101){
                $check_type = 'Transfer';
            }elseif($transaction->check_number == 2020202){
                $check_type = 'Cash';
            }else{
                $check_type = 'Check';
            }

            $transaction_checks = 
                Check::withoutGlobalScopes()
                ->whereDoesntHave('transactions')
                ->whereIn('bank_account_id', $bank_accounts)
                ->where('check_type', $check_type)
                ->whereBetween('date', [$transaction->transaction_date->subDays(385)->format('Y-m-d'), $transaction->transaction_date->format('Y-m-d')])->get();
            //match amount first
            if($transaction_checks->where('amount', str_replace('-','',$transaction->amount))){
                $transaction_checks = $transaction_checks->where('amount', str_replace('-','',$transaction->amount));
            }elseif($transaction_checks->where('amount', str_replace('-','',$transaction->amount))->isEmpty()){
                $transaction_checks = $transaction_checks->where('check_number', $transaction->check_number);
            }else{
                //only if check_type = Check do a check_number constraint
                if($check_type == 'Check'){
                    $transaction_checks = $transaction_checks->where('check_number', $transaction->check_number);
                }
                // else{
                //     $transaction_checks = $transaction_checks->where('amount', str_replace('-','',$transaction->amount));
                // }              
            }

            if($transaction_checks->count() == 1){
                $check = $transaction_checks->first();
                // dd($check->amount . ' | ' .$transaction->amount);
                if(isset($check)){
                    $transaction->check()->associate($check);
                    $transaction->save();
                }else{
                    //remove $transaction from $transactions collection
                    //is this needed?!
                    // $transactions->forget($key);
                }
                $transaction = NULL;
            }
            
            // else{

            // }
            
            // else{
            //     continue;
            // }

            //when Institution/Bank check_number is not same as actual Check/Cliff Construction check_number but same Amount OR is the same (CASE STUDY: check #1737 from Citi / plaid_account_id = 4 ) but returned check happened and even a successful retry happened. All 3 transactions will link to the Check
            // $similar_check_numbers = collect();
            // foreach($checks as $check){
            //     if(strpos((string)$transaction->check_number, (string)$check->getRawOriginal('check_number')) !== false){
            //         //checks with similar numbers
            //         $similar_check_numbers[] = $check;
            //     }
            // }
            // dd($similar_check_numbers);

            // if($similar_check_numbers->count() == 1){
            //     $check = $similar_check_numbers->first();    
            // }elseif($similar_check_numbers->count() > 1){
            //     continue;

            //     // foreach($similar_check_numbers as $check){
            //     //     $check->date_diff = $transaction->transaction_date->floatDiffInDays($check->date);   
            //     // }
            //     // //07/03/2021 NO! Can't match if dates are way different 
            //     // $check = $similar_check_numbers->sortBy('date_diff')->first();
            // }else{
            //     continue;

            //     // //need a way to match checks and transactions, ignoring amount...opposite of the Else statement below that finds them by amount only.
            //     // $checks = Check::withoutGlobalScopes()->whereIn('bank_account_id', $bank_accounts)->where('check_number', $transaction->check_number)->whereDoesntHave('transactions')->get();
            //     // if($checks->count() == 1){
            //     //     $check = $checks->first();
            //     // }else{
            //     //     // //Find by amount only.. BE CAREFUL! NEED MORE TESTS 1/18/2021
            //     //     continue;                    
            //     //     //     //->with('transactions')
            //     //     // $checks = Check::withoutGlobalScopes()->whereIn('bank_account_id', $bank_accounts)->where('total', str_replace('-','',$transaction->amount))->whereDoesntHave('transactions')->get();
            //     //     // // dd($checks);
            //     //     // foreach($checks as $check){
            //     //     //     $check->date_diff = $transaction->transaction_date->floatDiffInDays($check->date);
            //     //     // }
            //     //     // // dd($transaction);
            //     //     // if($checks->count() >= 1){
            //     //     //     //NO! Can't match if dates are way different (date_diff <= 99 days)..works for all by common amounts $200, $1500 07/03/2021
            //     //     //     if($checks->sortBy('date_diff')->first()->date_diff <= 7){
            //     //     //         //if the 7 day constrain doesn't work, add an if statement.. if over 7 days confirm check was created over 14 days ago, then find up to 21 days (for now) and match
            //     //     //         $check = $checks->sortBy('date_diff')->first();
            //     //     //         //if check_number does or amount does not match..needs another if?
            //     //     //         $transaction->check_number = $check->getRawOriginal('check');
            //     //     //         $transaction->save();
            //     //     //     }else{
            //     //     //         if($checks->sortBy('date_diff')->first()->created_at->diffInDays() <= 10){
            //     //     //             $check = NULL;
            //     //     //         }else{
            //     //     //             if($checks->sortBy('date_diff')->first()->date_diff <= 21){
            //     //     //                 $check = $checks->sortBy('date_diff')->first();
            //     //     //                 //if check_number does or amount does not match..needs another if?
            //     //     //                 $transaction->check_number = $check->getRawOriginal('check');
            //     //     //                 $transaction->save();
            //     //     //             }else{
            //     //     //                 $check = NULL;
            //     //     //             }
            //     //     //         }
            //     //     //     }
            //     //     // }else{
            //     //     //     $check = NULL;
            //     //     // }
            //     // }
            // }

            // dd($check);

            // if(isset($check)){
            //     $transaction->check()->associate($check);
            //     $transaction->save();
            // }else{
            //     //remove $transaction from $transactions collection
            //     //is this needed?!
            //     $transactions->forget($key);
            // }
        } //transactions foreach
    }

    public function add_payments_to_transaction()
    {
        //where doesnt have clientpayment
        //why does 2017/older transactions/client_payments not work?
        $transactions = Transaction::
            where('transaction_date', '>', '2019-01-01')
            ->where('deposit', 1)
            ->whereDoesntHave('payments')
            ->whereNull('expense_id')
            // ->where('id', 17556)
            ->get();

        // dd($transactions);
        foreach($transactions as $transaction){
            // dd($transaction);
            $vendor_id = $transaction->bank_account->bank->vendor_id;
            //reset payments variable?
            $payments = Payment::
                // withoutGlobalScopes()
                whereBetween('date', [$transaction->transaction_date->subDays(90), $transaction->transaction_date->addDays(7)])
                //where bank_id belongs_to same vendor_id as this payment
                ->where('belongs_to_vendor_id', $vendor_id)
                ->where('transaction_id', NULL)
                // ->where('amount', substr($transaction->amount, 1))
                ->get();

                //json store which $transactions have been checked against which $payments so it doesnt check again?
                //where parent_client_payment_id is not in json for this $transaction
                // ->groupBy('parent_client_payment_id');

            // dd($payments);

            if(!$payments->isEmpty()){
                //try any of $payments->payment_total ($payment->sum('amount')) == $transaction->amount? if so and only one result..that's our guy. 
          
                //clear array before next foreach statement
                $payment_results = array();
                
                $client_payment_ids = $payments->pluck('id')->toArray();
                $client_payments_plucked = $payments->pluck('amount')->toArray();
                
                $arr = array_values(array_filter($client_payments_plucked));
                $n = sizeof($arr); 
                $ids = $client_payment_ids;

                $results = collect($this->subsetSums($arr, $n, $ids))->sortBy('sum');

                // dd($results);
                
                foreach ($results as $key => $result) {
                    $sum = number_format($result['sum'], 2, '.', '');
                    //this can happen multiple of times.. eg transaction_id 6230

                    //is this Transaction a RETURN CHECK "DEPOSIT"?
                    if($sum === substr($transaction->amount, 1) OR $sum === '-' . $transaction->amount){
                        $payment_results[] = $result;           
                    }else{
                        //if not found... create json array for $transaction with all parent_client_payment_id s so that we dont have to run this heavy program for those payments again.
                        //06/10/2021 we do the above line already with add_transactions_to_expenses... data is put into database... need it here too
                    }
                }

                $payment_results = collect($payment_results);

                // dd($payment_results);

                if(!$payment_results->isEmpty()){
                    $payment_array = $payment_results[0]['transactions'];
                    if(count($payment_results) == 1){
                        foreach($payment_array as $payment){
                            $save_payment = Payment::findOrFail($payment['client_payment_id']);
                            $save_payment->transaction_id = $transaction->id;
                            $save_payment->save();
                        }
                    }
                }
            }
        }            
    }

    // Iterative PHP program to print  
    // sums of all possible subsets. 
    // Prints sums of all subsets of array  
    public function subsetSums($arr, $n, $ids)  
    {  
        ini_set('max_execution_time', 600000);
        // There are totoal 2^n subsets  
        $total = 1 << $n; 
        // $sums = array();

        // Consider all numbers 
        // from 0 to 2^n - 1  
        for ($i = 0; $i < $total; $i++)  
        {  
            $sum = 0;  
            $summy = array();
            // Consider binary reprsentation of  
            // current i to decide which elements  
            // to pick.  
            for ($j = 0; $j < $n; $j++){
                if ($i & (1 << $j)){
                   $sum += $arr[$j];
                   $summy[] = array('subtotal' => $arr[$j], 'client_payment_id' => $ids[$j]);
                   // dd($summy);
                } 
            }                
      
            // Print sum of picked elements.  
            // echo $sum , " "; 
            if($sum != 0){
                $summys[] = ['sum' => $sum, 'transactions' => $summy];
            } 
        }  

        return $summys;
    }

    public function find_credit_payments_on_debit()
    {
        //group bank_accounts per vendor
        $vendors_credit_bank_accounts = 
            BankAccount::
                withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->where('type', 'Credit')
                ->get()
                ->groupBy('vendor_id');

        foreach($vendors_credit_bank_accounts as $vendor_id => $vendor_bank_accounts){
            $vendor = Vendor::findOrFail($vendor_id);
            $vendor_office_distribution_id = Distribution::withoutGlobalScopes()->where('vendor_id', $vendor_id)->where('name', 'OFFICE')->first()->id;
            $bank_account_ids = $vendors_credit_bank_accounts[$vendor_id]->pluck('id');
            $vendors_debit_bank_accounts = BankAccount::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('deleted_at', NULL)->where('type', 'Checking')->get();            

            $credit_transactions = 
                Transaction::
                    where('check_id', NULL)
                    // ->where('id', 17325)
                    ->where('expense_id', NULL)
                    ->where('check_number', NULL)
                    ->where('deposit', NULL)
                    ->whereNotNull('vendor_id')
                    ->whereDate('transaction_date', '>=', '2022-10-07')
                    ->whereIn('bank_account_id', $bank_account_ids) //where bank_id_account is a credit card for this user
                    ->orderBy('transaction_date', 'ASC')
                    ->get();

            foreach($credit_transactions as $credit_transaction){
                $debit_transactions = 
                    Transaction::
                        where('amount', substr($credit_transaction->amount, 1))
                        ->where('vendor_id', $credit_transaction->vendor_id)
                        ->whereIn('bank_account_id', $vendors_debit_bank_accounts->pluck('id')) //and where bank_type = DEBIT
                        ->where('expense_id', NULL)
                        //->subDays(2)
                        ->whereBetween('transaction_date', [$credit_transaction->transaction_date, $credit_transaction->transaction_date->addDays(5)])
                        //where what else?
                        ->get();

                //can we add a Model attribute in the above Where statement?! --I dont think so
                foreach($debit_transactions as $debit_transaction){
                    $debit_transaction->date_diff = $credit_transaction->transaction_date->floatDiffInDays($debit_transaction->transaction_date);                
                }

                $debit_transaction = $debit_transactions->sortBy('date_diff')->first();
                
                if(is_null($debit_transaction)){
                    // continue;
                }else{
                    //create new expenses with associated_expense_id

                    //CREDIT CARD TRANSACTION
                    $credit_expense = Expense::create([
                        'distribution_id' => $vendor_office_distribution_id,
                        'created_by_user_id' => 0,
                        'amount' => $credit_transaction->amount,
                        'date' => $credit_transaction->transaction_date,
                        'vendor_id' => $credit_transaction->vendor_id,
                        'belongs_to_vendor_id' => $vendor->id
                        // 'reimbursment' => 0
                    ]);

                    $credit_transaction->expense()->associate($credit_expense);
                    $credit_transaction->save();

                    //DEBIT CARD TRANSACTION
                    $debit_expense = Expense::create([
                        'distribution_id' => $vendor_office_distribution_id,
                        'created_by_user_id' => 0,
                        'amount' => $debit_transaction->amount,
                        'date' => $debit_transaction->transaction_date,
                        'vendor_id' => $debit_transaction->vendor_id,
                        'belongs_to_vendor_id' => $vendor->id
                        // 'parent_expense_id' => $credit_expense->id
                    ]);

                    $debit_expense->parent_expense_id = $credit_expense->id;
                    $debit_expense->save();
                    $debit_transaction = Transaction::find($debit_transaction->id);
                    $debit_transaction->expense()->associate($debit_expense);
                    $debit_transaction->save();
                }
            } 
        }
    }

    public function amazon_order_api()
    {
        $amazon_vendor_accounts = ReceiptAccount::where('vendor_id', 54)->whereNotNull('options')->get();

        foreach($amazon_vendor_accounts as $amazon_account){
            //Configure the authorization parameters. 
            $access_key = $amazon_account->options['access_key']; 
            $secret_key = $amazon_account->options['secret_key']; 
            $api_key = $amazon_account->options['api_key']; 
            //This will vary depending on the API you are calling. In this case, we'll use the orders API. 
            $endpoint_url = 'https://alcq0apxu3.execute-api.us-east-1.amazonaws.com';
            $resource_path = '/v1/orders';
            $region = 'us-east-1'; 
            //Initialize the Credentials object. 
            $credentials = new \Aws\Credentials\Credentials($access_key, $secret_key); 

            $start_date = Carbon::today()->subDays(60)->format('Y-m-d');
            $end_date = Carbon::today()->format('Y-m-d');

            //where amount doesnt start with a minus/return 
            $expenses = Expense::where('vendor_id', 54)->whereNotNull('invoice')->whereDoesntHave('transactions')->whereBetween('date', [$start_date, $end_date])->where('amount', 'NOT LIKE', "-%")->get();
            // dd($expenses);

            foreach ($expenses as $expense) {
                $full_url = $endpoint_url .  $resource_path . '/' . $expense->invoice . '?includeCharges=true'; 
                /*?includeLineItems=true&includeShipments=true&includeCharges=true*/
                //Instantiate Client object with api key header. 
                $client = new \GuzzleHttp\Client(['headers' => ['x-api-key' => $api_key]]); 
                //Instantiate request object with http method and query string encoded URL. 
                $request = new \GuzzleHttp\Psr7\Request('GET', $full_url); 
                //Intialize the signer. 
                $s4 = new \Aws\Signature\SignatureV4("execute-api", $region); 
                //Build the signed request using the Credentials object. This is required in order to authenticate the call. 
                $signedRequest = $s4->signRequest($request, $credentials); 
                //Send the (signed) API request. 
                $response = $client->send($signedRequest); 
                //Print the response body. 
                /*var_dump($response->getBody());*/
                $order = collect(json_decode($response->getBody()->getContents(), true));

                if(!empty($order['orders'])){
                    foreach($order['orders'] as $order){
                        if($order['orderStatus'] == 'Cancelled' AND $expense->amount != 0){
                            $expense->amount = 0;
                            $expense->note = 'Order Cancelled';
                            $expense->delete();
                            $expense->save();
                        }elseif($expense->amount != $order['orderNetTotal']['amount']){
                            $expense->amount = $order['orderNetTotal']['amount'];
                            $expense->save();
                        }
                    }
                }
                sleep(1);
            }
            // Log::channel('amazon')->info('finished amazon_order_api');
        }
    }
}
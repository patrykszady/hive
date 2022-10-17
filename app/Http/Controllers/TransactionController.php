<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bank;
use App\Models\Expense;
use App\Models\Vendor;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\VendorTransaction;

use Carbon\Carbon;

class TransactionController extends Controller
{
    //TEST ONLY //FOR DEVELOPER EXECUTION ONLY
    //only needed for test purposes...transactions update from Plaid.com webhooks
    //For use when Plaid API isn't acting as expected and can always be executed manually...

    public function plaid_transactions_scheduled()
    {        
        $banks = Bank::withoutGlobalScopes()->whereNotNull('plaid_access_token')->get();

        foreach($banks as $bank){
            $data = array(
                "client_id" => env('PLAID_CLIENT_ID'),
                "secret" => env('PLAID_SECRET'),
                "access_token" => $bank->plaid_access_token,
                "webhook_type" => 'TRANSACTIONS',
                "webhook_code" => 'DEFAULT_UPDATE', //TRANSACTIONS_REMOVED
                "new_transactions"=> 899
            );
  
            $this->plaid_transactions($bank, $data);
        }
        // return Log::channel('plaid_institution_info')->info('finished plaid_transactions_scheduled');
    }

    //public function plaid_webhooks(Request $request)

    public function plaid_transactions(Bank $bank, $data)
    {
        for($i = 0; $i < $data['new_transactions'] + 100; $i+=100){
            $new_data = array(
                "client_id"=> env('PLAID_CLIENT_ID'),
                "secret"=> env('PLAID_SECRET'),
                "access_token"=> $bank->plaid_access_token,
                "options" => array(
                    "count"=> 90,
                    "offset"=> $i
                ),
            );

            if($data['webhook_type'] == 'TRANSACTIONS'){
                if($data['webhook_code'] == 'HISTORICAL_UPDATE'){

                }elseif($data['webhook_code'] == 'DEFAULT_UPDATE'){
                    //4-11-2020: unless new vendor (45 days), use last Plaid Update Date for this Bank as start date
                    // $bank_add_date = Carbon::create($bank->vendor->cliff_registration->vendor_registration_date);

                    // if($bank_add_date->lessThan(today()->subDays(14))){
                    //     $new_data['start_date'] = Carbon::now()->subDays(45)->toDateString();  
                    // }else{
                    //     $new_data['start_date'] = $bank_add_date->toDateString();
                    // }

                    $new_data['start_date'] = Carbon::now()->subDays(45)->toDateString(); 
                    $new_data['end_date'] = Carbon::now()->toDateString();
                }elseif($data['webhook_code'] == 'TRANSACTIONS_REMOVED'){
                    dd('in transactions_removed');
                    //remove these transactions (soft)
                    // Log::channel('plaid')->info($data);
                    // foreach($data['removed_transactions'] as $transaction_plaid_id){
                    //     $transaction = Transaction::withoutGlobalScopes()->where('plaid_transaction_id', $transaction_plaid_id)->first()->delete();
                    // }
                }
            }

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

            // dd($result);
         
            //if institution is in error, continue loop
            if(!isset($result['transactions'])){
                //04-11-2022 SAVE INSTITITION ERROR TO LOG
                continue;
            }

            //TEST ONLY! COMMENT OUT FOR PRODUCTION next 3 lines
            // $transactions_per_bank_account = collect($result['transactions'])->where('account_id', 'oyBJqz36ROUqK7vbwwXEfVnnmyLnnLHB5aje0');
            // dd($transactions_per_bank_account);
            // $result['transactions'] = array($result['transactions'][7]);
            // dd($result['transactions']);
            // $transactions_found = collect($result['transactions']);
            // dd($transactions_found->first()->account_id);

            foreach($result['transactions'] as $key => $transaction)
            {
                //4-11-2022 -- only get transactions where the BankAccount matches instead of doing all these loops below to filter. $result['transactions'] should be an eloquent collection
                $bank_account = BankAccount::withoutGlobalScopes()->where('plaid_account_id', $transaction['account_id'])->first();

                if(is_null($bank_account)){
                    //6-4-2022 LOG
                    continue;
                }

                //$same_accounts = Bank::where('vendor_id', $bank->vendor_id)->where('plaid_ins_id', $result['item']['institution_id'])->pluck('id');
                $same_accounts = BankAccount::withoutGlobalScopes()->where('vendor_id', $bank->vendor_id)->where('bank_id', $bank->id)->pluck('id');

                $start_date = Carbon::parse($transaction['date'])->subDays(10)->format('Y-m-d');
                $end_date = Carbon::parse($transaction['date'])->addDays(10)->format('Y-m-d');

                $duplicate_transaction_id = Transaction::withoutGlobalScopes()->whereNotNull('plaid_transaction_id')->where('plaid_transaction_id', $transaction['transaction_id'])->first();
                // dd($duplicate_transaction_id);
                //if plaid_transaction_id not found... try to find the Plaid Transaction another way...
                if(is_null($duplicate_transaction_id)){
                    //get all transactions with same amount, simuilar date, and same Ins_id, exclude $this->bank_account->id
                    $pending_transaction = Transaction::withoutGlobalScopes()->whereNotNull('plaid_transaction_id')->where('plaid_transaction_id', $transaction['pending_transaction_id'])->first();

                    //if $transaction['merchant_name'] empty, use $transaction['name']
                    if(isset($transaction['merchant_name'])){
                        $transaction_plaid_merchant_name = $transaction['merchant_name'];
                    }else{
                        $transaction_plaid_merchant_name = $transaction['name'];
                    }
                    
                    $transaction_plaid_merchant_desc = $transaction['name'];

                    if(!is_null($pending_transaction)){
                        $transaction_save = $pending_transaction;
                        $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
                        if($transaction['pending'] == true){
                            $transaction_save->posted_date = NULL;
                        }else{
                            $transaction_save->posted_date = $transaction['date'];
                        }                      

                        if($transaction['authorized_date'] == null){
                            $transaction_save->transaction_date = $transaction['date'];
                        }else{
                            $transaction_save->transaction_date = $transaction['authorized_date'];
                        }

                        //plaid_transaction_id
                        $transaction_save->amount = $transaction['amount'];
                        $transaction_save->plaid_merchant_name = $transaction_plaid_merchant_name;
                        $transaction_save->plaid_merchant_description = $transaction_plaid_merchant_desc;
                        $transaction_save->save();
                    }else{
                        $transactions_search_and_database = collect($result['transactions'])->where('amount', $transaction['amount'])->pluck('transaction_id');

                        // dd($transactions_search_and_database);
                        $transactions_same_plaid_inst = Transaction::whereNotIn('plaid_transaction_id', $transactions_search_and_database)->whereIn('bank_account_id', $same_accounts)->where('amount', $transaction['amount'])->whereBetween('transaction_date', [$start_date, $end_date])->get();
                        //whereNotIn('id', $transactions_search_and_database)
                        // ->where('plaid_merchant_name', $transaction['name'])
                        // ->where('plaid_transaction_id', '!=', $transaction['transaction_id'])
                        // dd($transactions_same_plaid_inst);

                        //no other transactions matching...save a new Transaction
                        if($transactions_same_plaid_inst->isEmpty()){
                            // dd('if');
                            // dd($result);
                            $transaction_save = new Transaction;

                            if($transaction['pending'] == true){
                                $transaction_save->posted_date = NULL;
                            }else{
                                $transaction_save->posted_date = $transaction['date'];
                            }                      

                            if($transaction['authorized_date'] == null){
                                $transaction_save->transaction_date = $transaction['date'];
                            }else{
                                $transaction_save->transaction_date = $transaction['authorized_date'];
                            }

                            $transaction_save->amount = $transaction['amount'];
                            $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
                            $transaction_save->bank_account_id = $bank_account->id;
                            // $transaction_save->bank_id = $bank->id;

                            $transaction_save->plaid_merchant_name = $transaction_plaid_merchant_name;
                            $transaction_save->plaid_merchant_description = $transaction_plaid_merchant_desc;
                            $transaction_save->save();
                        }else{
                            // dd($transaction);
                            // dd($transactions_same_plaid_inst);
                            //if 1 or none or mupliple found
                            if($transactions_same_plaid_inst->count() >= 1){
                                foreach($transactions_same_plaid_inst as $row_duplicate){
                                    $row_duplicate->date_diff = $row_duplicate->transaction_date->floatDiffInDays($transaction['date']);    
                                }

                                $duplicate_row = $transactions_same_plaid_inst->sortBy('date_diff')->first();
                                // dd($duplicate_row);
                                $transaction_save = Transaction::findOrFail($duplicate_row->id);
                                // dd(Transaction::where('id', $duplicate_row->id)->first());
                                $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
                                $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
                                $transaction_save->posted_date = $transaction['date'];
                                if($transaction['authorized_date'] == null){
                                    $transaction_save->transaction_date = $transaction['date'];
                                }
                                // else{
                                //     $transaction_save->transaction_date = $transaction['authorized_date'];
                                // }
                                $transaction_save->plaid_transaction_id = $transaction['transaction_id'];
                                $transaction_save->plaid_merchant_name = $transaction_plaid_merchant_name;
                                $transaction_save->plaid_merchant_description = $transaction_plaid_merchant_desc;
                                $transaction_save->save();
                                //if $transactions_same_plaid_inst->count() == more than 1 do more diagnostics..?
                            }else{
                                // dd('else else');
                            }
                        }
                    }
                }else{
                    //check if the existing transaction id has nay changed info?
                    //pending_transaction_id
                    //dd(['in else else', $transaction]);
                }
                //otherwise if there's a dupliate, check if it's posted. if not posted yet, continue, if posted, save 'posted_date'
            }
        } //for loop  
    }

    public function add_vendor_to_transactions()
    {     
        $transactions = Transaction::TransactionsSinVendor()->get()->groupBy('plaid_merchant_name');

        $vendors = Vendor::withoutGlobalScopes()->where('business_type', 'Retail')->get();

        foreach($transactions as $merchant_name => $merchant_transactions){
            // $vendor_match = preg_grep("/^" . $merchant_name . "/i", $vendors_name_array);
            $vendor_match = $vendors->where('business_name', $merchant_name)->first();

            if($vendor_match){
                foreach($merchant_transactions as $key => $transaction){
                    $transaction->vendor_id = $vendor_match->id;
                    $transaction->save();
                }

                //USED IN MULTIPLE OF PLACES MatchVendor@store, ExpesnesForm@createExpenseFromTransaction, below in CHECK VendorTransaction code in this function as well
                //add vendor if vendor is not part of the currently logged in vendor
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

            
            // dd($transactions);

            //Alter $transactions variable/results based on the if statement below
            // dd(Transaction::TransactionsSinVendor()->where('bank_account_id', 1)->get());

            foreach($transactions as $plaid_name_transactions){
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
                // dd('too far');

                $vendor_desc = $plaid_name_transactions->first()->plaid_merchant_description;
            
                //decode json on VendorTrasaction Model!
                $preg = json_decode($vendor_transaction->options);
                preg_match('/'. $vendor_transaction->desc . $preg, $vendor_desc, $matches, PREG_UNMATCHED_AS_NULL);

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

    public function add_expense_to_transactions()
    {
        //OLD: $cliff_vendors = Vendor::where('cliff_registration->vendor_registration_complete', 'true')->get();
        $cliff_vendors = Vendor::where('business_type', 'Sub')->get();

        foreach($cliff_vendors as $cliff_vendor){
            $cliff_vendor_bank_account_ids = $cliff_vendor->bank_accounts->pluck('id');
            // dd($cliff_vendor_bank_account_ids);
            $expenses = Expense::withoutGlobalScopes()
                ->with('transactions')
                ->with('receipts')
                ->whereNull('deleted_at')
                // ->doesntHave('splits')
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

                //6/1/2021 is the amount negative or positive? combine into 1 .. 
                if(substr($transaction_amount_outstanding,0,1) == '-'){
                    //amount is negative
                    $transactions = Transaction::
                        whereIn('bank_account_id', $cliff_vendor_bank_account_ids)
                        ->whereNull('expense_id')
                        ->where('vendor_id', $expense->vendor_id)
                        ->whereNull('check_number')
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
                // dd($transaction_amount_outstanding);
                
                // dd($transactions);
                // foreach($transactions as $transaction){
                //     $transaction->date_diff = $transaction->transaction_date->floatDiffInDays($expense['date']);    
                // }

                // $transactions = $transactions->sortBy('date_diff');

                // $duplicate_row = $transactions_same_plaid_inst->sortBy('date_diff')->first();

                // dd($transactions);
                //track which transaction/s/combos we have tried
                foreach($transactions as $transaction){
                    if($transaction->amount == $expense->amount){
                        // $transaction = $transaction->getOriginal();
                        // dd($transaction);
                        $transaction->expense()->associate($expense);
                        $transaction->save();

                        continue 2;
                    }

                    if(!$expense->expense_receipts->isEmpty()){
                        $receipt_text = $expense->expense_receipts->first()->receipt_html;
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
                    
                        // dd($expense_text_amounts);

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
}

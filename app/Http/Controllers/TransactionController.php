<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bank;
use App\Models\Check;
use App\Models\Expense;
use App\Models\Vendor;
use App\Models\Payment;
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
        $transaction_bank_accounts = BankAccount::where('vendor_id', 1)->pluck('id')->toArray();
        // $transactions = Transaction::TransactionsSinVendor()->get()->groupBy('plaid_merchant_name')
        $transactions = Transaction::TransactionsSinVendor()->whereIn('bank_account_id', $transaction_bank_accounts)->get()->groupBy('plaid_merchant_description');
        $vendors = Vendor::withoutGlobalScopes()->where('business_type', 'Retail')->get();

        // dd($transactions);

        foreach($transactions as $merchant_name => $merchant_transactions){
            // dd($merchant_transactions);

            //find vendor where vendor->business_name is contained in $merchant_name
            // $vendor_match = preg_grep("/^" . $merchant_name . "/i", $vendors->pluck('business_name')->toArray());
            $vendor_match = $vendors->where('business_name', $merchant_name)->first();
            // dd($vendor_match);

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
        // dd($vendor_transactions);
        foreach($vendor_transactions as $vendor_transaction){
            // dd($vendor_transaction);
            
            //get all BankAccount where bank_account_id 

            //get plaid_inst_id of bank_account_ids on transactions table

            
            // dd($transactions);

            //Alter $transactions variable/results based on the if statement below
            // dd(Transaction::TransactionsSinVendor()->where('bank_account_id', 1)->get());

            foreach($transactions as $vendor_name => $plaid_name_transactions){

                // dd($vendor_name);
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

                // $vendor_desc = $plaid_name_transactions->first()->plaid_merchant_description;
            
                //decode json on VendorTrasaction Model
                $preg = json_decode($vendor_transaction->options);

                // dd($vendor_transaction);
                preg_match('/'. $vendor_transaction->desc . $preg, $vendor_name, $matches, PREG_UNMATCHED_AS_NULL);
                // dd($matches);

                if(!empty($matches)){
                    foreach($plaid_name_transactions as $key => $transaction){
                        // dd($transaction->bank_account);
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

        //split by institution
        foreach($institutions as $institution){
            //NEED TO SHARE THIS WITH TrancationController@store_csv_array.. same code x2 06/29/2021
            $institution_bank_ids = Bank::where('plaid_ins_id', $institution)->pluck('id');
            $institution_bank_ids = BankAccount::whereIn('bank_id', $institution_bank_ids)->pluck('id');

            $deposit_check_types = VendorTransaction::groupBy('deposit_check')->where('plaid_inst_id', $institution)->pluck('deposit_check');

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
                            $check = $matches[0][0];
                            $transaction->check_number = $check;
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
        $cliff_vendors = Vendor::where('business_type', 'Sub')->get();

        foreach($cliff_vendors as $cliff_vendor){
            $cliff_vendor_bank_account_ids = $cliff_vendor->bank_accounts->pluck('id');

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
        //NOTES: 
            //using withoutGlobalScopes() in this function. Each of these queries MUST be accompanied by plaid_account_id to make sure vendor-specific data is compared.
            //1/18/2021 mutated values will break the code. Always $check->getRawOriginal('check') any mutated values....OR work that into Model code. Usually fails if the mudated value logic required Auth::user()

        $transactions = Transaction::withoutGlobalScopes()->whereNull('deleted_at')->whereNotNull('check_number')->whereNull('check_id')->orderBy('id', 'DESC')->get();
        foreach($transactions as $key => $transaction){
            $check_type = NULL;
            //need a way to match checks and transactions, ignoring amount...opposite of the Else statement below that finds them by amount only.
            //get all $transaction->plaid_account_ids
            $bank = Bank::withoutGlobalScopes()->find($transaction->bank_account->bank_id)->plaid_ins_id;
            $banks = Bank::withoutGlobalScopes()->where('plaid_ins_id', $bank)->pluck('id');
            $bank_accounts = BankAccount::withoutGlobalScopes()->whereIn('bank_id', $banks)->pluck('id');
            
            if($transaction->check_number == 1010101){
                $check_type = 'Transfer';
            }elseif($transaction->check_number == 2020202){
                $check_type = 'Cash';
            }else{
                $check_type = 'Check';
            }

            //10-22-2022 where trnsaction->date within 15 days of check->date

            $transaction_amount_checks = Check::withoutGlobalScopes()->whereDoesntHave('transactions')->whereIn('bank_account_id', $bank_accounts)->where('amount', str_replace('-','',$transaction->amount))->where('check_type', $check_type)->whereDate('date', '>', $transaction->transaction_date->subDays(15)->format('Y-m-d'))->get();
 
            //only if check_type = Check do a check_number constraint
            if($check_type == 'Check'){
                $transaction_amount_checks = $transaction_amount_checks->where('check_number', $transaction->check_number)->where('check_type', $check_type);
            }

            if($transaction_amount_checks->count() == 1){
                $check = $transaction_amount_checks->first();
                // dd($check->amount . ' | ' .$transaction->amount);
                if(isset($check)){
                    $transaction->check()->associate($check);
                    $transaction->save();
                }else{
                    //remove $transaction from $transactions collection
                    //is this needed?!
                    // $transactions->forget($key);
                }

            }else{
                continue;
            }

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
        $transactions = Transaction::where('transaction_date', '>', '2019-01-01')->where('deposit', 1)->whereDoesntHave('payments')->whereNull('expense_id')->get();

        foreach($transactions as $transaction){
            $vendor_id = $transaction->bank_account->bank->vendor_id;
            //reset payments variable?
            $payments = Payment::
                whereBetween('date', [$transaction->transaction_date->subDays(90), $transaction->transaction_date->addDays(7)])
                //where bank_id belongs_to same vendor_id as this payment
                ->where('belongs_to_vendor_id', $vendor_id)
                ->where('transaction_id', NULL)
                ->where('amount', substr($transaction->amount, 1))
                ->get();

                //json store which $transactions have been checked against which $payments so it doesnt check again?
                //where parent_client_payment_id is not in json for this $transaction
                // ->groupBy('parent_client_payment_id');
            
            if($payments->count() == 1){
                $payments->first()->transaction_id = $transaction->id;
                $payments->first()->save();
            }
        }            
    }

    // Iterative PHP program to print  
    // sums of all possible subsets.  
      
    // Prints sums of all subsets of array  
    public function subsetSums($arr, $n, $ids)  
    {  
        ini_set('max_execution_time', 600000);
        // dd($ids);
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
}
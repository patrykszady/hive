<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bank;
use App\Models\Check;
use App\Models\Expense;
use App\Models\ExpenseSplits;
use App\Models\Vendor;
use App\Models\Payment;
use App\Models\Distribution;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\ReceiptAccount;
use App\Models\VendorTransaction;
use App\Models\TransactionBulkMatch;

use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    //changed
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
    public function plaid_item_status()
    {
        $banks = Bank::withoutGlobalScopes()->whereNotNull('plaid_access_token')->get();

        foreach($banks as $bank){
            // $today = Carbon::now()->toDateString();
            // //Balances
            // $new_data = array(
            //     "client_id" => env('PLAID_CLIENT_ID'),
            //     "secret" => env('PLAID_SECRET'),
            //     "access_token" => $bank->plaid_access_token,
            //     "start_date" => $today,
            //     "end_date" => $today
            // );

            // $new_data = json_encode($new_data);
            // // dd($new_data);
            // //initialize session
            // $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/transactions/get");
            // //set options
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            //     'Content-Type: application/json',
            //     ));
            // curl_setopt($ch, CURLOPT_POST, true);
            // curl_setopt($ch, CURLOPT_POSTFIELDS, $new_data);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // //execute session
            // $result = curl_exec($ch);
            // //close session
            // curl_close($ch);
            // dd($result);

            // $balances = json_decode($result, true);
            // dd($balances);
            //--------------------------------//

            $data = array(
                "client_id" => env('PLAID_CLIENT_ID'),
                "secret" => env('PLAID_SECRET'),
                "access_token" => $bank->plaid_access_token
                );
            //convert array into JSON
            $data = json_encode($data);
            //initialize session
            $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/item/get");
            //set options
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                ));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //execute session
            $exchangeToken = curl_exec($ch);
            //close session
            curl_close($ch);

            $result = json_decode($exchangeToken, true);

            //get bank_account balnace here.
            // dd($bank->plaid_options->next_cursor);
            //save json status/errors
            // if(isset($result['item']['error'])){
            //     //$result['item']['error']
            //     $error = 'true';
            // }else{
            //     $error = 'false';
            // }
            // //$bank->plaid_options->error
            // // $options = $bank->plaid_options;
            // // $options->error = $error;

            // $bank->plaid_options['error'] = $error;
            // // dd($bank->plaid_options);

            // $bank->save();

            // dd($bank);

            if(isset($result['item']['error'])){
                $error = array("error" => $result['item']['error'],);
            }else{
                $error = array("error" => false,);
            }

            $bank->plaid_options = json_encode(array_merge(collect($bank->plaid_options)->toArray(), $error));
            $bank->save();
            // dd('too far');

            // // dd($balances['accounts'][0]['balances']);
            // if(isset($balances) AND !isset($balances['error_code'])){
            //     $plaid_options3 = array("balances" => $balances['accounts'][0]['balances'],);
            // }else{
            //     $plaid_options3 = array("balances" => false,);
            // }

            // $bank->plaid_options = json_encode(array_merge($plaid_options, $plaid_options1, $plaid_options2, $plaid_options3));
            // $bank->save();
        }
    }

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
        }
    }

    public function plaid_transactions_sync()
    {
        // $expenses = Expense::where('reimbursment', '=', 'Client')->whereDoesntHave('receipts')->get();
        // dd($expenses);
        // $transactions = Expense::whereHas('transactions', function ($query) {
        //     return $query->where('amount', '=', 260.02);
        //     })->get();
        // $expenses = Expense::whereHas('transactions')->whereYear('date', '2023')->with('transactions')->get();
        // $transactions = [];
        // foreach($expenses as $expense){
        //     if($expense->amount != $expense->transactions->sum('amount')){
        //         $transactions[] = $expense;
        //     }
        // }

        // dd(collect($transactions)->pluck('id'));

        //->where('id', 21)
        $banks = Bank::withoutGlobalScopes()->whereNotNull('plaid_access_token')->get();
        $bank_accounts = BankAccount::all();
        $transactions = Transaction::whereDate('transaction_date', '>=', '2022-01-01')->get();

        //07-26-2023 if not in error state...
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
        // dd($result);

        if(!empty($result['added']) OR !empty($result['modified']) OR !empty($result['removed']) OR isset($result['error_code'])){
            Log::channel('plaid_adds')->info([[$bank->getAttributes(), $bank->plaid_options], $result]);
        }

        //if not in error state...
        if(!array_key_exists("error_code", $result)){
            $next_cursor = array("next_cursor" => $result["next_cursor"],);
            //1/9/2023 previous_cursor
            $bank->plaid_options = json_encode(array_merge(json_decode(json_encode($bank->plaid_options), true), $next_cursor));
            $bank->save();

            if($result['has_more'] == true){
                $this->plaid_transactions_sync_bank($bank, $bank_accounts, $transactions);
            }else{
                //added
                foreach($result['added'] as $index => $new_transaction){
                    // dump($new_transaction);
                    // dd();
                // if($index == 4){
                    // //make sure transaction_id does not exist yet.. if it does..update..
                    if($transactions->where('plaid_transaction_id', $new_transaction['pending_transaction_id'])->first()){
                        $transaction = $transactions->where('plaid_transaction_id', $new_transaction['pending_transaction_id'])->first();
                        // if($transaction->plaid_merchant_name == $new_transaction['name']){

                        // }else{
                        //     $transaction = new Transaction;
                        // }
                    }elseif($transactions->where('plaid_transaction_id', $new_transaction['transaction_id'])->first()){
                        $transaction = $transactions->where('plaid_transaction_id', $new_transaction['transaction_id'])->first();
                    }else{
                        $transaction = new Transaction;
                    }

                    // dd($transaction);

                    //dates
                    if($new_transaction['pending'] == TRUE){
                        $transaction->posted_date = NULL;
                    }else{
                        $transaction->posted_date = $new_transaction['date'];
                    }

                    if($new_transaction['authorized_date'] == NULL){
                        $transaction->transaction_date = $new_transaction['date'];
                    }else{
                        if(isset($transaction->transaction_date)){

                        }else{
                            $transaction->transaction_date = $new_transaction['authorized_date'];
                        }
                    }

                    //if $transaction['merchant_name'] empty, use $new_transaction['name']
                    if(isset($new_transaction['merchant_name'])){
                        $transaction->plaid_merchant_name = $new_transaction['merchant_name'];
                    }else{
                        // $transaction->plaid_merchant_name = $new_transaction['name'];
                        // $transaction->plaid_merchant_name = NULL;
                    }

                    $transaction->amount = $new_transaction['amount'];
                    $transaction->plaid_merchant_description = $new_transaction['name'];
                    $transaction->plaid_transaction_id = $new_transaction['transaction_id'];

                    // if(!$bank_accounts->where('plaid_account_id', $new_transaction['account_id'])->first()->id){
                    //     dd($bank_accounts->where('plaid_account_id', $new_transaction['account_id'])->first());
                    // }
                    $transaction->bank_account_id = $bank_accounts->where('plaid_account_id', $new_transaction['account_id'])->first()->id;
                    if($new_transaction['check_number'] != NULL){
                        $transaction->check_number = $new_transaction['check_number'];
                    }else{
                        // $transaction->check_number = NULL;
                    }

                    $transaction->owner = $new_transaction['account_owner'];
                    $transaction->details = $new_transaction;
                    $transaction->save();
                // }//if $index
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

                    // if($transaction['check_id'] == NULL || $transaction['check_id'] == 0000){
                    //     // $transaction->check_number = $new_transaction['check_number'];
                    //     $transaction->check_number = NULL;

                    //     if($new_transaction['check_number'] != NULL){
                    //         $transaction->check_number = $new_transaction['check_number'];
                    //     }
                    // }else{

                    // }
                    if($new_transaction['check_number'] != NULL){
                        $transaction->check_number = $new_transaction['check_number'];
                    }else{
                        $transaction->check_number = NULL;
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
                        // $transaction->transaction_date = $new_transaction['authorized_date'];
                        if(isset($transaction->transaction_date)){

                        }else{
                            $transaction->transaction_date = $new_transaction['authorized_date'];
                        }
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
                    $transaction->details = $new_transaction;
                    $transaction->save();
                }

                //removed
                foreach($result['removed'] as $new_transaction){
                    //make sure transaction_id does not exist yet.. if it does..update..
                    $transaction = $transactions->where('plaid_transaction_id', $new_transaction['transaction_id'])->first();
                    if(!is_null($transaction)){
                        //transaction has payments ...disassociate
                        $transaction->payments()->get()->each(function($payment) {
                            $payment->transaction()->dissociate();
                            $payment->save();
                        });

                        $transaction->deleted_at = now();
                        $transaction->save();
                    }
                }
            }
        }else{
            return;
        }
    }

    public function plaid_transactions_enrich()
    {
        ini_set('max_execution_time', '9900000');
        $start_date = Carbon::now()->subDays(450);
        $end_date = Carbon::now();
        $offset = 0;
        $count = 99;
        $transactions_count = Transaction::where('bank_account_id', 10)->whereNull('details')->whereBetween('posted_date', [$start_date, $end_date])->orderBy('id', 'DESC')->get();

        $total_transactions_count = $transactions_count->count();
        // dd($total_transactions_count);
        // for loop. Count, Offset
        for($offset = $offset; $offset <= $total_transactions_count; $offset += $count) {
            $transactions = Transaction::where('bank_account_id', 10)->whereNull('details')->whereBetween('posted_date', [$start_date, $end_date])->orderBy('id', 'DESC')->get()->take(99);

            $array_transactions = [];
            foreach($transactions as $index => $transaction){
                //if MINUS - then INFLOW, otherwise OUTFOLW
                $negative = substr($transaction['amount'], 0, 1);
                if($negative == '-'){
                    $direction = 'INFLOW';
                }else{
                    $direction = 'OUTFLOW';
                }

                if($transaction->vendor){
                    if($transaction->vendor->business_name != 'No Vendor'){
                        $business_name = $transaction->vendor->business_name;
                    }else{
                        if($transaction->plaid_merchant_name){
                            $business_name = $transaction->plaid_merchant_name;
                        }else{
                            $business_name = NULL;
                        }
                    }
                }else{
                    $business_name = $transaction->plaid_merchant_name;
                }

                $business_name = str_replace("&", "And", $business_name);
                //where $business_name not in plaid_merchant_description
                if(str_contains(strtolower($transaction['plaid_merchant_description']), strtolower($business_name))){
                    $business_name = NULL;
                }

                $array_transactions[$index]['id'] = (string) $transaction['id'];
                $array_transactions[$index]['description'] =  ltrim($business_name . ' ' . $transaction['plaid_merchant_description']);
                $array_transactions[$index]['amount'] = (float) str_replace('-', '' , $transaction['amount']);
                $array_transactions[$index]['direction'] = $direction;
                $array_transactions[$index]['iso_currency_code'] = 'USD';
                $array_transactions[$index]['date_posted'] = $transaction['posted_date']->format('Y-m-d');
            }

            // dd($array_transactions);

            $new_data = array(
                "client_id"=> env('PLAID_CLIENT_ID'),
                "secret"=> env('PLAID_SECRET'),
                "account_type" => "depository",
                "transactions" => $array_transactions
            );

            $new_data = json_encode($new_data);

            //initialize session
            $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/transactions/enrich");
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

            $transactions_enriched = json_decode($result, true);
            $transactions_enriched = $transactions_enriched['enriched_transactions'];
            foreach($transactions_enriched as $transaction_enriched){
                // dd($transaction_enriched);
                $attach_transaction = Transaction::where('bank_account_id', 10)->whereNull('details')->whereBetween('posted_date', [$start_date, $end_date])->orderBy('id', 'DESC')->findOrFail($transaction_enriched['id']);
                // $attach_transaction = $transactions->findOrFail($transaction_enriched['id']);
                // dd($attach_transaction);
                $attach_transaction['details'] = $transaction_enriched['enrichments'];
                $attach_transaction->timestamps = false;
                $attach_transaction->save();
            }
        }
    }

    public function plaid_transactions_get_connect($bank, $count, $offset = 0)
    {
        $new_data = array(
            "client_id"=> env('PLAID_CLIENT_ID'),
            "secret"=> env('PLAID_SECRET'),
            //bank access token
            "access_token"=> $bank->plaid_access_token,
            "options" => array(
                "count" => $count,
                "offset" => $offset
            ),
        );

        // $start_date = Carbon::parse('2024-01-01')->toDateString();
        // $nend_date = Carbon::parse('2024-01-05')->toDateString();
        $start_date = Carbon::now()->subDays(450);
        $end_date = Carbon::now();

        $new_data['start_date'] = $start_date->toDateString();
        $new_data['end_date'] = $end_date->toDateString();

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

        return json_decode($result, true);
    }

    //FOR DEVELOPMENT ONLY DIAGNOSE PLAID TRANSACTIONS
    //01-04-2024 get Transaction json details going back as far as possible ...
    public function plaid_transactions_get()
    {
        ini_set('max_execution_time', '9900000');
        //foreach access_token / bank
        $banks = Bank::withoutGlobalScopes()->whereNotNull('plaid_access_token')->where('id', env('BANK_ID'))->get();
        foreach($banks as $bank){
            $offset = 0;
            $bank_accounts = $bank->accounts->pluck('id')->toArray();
            $count = 500;
            $result = $this->plaid_transactions_get_connect($bank, $count, $offset);

            //if error ... wait... 1 min +
            //30 requests per Item per minute
            // //https://plaid.com/docs/errors/rate-limit-exceeded/#transactions_limit
            if(isset($result['error_code']) && $result['error_code'] == 'TRANSACTIONS_LIMIT'){
                sleep(85);
                $result = $this->plaid_transactions_get_connect($bank, $count, $offset);
            }

            $total_transactions_count = $result['total_transactions'];

            // for($n = 0; $n <= 3335; $n += 500) {
            //     echo '<p>'. ($n) .'</p>';
            //     // echo '<p>'. ($n * 2 - 1) .'</p>';
            // }

            //get date of first and last in array
            // $start_transaction_date = Carbon::parse($result['transactions'][array_key_first($result['transactions'])]['date'])->subDays(30)->toDateString();
            // $end_transaction_date = Carbon::parse($result['transactions'][array_key_last($result['transactions'])]['date'])->addDays(30)->toDateString();

            for($offset = $offset; $offset <= $total_transactions_count; $offset += $count) {
                $result = $this->plaid_transactions_get_connect($bank, $count, $offset);
                //if error ... wait... 1 min +
                //30 requests per Item per minute
                // //https://plaid.com/docs/errors/rate-limit-exceeded/#transactions_limit
                if(isset($result['error_code']) && $result['error_code'] == 'TRANSACTIONS_LIMIT'){
                    sleep(85);
                    $result = $this->plaid_transactions_get_connect($bank, $count, $offset);
                }

                $transactions = collect($result['transactions']);
                // dd($transactions);
                //DEV ONLY FOR bank_id = 10 / Citi
                // dd($transactions);
                            // // dd($transactions->where('amount', 50.00)->where('date','2023-07-26'));
                            // // dd($result['transactions'])->where('amount');
                            // //where('id', 20256)->
                            // $transactions_no_plaid_id = Transaction::where('plaid_transaction_id', 'LIKE', 'PLAID_NO_ID_%')->orWhere('plaid_transaction_id', 'LIKE', 'NO_PLAID_TRANS_ID_%')->orWhere('plaid_transaction_id', 'LIKE', 'NO_PLAID_ID_%')->get();
                            // foreach($transactions_no_plaid_id as $transaction_no_plaid_id){
                            //     // dd($transaction_no_plaid_id);
                            //     // dd($transaction_no_plaid_id->posted_date->format('Y-m-d'));
                            //     //find transactuion and change plaid_transaction_id
                            //     $match_no_plaid_id_transactions = $transactions->where('amount', $transaction_no_plaid_id->amount)->where('date', $transaction_no_plaid_id->posted_date->format('Y-m-d'));
                            //     // dd($match_no_plaid_id_transaction->count());
                            //     // dd($match_no_plaid_id_transactions);
                            //     if($match_no_plaid_id_transactions->count() == 1){
                            //         $transaction_no_plaid_id->plaid_transaction_id = $match_no_plaid_id_transactions->first()['transaction_id'];
                            //         $transaction_no_plaid_id->save();
                            //     }elseif($match_no_plaid_id_transactions->count() > 1){
                            //         // foreach($match_no_plaid_id_transactions as $match_no_plaid_id_transaction){
                            //         //     dd($transaction_no_plaid_id);
                            //         //     dd($match_no_plaid_id_transaction);
                            //         // }


                            //         $again_count = $match_no_plaid_id_transactions->where('name', trim($transaction_no_plaid_id->plaid_merchant_description));

                            //         if($again_count->count() == 1){
                            //             $transaction_no_plaid_id->plaid_transaction_id = $again_count->first()['transaction_id'];
                            //             $transaction_no_plaid_id->save();
                            //         }else{
                            //             // dd( $again_count);
                            //             // if($transactions_no_plaid_id->count() == 1){
                            //             //     dd($again_count->first());
                            //             //     $transaction_no_plaid_id->first()->plaid_transaction_id = $again_count->first()['transaction_id'];
                            //             //     // $again_count->first()->plaid_transaction_id = $match_no_plaid_id_transactions->first()['transaction_id'];
                            //             //     $transaction_no_plaid_id->save();
                            //             // }
                            //         }
                            //         // dd('more than one ... log and error');
                            //     }
                            // }
                            // // dd('done');
                            // // dd(collect($result['transactions'])->where('amount', 98.84));

                // //get date of first and last in array

                $existing_transactions =
                Transaction::
                    // whereBetween('transaction_date', [$end_transaction_date, $start_transaction_date])
                    whereIn('bank_account_id', $bank_accounts);

                foreach($transactions as $transaction){
                    $match_transaction = $existing_transactions->get()->where('plaid_transaction_id', $transaction['transaction_id'])->first();
                    // $match_transaction = $match_transaction->first();

                    if(is_null($match_transaction)){
                        // $date_amount_transactions = $existing_transactions->whereDate('posted_date', $transaction['date'])->where('amount', $transaction['amount'])->get();
                        // if($date_amount_transactions->count() == 1){
                        //     $date_amount_transaction = $date_amount_transactions->first();
                        //     $date_amount_transaction->plaid_transaction_id = $transaction['transaction_id'];
                        //     $date_amount_transaction->details = $transaction;
                        //     $date_amount_transaction->timestamps = false;
                        //     $date_amount_transaction->save();
                        // }
                        // continue;
                        //Log / error / move on / continue;
                    }elseif(is_null($match_transaction->details)){
                        $match_transaction->details = $transaction;
                        $match_transaction->timestamps = false;
                        $match_transaction->save();
                    }else{
                        // dd($transaction);
                        // continue;
                        //Log / error / move on / continue;
                    }
                }

                // $offset = $offset + $count;
            }
        }

        dd('done');
    }

    public function add_vendor_to_transactions()
    {
        //if transaction where vendor is being added HAS expense? and expense->vendor is NULL ... add $transaction->vendor as $expense->vendor
        $transaction_bank_accounts = BankAccount::withoutGlobalScopes()->whereNull('deleted_at')->pluck('id')->toArray();
        $transactions = Transaction::TransactionsSinVendor()->whereIn('bank_account_id', $transaction_bank_accounts)->get()->groupBy('plaid_merchant_name');
        // dd($transactions);
        $vendors = Vendor::withoutGlobalScopes()->where('business_type', 'Retail')->get();

        foreach($transactions as $merchant_name => $merchant_transactions){
            // dd($merchant_name);
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

                if(!$transaction->bank_account->vendor->vendors->contains($transaction->vendor_id)){
                    $transaction->bank_account->vendor->vendors()->attach($transaction->vendor_id);
                }
            }
        }

        $transactions = Transaction::TransactionsSinVendor()->whereIn('bank_account_id', $transaction_bank_accounts)->get()->groupBy('plaid_merchant_description');

        //CHECK VendorTransaction table
        $vendor_transactions = VendorTransaction::whereNull('deposit_check')->get();
        foreach($vendor_transactions as $vendor_transaction){
            //get all BankAccount where bank_account_id
            //get plaid_inst_id of bank_account_ids on transactions table

            //Alter $transactions variable/results based on the if statement below

            foreach($transactions as $vendor_name => $plaid_name_transactions){
                $vendor_name = $vendor_name . ' ' . $plaid_name_transactions->first()->plaid_merchant_name;
                //decode json on VendorTrasaction Model
                $preg = json_decode($vendor_transaction->options);
                preg_match('/'. $vendor_transaction->desc . $preg, $vendor_name, $matches, PREG_UNMATCHED_AS_NULL);

                if(!empty($matches)){
                    foreach($plaid_name_transactions as $key => $transaction){
                        $transaction->vendor_id = $vendor_transaction->vendor_id;
                        $transaction->save();

                        if($transaction->expense){
                            $expense = $transaction->expense;
                            $expense->vendor_id = $transaction->vendor_id;
                            $expense->save();
                        }

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
            //06/29/2021 NEED TO SHARE THIS WITH TrancationController@store_csv_array.. same code x2
            $institution_bank_ids = Bank::withoutGlobalScopes()->where('plaid_ins_id', $institution)->pluck('id');
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
                    // ->where('check_id', NULL)
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
        $hive_vendors = Vendor::hiveVendors()->get();

        foreach($hive_vendors as $hive_vendor){
            $hive_vendor_bank_account_ids = $hive_vendor->bank_accounts->pluck('id');

            //withoutGlobalScopes()
            $expenses = Expense::
                with('transactions')
                ->with('receipts')
                ->whereNull('deleted_at')
                ->where('belongs_to_vendor_id', $hive_vendor->id)
                ->whereNotNull('vendor_id')
                // ->whereId('20678')
                //where transacitons->sum != $expense(item)->sum  \\ whereNull checked_at (transactions add up to expense)
                ->whereDate('date', '>=', Carbon::now()->subMonths(3))
                ->get();

            foreach($expenses as $expense){
                // dd($expense);
                $start_date = $expense->date->subDays(7)->format('Y-m-d');
                $end_date = $expense->date->addDays(21)->format('Y-m-d');

                if(!$expense->transactions->isEmpty()){
                    //transaction->amount cannot be more than expense->amount
                    $transaction_amount_outstanding = $expense->amount - $expense->transactions->sum('amount');

                    //if amount = full expense amount...
                    if($transaction_amount_outstanding == 0){
                        continue;
                    }
                }else{
                    $transaction_amount_outstanding = $expense->amount;
                }

                $transaction_amount_outstanding = (float)$transaction_amount_outstanding;
                // dd($transaction_amount_outstanding);

                $transactions = Transaction::
                    whereIn('bank_account_id', $hive_vendor_bank_account_ids)
                    ->whereNull('expense_id')
                    //03-22 -2023 when negative, ignore vendor_id
                    // ->when(substr($expense->amount, 0, 1) == '-', function ($query) {
                    //     dd($vendor_id);
                    //     $query->whereNull('vendor_id')->where('deposit', 1);
                    // }, function ($query) use ($vendor_id) {
                    //     dd($vendor_id);
                    //     $query->where('vendor_id', $vendor_id);
                    // })
                    //whereDoesntHave payments
                    ->doesntHave('payments')
                    ->whereNull('check_number')

                    ->whereBetween('transaction_date', [$start_date, $end_date])

                    //03/08/2023 floatDiffInDays dateDiff? orderBy faster i think?
                    ->orderBy('transaction_date', 'asc');

                //if expense vendor_id == expense belongs
                //where Greg pays deposit to GS (expense_id 17637)
                if($expense->vendor_id == $expense->belongs_to_vendor_id){
                    $transactions = $transactions->whereNull('vendor_id')->where('deposit', 1);
                }else{
                    //if expense->amount is negative
                    // if(substr($expense->amount, 0, 1) == '-'){
                    //     $transactions = $transactions->whereNull('vendor_id');
                    // }else{
                    //     $transactions = $transactions->where('vendor_id', $expense->vendor_id);
                    // }
                    $transactions = $transactions->where('vendor_id', $expense->vendor_id);
                }

                // dd($transactions->get());

                //if negative
                if(substr($expense->amount, 0, 1) == '-'){
                    $transactions = $transactions->where('amount', '>=', $transaction_amount_outstanding)->where('amount', 'LIKE', '-%')->get();
                }else{
                    $transactions = $transactions->where('amount', '<=', $transaction_amount_outstanding)->where('amount', 'NOT LIKE', '-%')->get();
                }
                // dd($transactions);

                //finds correct transaction
                if(!$transactions->isEmpty()){
                    foreach($transactions as $transaction){
                        $transaction->date_diff = $transaction->transaction_date->floatDiffInDays($expense->date);
                    }

                    $transactions_full_amount = $transactions->where('amount', $transaction_amount_outstanding);

                    if(!$transactions_full_amount->isEmpty()){
                        // dd($transaction->makeHidden('date_diff'));
                        $transaction = Transaction::findOrFail($transactions_full_amount->sortBy('date_diff')->first()->id);
                        $transaction->expense()->associate($expense);
                        $transaction->save();
                    }else{
                        if(!$expense->receipts->isEmpty()){
                            // dd($expense);
                            // dd('if');
                            //where amount != $expense->amount

                            foreach($transactions as $transaction){
                                //find $transaction->amount in $receipt_text
                                if($expense->vendor_id == $transaction->vendor_id){
                                    $receipt = $expense->receipts->last();

                                    if($receipt->receipt_html){
                                        $str = $receipt->receipt_html;
                                        $re = '/\\D' . str_replace(".", "\.", trim($transaction->amount, '-')) . '/m';
                                        preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

                                        if(!empty($matches)){
                                            $transaction = Transaction::findOrFail($transaction->id);
                                            $transaction->expense()->associate($expense);
                                            $transaction->save();

                                            continue;
                                        }
                                    }else{
                                        if(isset($receipt->receipt_items->charges)){
                                            $matches = collect($receipt->receipt_items->charges)->where('amount', $transaction->amount);
                                            if(!$matches->isEmpty()){
                                                $transaction = Transaction::findOrFail($transaction->id);
                                                $transaction->expense()->associate($expense);
                                                $transaction->save();

                                                continue;
                                            }
                                        }
                                    }
                                }
                            }

                            //summy
                            //clear array before next foreach statement
                            $transaction_results = array();

                            $transaction_ids = $transactions->pluck('id')->toArray();
                            $transaction_plucked = $transactions->pluck('amount')->toArray();

                            $arr = array_values(array_filter($transaction_plucked));
                            $n = sizeof($arr);
                            $ids = $transaction_ids;

                            $results = collect($this->subsetSums($arr, $n, $ids, 'expense'))->sortBy('sum');

                            foreach($results as $key => $result) {
                                $sum = number_format($result['sum'], 2, '.', '');
                                //this can happen multiple of times.. eg transaction_id 6230

                                //is this Transaction a RETURN CHECK "DEPOSIT"?
                                if($sum == $expense->amount){
                                    $transaction_results = $result;
                                }
                            }

                            if(isset($transaction_results['transactions'])){
                                $transaction_results = collect($transaction_results['transactions']);
                                foreach($transaction_results as $transaction){
                                    $transaction = Transaction::findOrFail($transaction['client_payment_id']);
                                    $transaction->expense()->associate($expense);
                                    $transaction->save();
                                }
                            }
                        }else{
                            // dd('self else pls');
                        }
                    }
                }else{
                    // dd('esle 85646');
                    // $expenses = Expense::where('vendor_id', $expense->vendor->id)->where('date', $expense->date->format('Y-m-d'))->get();
                    // $expenses_sum = $expenses->sum('amount');
                    // // dd($expenses_sum);
                    // $transactions =
                    //     Transaction::where('amount', $expenses_sum)
                    //         ->whereDoesntHave('expense')->get();
                    //         // ->each(function ($item) use($expense)  {
                    //         //     $item->date_diff = $expense->date->floatDiffInDays($item->transaction_date);
                    //         // });
                    // // dd($transactions);


                    // // dd((float)$transactions->first()->amount);
                    // dd((float)$transactions->first()->amount == $expenses_sum);

                    // if($transactions->first()->amount == $expenses_sum){
                    //     dd($transactions->first());
                    //     // foreach(){

                    //     // }
                    // }else{
                    //     dd('else');
                    // }



                    // //Expnense transaction_id = $transaction->id;
                    // //these $expenses = Transaction
                    // dd('in else transactions isEmpty...');
                    //associate Expenses...
                }

                // dd('too far');

                // if(!$transactions->isEmpty()){
                //     //floatDiffInDays from old/gsd3 TransactionController not by orderBy above
                //     foreach($transactions as $transaction){
                //         // dd(str_replace(".", "\.", $transaction->amount));
                //         if($transaction->amount == (float) $expense->amount){
                //             $transaction->expense()->associate($expense);
                //             $transaction->save();

                //             //03/08/2023 if isset deposit
                //         }else{
                //             //03/21/23 vendor has to be the same!!
                //             if(!$expense->receipts->isEmpty()){
                //                 //find $transaction->amount in $receipt_text
                //                 if($expense->vendor_id == $transaction->vendor_id){
                //                     $str = $expense->receipts->last()->receipt_html;
                //                     $re = '/\\D' . str_replace(".", "\.", trim($transaction->amount, '-')) . '/m';
                //                     preg_match($re, $str, $matches, PREG_OFFSET_CAPTURE, 0);

                //                     if(!empty($matches)){
                //                         // dd($matches);
                //                         $transaction->expense()->associate($expense);
                //                         $transaction->save();
                //                     }
                //                 }
                //             }
                //         }
                //     }
                // }
            } //foreach $expenses
        }
    }

    public function add_transaction_to_multi_expenses()
    {
        $hive_vendors = Vendor::hiveVendors()->get();
        foreach($hive_vendors as $hive_vendor){
            $hive_vendor_bank_account_ids = $hive_vendor->bank_accounts->pluck('id');

            //find Expenses per Vendor that have at least 2 expenses sin Transactions
            //associate expenses.. each Expense has the same Transaction
            $transactions = Transaction::
                where('id', 20541)
                ->whereIn('bank_account_id', $hive_vendor_bank_account_ids)
                ->whereNull('expense_id')
                //whereDoesntHave payments
                ->doesntHave('payments')
                ->whereNull('check_number')
                // ->whereBetween('transaction_date', [$start_date, $end_date])

                //03/08/2023 floatDiffInDays dateDiff? orderBy faster I think?
                ->orderBy('transaction_date', 'desc')
                ->get();

                foreach($transactions as $transaction){
                    $start_date = $transaction->transaction_date->subDays(7)->format('Y-m-d');
                    $end_date = $transaction->transaction_date->addDays(7)->format('Y-m-d');

                    $expenses =
                        Expense::
                            whereNull('deleted_at')
                            ->where('belongs_to_vendor_id', $hive_vendor->id)
                            ->where('vendor_id', $transaction->vendor_id)
                            ->whereNull('paid_by')
                            ->whereDoesntHave('transactions')
                            ->whereBetween('date', [$start_date, $end_date])
                            ->get();

                    //run subsetSums here, if any combination equals $transaction->amount, use those!
                    if($expenses->count() >= 2){
                        //summy
                        //clear array before next foreach statement
                        $expense_resluts = array();

                        $expenses_ids = $expenses->pluck('id')->toArray();
                        $expenses_plucked = $expenses->pluck('amount')->toArray();

                        $arr = array_values(array_filter($expenses_plucked));
                        $n = sizeof($arr);
                        $ids = $expenses_ids;

                        //model
                        $results = collect($this->subsetSums($arr, $n, $ids, 'expense'))->sortBy('sum');

                        foreach($results as $key => $result) {
                            $sum = number_format($result['sum'], 2, '.', '');
                            //this can happen multiple of times.. eg transaction_id 6230

                            //is this Transaction a RETURN CHECK "DEPOSIT"?
                            if($sum == $transaction->amount){
                                $expense_resluts[] = $result;
                            }
                        }

                        $expense_resluts = collect($expense_resluts);

                        if(!$expense_resluts->isEmpty()){
                            $expense_array = $expense_resluts[0]['expenses'];

                            foreach($expense_array as $expense){
                                // $transaction
                                $save_expense = Expense::findOrFail($expense['expense_id']);
                                $save_expense->transaction_id = $transaction->id;
                                $save_expense->save();
                            }
                        }
                    }
                }
        }
    }

    public function add_check_id_to_transactions()
    {
        $checks = Check::withoutGlobalScopes()
                ->whereDoesntHave('transactions')
                ->whereNull('deleted_at')
                ->orderBy('date', 'DESC')
                ->where('date', '>', '2021-01-01')
                ->get();

        foreach($checks as $check){
            if($check->check_type == 'Transfer'){
                $check_number = '1010101';
                $add_days = 14;
            }elseif($check->check_type == 'Check'){
                $check_number = $check->check_number;
                $add_days = 180;
            }elseif($check->check_type == 'Cash'){
                $check_number = '2020202';
                $add_days = 14;
            }else{
                Log::channel('add_check_id_to_transactions')->info($check);
                continue;
            }

            $transactions = Transaction::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->whereNull('check_id')
                ->where('check_number', $check_number)
                //per hive vendor... checks table foreach bank_account_id
                // ->where('bank_account_id', $check->bank_account_id)
                ->whereBetween('transaction_date', [
                        $check->date->subDays(7)->format('Y-m-d'),
                        $check->date->addDays($add_days)->format('Y-m-d')
                        ])
                ->where('amount', $check->amount)
                ->orderBy('id', 'DESC')
                ->get();

            if($transactions->count() == 1){
                //if check_number matches, that's the one
                //if not BUT if amount matches, that's the one
                $transactions->first()->check()->associate($check)->save();
            }else{
                // Log::channel('add_check_id_to_transactions')->info($check);
            }
        }
    }

    public function REMVOE_FOR_TEXT_ONLY_add_check_id_to_transactions()
    {
        dd('REMVOE_FOR_TEXT_ONLY_add_check_id_to_transactions');
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
            //     //     // foreach($checks as $check){
            //     //     //     $check->date_diff = $transaction->transaction_date->floatDiffInDays($check->date);
            //     //     // }
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
        //1-26-2023 why does 2019/older transactions/client_payments not work?
        $transactions = Transaction::
            where('transaction_date', '>', '2019-01-01')
            ->where('deposit', 1)
            ->whereDoesntHave('payments')
            ->whereNull('expense_id')
            // ->where('id', 21781)
            ->orderBy('posted_date', 'DESC')
            ->get();

        foreach($transactions as $transaction){
            $vendor_id = $transaction->bank_account->bank->vendor_id;

            $payments = Payment::
                // withoutGlobalScopes()
                whereBetween('date', [$transaction->transaction_date->subDays(21), $transaction->transaction_date->addDays(4)])
                //where bank_id belongs_to same vendor_id as this payment
                ->where('belongs_to_vendor_id', $vendor_id)
                ->where('transaction_id', NULL);
                // ->where('amount', substr($transaction->amount, 1))
                // ->get();

                //06-21-2021 json store which $transactions have been checked against which $payments so it doesnt check again?
                //where parent_client_payment_id is not in json for this $transaction
                // ->groupBy('parent_client_payment_id');

            // if first character is -
            $single_payments = $payments->where('amount', is_numeric(substr($transaction->amount, 0, 1)) ? '-' . $transaction->amount : substr($transaction->amount, 1))->get();
            // dd($single_payments);
            if(!$single_payments->isEmpty()){
                //closest date. diffInDays
                foreach($single_payments as $single_payment){
                    $single_payment->date_diff = $transaction->transaction_date->floatDiffInDays($single_payment->date);
                }

                $save_payment = $single_payments->sortBy('date_diff')->first();
                // dd($payment->makeHidden('date_diff'));
                $save_payment = Payment::findOrFail($save_payment->id);
                $save_payment->transaction_id = $transaction->id;
                $save_payment->save();
                // $transaction->payments()->associate($payment->id);
            }else{
                $payments = Payment::
                    whereBetween('date', [$transaction->transaction_date->subDays(21), $transaction->transaction_date->addDays(4)])
                    //where bank_id belongs_to same vendor_id as this payment
                    ->where('belongs_to_vendor_id', $vendor_id)
                    ->where('transaction_id', NULL)
                    ->get();
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

                    $results = collect($this->subsetSums($arr, $n, $ids, 'client_payment'))->sortBy('sum');
                    // dd($results);

                    foreach($results as $key => $result) {
                        $sum = number_format($result['sum'], 2, '.', '');
                        //this can happen multiple of times.. eg transaction_id 6230

                        //is this Transaction a RETURN CHECK "DEPOSIT"?
                        if($sum === substr($transaction->amount, 1) OR $sum === '-' . $transaction->amount){
                            $payment_results[] = $result;
                        }else{
                            //06/10/2021 if not found... create json array for $transaction with all parent_client_payment_id s so that we dont have to run this heavy program for those payments again.
                            //06/10/2021 we do the above line already with add_transactions_to_expenses... data is put into database... need it here too
                        }
                    }

                    $payment_results = collect($payment_results);
                    // dd($payment_results);

                    if(!$payment_results->isEmpty()){
                        $payment_array = $payment_results[0]['client_payments'];

                        foreach($payment_array as $payment){
                            $save_payment = Payment::findOrFail($payment['client_payment_id']);
                            $save_payment->transaction_id = $transaction->id;
                            $save_payment->save();

                            // $payments->fresh();
                        }
                    }
                }
            }
        }
    }

    //find expenses with NO VENDOR that match transactions
    public function add_transaction_to_expenses_sin_vendor(){
        $expenses = Expense::where('vendor_id', 0)->get();

        foreach($expenses as $expense){
            $matching_transaction =
                Transaction::where('amount', $expense->amount)
                    ->whereNull('expense_id')
                    ->get()
                    ->each(function ($item) use($expense)  {
                        $item->date_diff = $expense->date->floatDiffInDays($item->transaction_date);
                    })
                    ->sortBy('date_diff')
                    ->first();

            if(isset($matching_transaction)){
                $expense->vendor_id = $matching_transaction->vendor_id;
                $expense->save();

                $matching_transaction = Transaction::findOrFail($matching_transaction->id);
                $matching_transaction->expense()->associate($expense);
                $matching_transaction->save();
            }
        }
    }

    public function transactions_sum_not_expense_amount()
    {
        dd('in transactions_sum_not_expense_amount');
        ini_set('max_execution_time', '480000');
        $expenses =
            Expense::whereHas('transactions')->withSum('transactions', 'amount')->get();

        foreach($expenses as $key => $expense){
            if($expense->amount == $expense->transactions_sum_amount){
                $expenses->forget($key);
            }
        }

        dd($expenses->pluck('id'));

        $expenses = Expense::withWhereHas('transactions', function ($query) {
            $query->whereNotNull('check_id');
        })->get();
        dd($expenses);
    }

    // Iterative PHP program to print
    // sums of all possible subsets.
    // Prints sums of all subsets of array
    public function subsetSums($arr, $n, $ids, $model)
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
                   //1/3/24 client_payment_id should be id of Model being send here
                   $summy[] = array('subtotal' => $arr[$j], $model . '_id' => $ids[$j]);
                }
            }

            // Print sum of picked elements.
            // echo $sum , " ";
            if($sum != 0){
                $summys[] = ['sum' => $sum, $model . 's' => $summy];
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

            $vendors_debit_bank_accounts =
            BankAccount::withoutGlobalScopes()
                ->where('vendor_id', $vendor_id)
                ->where('deleted_at', NULL)
                ->where('type', 'Checking')
                ->get();

            $credit_transactions =
                Transaction::
                    where('check_id', NULL)
                    // ->where('id', 20425)
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
                // dd($debit_transactions);

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

    public function transaction_vendor_bulk_match_splits($match, $expense, $amount)
    {
        $all_previous_splits = [];
        if(isset($match->options['splits'])){
            foreach(collect($match->options['splits']) as $index => $split){
                if($split['amount_type'] == '%'){
                    $percent = $split['amount']; //2 decimals required
                    $percent_amount = $amount * $percent;

                    if($index === array_key_last($match->options['splits'])) {
                        // dd(collect($test_splits)->sum('amount'));
                        // // $split_amount = round($per_cent, 2);
                        // dd(collect($all_previous_splits)->sum('amount'));
                        $split_amount = round($amount - collect($all_previous_splits)->sum('amount'), 2);
                    }else{
                        $split_amount = round($percent_amount, 2);
                        $all_previous_splits[$index]['amount'] = $split_amount;
                    }
                }else{
                    $split_amount = $split['amount'];
                }

                $split = ExpenseSplits::create([
                    'amount' => $split_amount,
                    'expense_id' => $expense->id,
                    'project_id' => NULL,
                    'distribution_id' => $split['distribution_id'],
                    'reimbursment' => NULL,
                    'note' => NULL,
                    'belongs_to_vendor_id' => $match->belongs_to_vendor_id,
                    'created_by_user_id' => 0,
                ]);
            }
        }
    }

    public function transaction_vendor_bulk_match(){
        //->where('id', 41)
        $matches = TransactionBulkMatch::withoutGlobalScopes()->get();

        foreach($matches as $match){
            //get vendor back accounts..
            $bank_account_ids = $match->belongs_to_vendor->bank_accounts->pluck('id')->toArray();

            $transactions =
                Transaction::withoutGlobalScopes()
                    ->whereNull('deleted_at')
                    ->whereIn('bank_account_id', $bank_account_ids)
                    ->where('vendor_id', $match->vendor_id)
                    ->whereDoesntHave('expense')
                    ->whereNull('check_number')
                    // ->where('id', 21390)
                    // ->whereNotNull('posted_date')
                    //today()->subDays(3)
                    // ->where('posted_date', '<=', today()->format('Y-m-d'))
                    ->when($match->amount != NULL, function ($query) use ($match){
                        return $query->where('amount', isset($match->options['amount_type']) ? $match->options['amount_type'] : '=', $match->amount);
                    })
                    ->when(isset($match->options['desc']), function ($query) use ($match){
                        return $query->where('plaid_merchant_description', $match->options['desc']);
                    })
                    ->get();
            // dd($transactions);
            $expenses =
                Expense::withoutGlobalScopes()
                    ->where('vendor_id', $match->vendor_id)
                    //repetative?
                    ->where('belongs_to_vendor_id', $match->belongs_to_vendor->id)
                    ->where('project_id', 0)
                    ->when($match->amount != NULL, function ($query) use ($match){
                        return $query->where('amount', isset($match->options['amount_type']) ? $match->options['amount_type'] : '=', $match->amount);
                    })
                    ->get();

            //set project to existing expenses where $expense->project = NO PROJECT (e.g. email receipt linked with transaction before Project was assigned to transaction).
            foreach($expenses as $expense){
                $expense->update([
                    'project_id' => NULL,
                    //if splits distribution_id = NULL
                    'distribution_id' => $match->distribution_id,
                    'created_by_user_id' => 0,
                ]);

                //splits
                $this->transaction_vendor_bulk_match_splits($match, $expense, $expense['amount']);
            }

            //create new expense foreach transaction
            foreach($transactions as $transaction){
                //Find Duplicates $expense = $duplicate
                //date diff
                $duplicate_start_date = $transaction->transaction_date->subDays(1)->format('Y-m-d');
                $duplicate_end_date = $transaction->transaction_date->addDays(4)->format('Y-m-d');

                //find duplicate expenses
                $duplicates =
                    Expense::
                        where('belongs_to_vendor_id', $transaction->bank_account->bank->vendor_id)->
                        whereNull('deleted_at')->
                        where('amount', $transaction->amount)->
                        whereBetween('date', [$duplicate_start_date, $duplicate_end_date])->
                        get();

                if($duplicates->count() >= 1){
                    foreach($duplicates as $duplicate){
                        $duplicate->date_diff = $transaction->transaction_date->floatDiffInDays($duplicate->date);
                    }

                    $expense_duplicate = $duplicates->sortBy('date_diff')->first();

                    $expense = $expense_duplicate;
                }else{
                    $expense = Expense::create([
                        'amount' => $transaction->amount,
                        'date' => $transaction->transaction_date,
                        'project_id' => NULL,
                        //if splits distribution_id = NULL
                        'distribution_id' => $match->distribution_id,
                        'vendor_id' => $transaction->vendor_id,
                        'check_id' => NULL,
                        'paid_by' => NULL,
                        'belongs_to_vendor_id' => $match->belongs_to_vendor_id,
                        'created_by_user_id' => 0,
                    ]);
                }

                $transaction->expense_id = $expense->id;
                $transaction->save();

                //splits
                $this->transaction_vendor_bulk_match_splits($match, $expense, $transaction['amount']);
            }
        }
    }
}

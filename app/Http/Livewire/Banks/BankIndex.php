<?php

namespace App\Http\Livewire\Banks;

use App\Models\Bank;
use Livewire\Component;
use App\Models\BankAccount;

class BankIndex extends Component
{
    protected $listeners = ['plaidLinkItem' => 'plaid_link_item'];

    //from GS/TransactionController.plaid_link_token
    public function plaid_link_token()
    {
        $data = array(
            "client_id" => env('PLAID_CLIENT_ID'),
            "secret" => env('PLAID_SECRET'),
            "client_name" => env('APP_NAME'),
            //variable of user json cleaned below (single quotes inside single quotes)
            "user" => ['client_user_id' => (string)auth()->user()->id], //, 'client_vendor_id' => (string)auth()->user()->getVendor()->id
            "country_codes" => ['US'],
            "language" => 'en',
            // "redirect_uri" => OAuth redirect URI must be configured in the developer dashboard. See https://plaid.com/docs/#oauth-redirect-uris
            "webhook" => env('PLAID_WEBHOOK')
            );
    
        $data['products'] = array('transactions');
      
        //convert array into JSON
        $data = json_encode($data);

        //initialize session
        $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/link/token/create");
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

        //open Plaid Link Modal
        $this->emit('linkToken', $result['link_token']);
    }

    //12-28-2021 .. workflow is VERY similar between NEW bank and UPDATING existing back.. see old GS/TransactionController.plaid_CREATE_item

    //from GS/TransactionController.plaid_CREATE_item (changed to plaid_link_item)
    //plaid_link_item() / plaidLinkItem()
    public function plaid_link_item($item_data)
    {
        //php proccess the $data /aka: add bank and bank_accounts to user

        // Log::channel('plaid')->info(request()->all());

        $data = array(
            "client_id"=> env('PLAID_CLIENT_ID'),
            "secret"=> env('PLAID_SECRET'),
            "public_token"=> $item_data['public_token']
            );

        //convert array into JSON
        $data = json_encode($data);
        //initialize session
        $ch = curl_init("https://" . env('PLAID_ENV') .  ".plaid.com/item/public_token/exchange");
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

        // Log::channel('plaid')->info($result);
        
        $bank = new Bank;
        $bank->name = $item_data['institution']['name'];
        $bank->plaid_access_token = $result['access_token'];
        $bank->plaid_item_id = $result['item_id'];
        $bank->vendor_id = auth()->user()->vendor->id;
        $bank->plaid_ins_id = $item_data['institution']['institution_id'];
        //6/27/2021 need to set $balance/s to NULL because of banks.index and/or banks.show/edit view requirements
        $bank->plaid_options = '{"error_code": false, "balances": false}';
        $bank->save();

        foreach($item_data['accounts'] as $account){
            $bank_account = new BankAccount;
            $bank_account->bank_id = $bank->id;
            //if 0 or less than 4 ... add 0 in front until it reaches 4 digits on the BankAccount Model... 06/27/2021
            $bank_account->account_number = $account['mask']; 
            $bank_account->vendor_id = $bank->vendor_id;
            $bank_account->type =  ucwords($account['subtype']);
                //06/25/2021 There's way more subtypes...account for all 
                //09/03/2021 add type to database  see https://plaid.com/docs/api/accounts/
                    // checking
                    // savings
                    // credit
                    // cd
                    // money market
                    // 401k
                    // student
                    // auto
                    // consumer
            $bank_account->plaid_account_id = $account['id'];
            $bank_account->save();
        }

        //12/30/2022 if successful send to bank.show route, otherwise send back with error (plaind link or lalarvel php?)
        return redirect(route('banks.show', $bank->id));
    }

    public function render()
    {
        return view('livewire.banks.index', [
            'banks' => Bank::withoutGlobalScopes()->whereNotNull('plaid_access_token')->get(),
        ]);
    }
}

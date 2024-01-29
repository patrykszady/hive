<?php

namespace App\Livewire\Banks;

use App\Models\Bank;
use Livewire\Component;
use Livewire\Attributes\Title;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BankShow extends Component
{
    use AuthorizesRequests;
    public Bank $bank;
    // protected $listeners = ['plaidLinkItemUpdate' => 'plaid_link_item'];
    //from GS/TransactionController.plaid_link_token

    public function mount()
    {
        // dd($this->bank->plaid_options->error->error_message);
    }

    public function plaid_link_token_update()
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
            "webhook" => env('PLAID_WEBHOOK'),
            "access_token" => $this->bank->plaid_access_token
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
        // dd($result);

        //open Plaid Link Modal.
            //script file in banks.show.blade file.
        $this->dispatch('linkTokenUpdate', $result['link_token']);
    }

    #[Title('Bank')]
    public function render()
    {
        $this->authorize('create', Bank::class);

        return view('livewire.banks.show');
    }
}

<?php

namespace App\Livewire\Checks;

use App\Models\BankAccount;

use Livewire\Component;
use App\Livewire\Forms\CheckForm;

class CheckCreate extends Component
{
    public CheckForm $form;

    // public $check_input = NULL;

    public $bank_accounts = [];
    public $employees = [];
    // public $payment_type = NULL;

    protected $listeners = ['validateCheck'];

    public function mount()
    {
        $this->bank_accounts = BankAccount::with('bank')->where('type', 'Checking')
            ->whereHas('bank', function ($query) {
                return $query->whereNotNull('plaid_access_token');
            })->get();

        $this->employees = auth()->user()->vendor->users()->where('is_employed', 1)->whereNot('users.id', auth()->user()->id)->get();
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }
    // public function updated($field)
    // {
    //     // if($field == 'check.check_type'){
    //     //     if($this->check->check_type == 'Check'){
    //     //         $this->check_input = TRUE;
    //     //     }else{
    //     //         $this->check->check_number = NULL;
    //     //         $this->check_input = FALSE;
    //     //     }
    //     // }

    //     $this->validateOnly($field);
    // }

    public function validateCheck()
    {
        dd('in validateCheck');
        // $this->modal_show = TRUE;
    }

    public function store()
    {
        dd('in store Check');
        $this->validate();
        $this->form->store();


        if($this->payment_type->getTable() == 'vendors'){
            // dd($this->payment_type->getTable());
            // dd('vendors table');
            //send to VendorPaymentForm
            // dd($this->check);
            $this->dispatch('vendorHasCheck', $this->check);
        }elseif($this->payment_type->getTable() == 'expenses'){
            $this->dispatch('hasCheck', $this->check);
        }
    }

    public function render()
    {
        //where Active on Bank
        // $this->bank_accounts = BankAccount::with('bank')->where('type', 'Checking')
        // ->whereHas('bank', function ($query) {
        //     return $query->whereNotNull('plaid_access_token');
        // })->get();

        // if(isset($this->check)){
        //     $this->view_text = [
        //         // 'card_title' => 'Update user',
        //         'button_text' => 'Update Payment',
        //         'form_submit' => 'store',
        //     ];

        //     $this->check_input = TRUE;
        // }else{
        //     $this->check = Check::make();

        //     $this->view_text = [
        //         // 'card_title' => 'Update user',
        //         'button_text' => 'Save Payment',
        //         'form_submit' => 'store',
        //     ];
        // }

        // $employees = $this->user->vendor->users()->where('is_employed', 1)->whereNot('users.id', $this->user->id)->get();

        return view('livewire.checks.form', [
            // 'bank_accounts' => $bank_accounts,
            // 'employees' => $employees,
        ]);
    }
}

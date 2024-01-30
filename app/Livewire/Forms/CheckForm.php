<?php

namespace App\Livewire\Forms;

use App\Models\Check;

use Livewire\Attributes\Rule;
use Livewire\Form;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CheckForm extends Form
{
    use AuthorizesRequests;

    public ?Check $check;

    #[Rule('nullable|date|before_or_equal:today|after:2017-01-01')]
    public $date = NULL;

    // required_without:check.bank_account_id
    // #[Rule('nullable', as: 'paid by')]
    // public $paid_by = NULL;

    // required_without:check.paid_by
    #[Rule('required', as: 'bank account')]
    public $bank_account_id = NULL;

    // required_with:check.bank_account_id
    #[Rule('required', as: 'check type')]
    public $check_type = NULL;

    // required_if:check.check_type,Check
    #[Rule('required', as: 'check number')]
    public $check_number = NULL;

    // // required_with:check.paid_by
    // #[Rule('nullable')]
    // public $invoice = NULL;

    // protected $messages =
    // [
    //     'check.check_number' => 'Check Number is required if Payment Type is Check',
    // ];

    public function store()
    {
        // $this->validate();
        // dd($this);
        dd('in store checkForm');
        // $this->authorize('create', Expense::class);
        // $this->validate();

        //return $check
        // //only
        // ExpenseSplit::create($this->all());

        // $this->reset();
    }
}

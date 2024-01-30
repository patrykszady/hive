<?php

namespace App\Livewire\Forms;

use App\Models\Check;
use App\Models\Expense;

use Livewire\Attributes\Rule;
use Livewire\Form;

class VendorPaymentForm extends Form
{
    #[Rule('nullable')]
    public $project_id = '';

    #[Rule('required|date|before_or_equal:today|after:2017-01-01')]
    public $date = NULL;

    //required_without:check_form.bank_account_id'
    #[Rule('required_without:form.bank_account_id')]
    public $paid_by = NULL;

    // required_without:check.paid_by
    #[Rule('required_without:form.paid_by')]
    public $bank_account_id = NULL;

    // required_with:check.bank_account_id
    #[Rule('required_with:form.bank_account_id')]
    public $check_type = NULL;

    // required_if:check.check_type,Check
    #[Rule('required_if:form.check_type,Check')]
    public $check_number = NULL;

    #[Rule('required_with:form.invoice')]
    public $invoice = NULL;

    // protected function rules()
    // {
    //     return [
    //         'payment_projects.*.amount' => 'required|numeric|min:0.01|regex:/^-?\d+(\.\d{1,2})?$/',

    //         'check.paid_by' => 'required_without:check.bank_account_id',
    //         'check.date' => 'required|date|before_or_equal:today|after:2017-01-01',
    //         'check.bank_account_id' => 'required_without:check.paid_by',
    //         'check.check_type' => 'required_with:check.bank_account_id',
    //         'check.invoice' => 'required_with:check.paid_by',
    //         //check_number is unique on Checks table where bank_account_id and check_number must be unique
    //         //02-21-2023 - used in MILTIPLE of places... ExpenseNewForm...
    //         'check.check_number' => [
    //             //ignore if vendor_id of Check is same as request()->vendor_id
    //             'required_if:check.check_type,Check',
    //             'nullable',
    //             'numeric',
    //             Rule::unique('checks', 'check_number')->where(function ($query) {
    //                 //->where('vendor_id', '!=', $this->vendor->id)
    //                 //03-16-2023 orWhere check_number on transaction....
    //                 return $query->where('deleted_at', NULL)->where('bank_account_id', $this->check->bank_account_id);
    //             }),
    //             //->ignore(request()->get('check_id_id'))
    //         ],
    //     ];
    // }

    // protected $messages =
    // [
    //     'payment_projects.*.amount.required' => 'Project Amount is required if included. "Remove Project" if not included in this Payment',
    //     'payment_projects.*.amount.numeric' => 'Project Amount must be a number if included.',
    //     'payment_projects.*.amount.min' => 'Project Amount must be at least $0.01 if included.',
    //     'payment_projects.*.amount.regex' => 'Amount format is incorrect. Format is 2145.36. No commas and only two digits after decimal allowed. If amount is under $1.00, use 0.XX',
    //     'check.check_number.required_if' => 'Check Number is required if Payment Type is Check',
    //     'check.check_number.unique' => 'Check Number is already taken.',
    // ];

    public function store()
    {
        $this->validate();
        //create expense for each $payment_projects. create one Check for all Expenses and associate with the Check.
        if(empty($this->paid_by)){
            $check = Check::create([
                'check_type' => $this->check_type,
                'check_number' => $this->check_number,
                'date' => $this->date,
                'bank_account_id' => $this->bank_account_id,
                'vendor_id' => $this->component->vendor->id,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'created_by_user_id' => auth()->user()->id,
            ]);
        }else{
            $check = NULL;
        }

        foreach($this->component->projects->where('show', 'true')->where('amount', '>' , 0) as $project){
            //ignore 'show' attribute when saving
            $project->offsetUnset('show');
            Expense::create([
                'amount' => $project->amount,
                'date' => $this->date,
                'invoice' => $this->invoice,
                'project_id' => $project->id,
                'vendor_id' => $this->component->vendor->id,
                'check_id' => isset($check) ? $check->id : NULL,
                'paid_by' => isset($check) ? NULL : $this->paid_by,
                'invoice' => isset($check) ? NULL : $this->invoice,
                'belongs_to_vendor_id' => auth()->user()->vendor->id,
                'created_by_user_id' => auth()->user()->id,
            ]);
        }

        //09-06-2023 put in observer?
        //if $this->vendor->id is registered
        //create payment for each check (/ payments / expenses / paid_by employee)?
        if($this->component->vendor->registration['registered']){
            app('App\Http\Controllers\VendorRegisteredController')
                ->create_payment_from_check(
                    $check,
                    $check->expenses,
                    $this->component->vendor
                );
        }

        return $check;
    }
}

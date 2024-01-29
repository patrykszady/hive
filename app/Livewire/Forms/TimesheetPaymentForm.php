<?php

namespace App\Livewire\Forms;

use App\Models\Vendor;
use App\Models\Check;

use Livewire\Attributes\Rule;
use Livewire\Form;

class TimesheetPaymentForm extends Form
{
    #[Rule('required')]
    public $payee_name = '';

    #[Rule('required')]
    public $first_name = '';

    #[Rule('required')]
    public $via_vendor_back = NULL;

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
    //         'user.full_name' => 'nullable',
    //         'user.payee_name' => 'nullable',
    //         'user.via_vendor_back' => 'nullable',

    //         'check.date' => 'required|date|before_or_equal:today|after:2017-01-01',
    //         'check.paid_by' => 'required_without:check.bank_account_id',

    //         'check.bank_account_id' => 'required_without:check.paid_by',
    //         'check.check_type' => 'required_with:check.bank_account_id',
    //          //02-21-2023 - used in MILTIPLE of places... VendorPaymentForm...
    //         'check.check_number' => [
    //             //ignore if vendor_id of Check is same as request()->vendor_id
    //             'required_if:check.check_type,Check',
    //             'nullable',
    //             'numeric',
    //             Rule::unique('checks', 'check_number')->where(function ($query) {
    //                 return $query->where('deleted_at', NULL)->where('bank_account_id', $this->check->bank_account_id);
    //             }),
    //             //->ignore(request()->get('check_id_id'))
    //         ],
    //         'check.invoice' => 'required_with:check.paid_by',

    //         //7/18/2022 ignore if updating Check  ->ignore(request()->get('check_id_id'))
    //         // 'check.check_number' => [
    //         //     'required_if:check.check_type,Check',
    //         //     'nullable',
    //         //     Rule::unique('checks', 'check_number')->where(function ($query) {
    //         //         return $query->whereNull('deleted_at')->where('bank_account_id', $this->check->bank_account_id);
    //         //     }),
    //         //     'nullable',
    //         //     'numeric',
    //         // ],
    //     ];
    // }

    public function setUser($user)
    {
        $this->payee_name = $user->payee_name;
        $this->first_name = $user->first_name;
        $this->via_vendor_back = $user->via_vendor_back;

        $this->date = today()->format('Y-m-d');
    }

    public function store()
    {
        // dd($this);
        $this->validate();

        //complete this on CheckObserver
        if(!is_null($this->component->user->pivot_user_vendor)){
            $via_vendor = Vendor::findOrFail($this->component->user->pivot_user_vendor);
            if($via_vendor->registration){
                if($via_vendor->registration['registered']){
                }
            }
        }

        if(isset($via_vendor)){
            $check_user_id = NULL;
            $check_vendor_id = $via_vendor->id;
        }else{
            $check_user_id = $this->component->user->id;
            $check_vendor_id = NULL;
        }

        if(empty($this->paid_by)){
            $check = Check::create([
                'check_type' => $this->check_type,
                'check_number' => $this->check_number,
                'date' => $this->date,
                'bank_account_id' => $this->bank_account_id,
                'user_id' => $check_user_id,
                'vendor_id' => $check_vendor_id,
                //via_vendor_id....
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'created_by_user_id' => auth()->user()->id,
            ]);
        }

        //weekly_timesheets
        foreach($this->component->weekly_timesheets->where('checkbox', 'true') as $weekly_timesheet){
            //ignore 'checkbox' attribute when saving
            $weekly_timesheet->offsetUnset('checkbox');
            $weekly_timesheet->check_id = isset($check) ? $check->id : NULL;
            $weekly_timesheet->paid_by = isset($check) ? NULL : $this->paid_by;
            $weekly_timesheet->invoice = isset($check) ? NULL : $this->invoice;
            $weekly_timesheet->save();
        }

        //employee_weekly_timesheets
        //09-05-2023 can we get here if check is not set ? shouldnt... validate if $employee_weekly_timesheets ? addError ..has to be paid by a Check not Paid by.
        foreach($this->component->employee_weekly_timesheets->where('checkbox', 'true') as $weekly_timesheet){
            //ignore 'checkbox'
            $weekly_timesheet->offsetUnset('checkbox');
            $weekly_timesheet->check_id = $check->id;
            $weekly_timesheet->save();
        }

        //user_paid_expenses
        foreach($this->component->user_paid_expenses->where('checkbox', 'true') as $expense){
            //ignore 'checkbox'
            $expense->offsetUnset('checkbox');
            $expense->check_id = isset($check) ? $check->id : NULL;
            // $expense->paid_by = isset($check) ? NULL : $this->paid_by;
            $expense->save();
        }

        //user_reimbursement_expenses
        foreach($this->component->user_reimbursement_expenses->where('checkbox', 'true') as $expense){
            //ignore 'checkbox'
            $expense->offsetUnset('checkbox');
            $expense->check_id = isset($check) ? $check->id : NULL;
            $expense->paid_by = isset($check) ? NULL : $this->paid_by;
            $expense->save();
        }

        //user_paid_by_reimbursements
        foreach($this->component->user_paid_by_reimbursements->where('checkbox', 'true') as $expense){
            //ignore 'checkbox'
            $expense->offsetUnset('checkbox');
            $expense->check_id = isset($check) ? $check->id : NULL;
            // $expense->paid_by = isset($check) ? NULL : $this->paid_by;
            $expense->save();
        }

        //find Check and create_payment_from_check if via_vendor?
        //06-01-2023 should be done in observer
        if(isset($via_vendor)){
            if($via_vendor->registration){
                if($via_vendor->registration['registered']){
                    app('App\Http\Controllers\VendorRegisteredController')
                        ->create_payment_from_check(
                            $check,
                            $check->timesheets,
                            $via_vendor
                        );
                }
            }
        }

        // dd($this->component->weekly_timesheets->where('checkbox', 'true')->sum('amount'));
        // dd($this->component->user_reimbursement_expenses->sum('amount'));

        if(isset($check)){
            $expenses = $check->expenses;
            foreach($expenses as $expense){
                if(is_numeric($expense->reimbursment)){
                    $expense->amount = -$expense->amount;
                }
            }

            //$check->expenses->whereNotNull('paid_by')->whereNull('reimbursment')->sum('amount') +
            $check->amount = $check->timesheets->sum('amount') + $expenses->sum('amount');
            $check->save();

            return $check;
        }else{
            return 'timesheets';
        }
    }
}

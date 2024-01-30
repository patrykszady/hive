<?php

namespace App\Livewire\Checks;

use App\Models\Vendor;
use App\Models\Check;
use App\Models\Expense;
use App\Models\Timesheet;

use App\Mail\VendorPaymentMade;

use Livewire\Component;
use Livewire\Attributes\Title;

use Illuminate\Support\Facades\Mail;

class CheckShow extends Component
{
    public Check $check;
    public $employee_timesheets_total = [];

    public function mount()
    {

    }

    public function email()
    {
        $paying_vendor = Vendor::findOrFail($this->check->belongs_to_vendor_id);

        //10-17-2023 move to a Mail method
        Mail::to($this->check->vendor->business_email)
            ->cc([$paying_vendor->business_email])
            ->send(new VendorPaymentMade($this->check->vendor, $paying_vendor, $this->check));
    }

    #[Title('Check')]
    public function render()
    {
        //paid_by 60 = Robert, find vendor_id on vendor_user table "team members"(include previous/all
        // $user_via_vendor = auth()->user()->vendor->users()->where('via_vendor_id', $this->check->vendor_id);

        // if(!$user_via_vendor->get()->isEmpty()){
        //     $via_vendor_user_id = $user_via_vendor->first()->id;

        //     $vendor_paid_expenses =
        //         Expense::
        //             where('paid_by', $via_vendor_user_id)
        //             ->where('check_id', $this->check->id)
        //             ->whereNotNull('distribution_id')
        //             ->get();
        //     if($vendor_paid_expenses->isEmpty()){
        //         $vendor_paid_expenses = NULL;
        //     }
        // }else{
        //     $vendor_paid_expenses = NULL;
        // }
        // dd($vendor_paid_expenses);

        $vendor_expenses =
            Expense::
                where('check_id', $this->check->id)
                ->whereNull('reimbursment')
                ->whereNull('distribution_id')
                ->whereNull('paid_by')
                ->get();

        $weekly_timesheets =
            Timesheet::
                where('check_id', $this->check->id)
                ->where('user_id', $this->check->user_id)
                ->get();

        $employee_total_timesheets =
            Timesheet::
                where('paid_by', $this->check->user_id)
                ->where('check_id', $this->check->id)
                ->get()
                ->groupBy(['user_id']);

        foreach($employee_total_timesheets as $user_id => $employee){
            $employee_timesheets_total[$user_id] = $employee->sum('amount');
        }

        // dd($this->employee_timesheets_total[3]);

        $employee_weekly_timesheets =
            Timesheet::
                where('paid_by', $this->check->user_id)
                ->where('check_id', $this->check->id)
                ->get()
                ->groupBy(['user_id', 'date']);

        $user_paid_expenses =
            Expense::
                whereNotNull('paid_by')
                // where('paid_by', $this->check->user_id)
                ->where('check_id', $this->check->id)
                ->whereNull('distribution_id')
                ->get();

        $user_distributions =
            Expense::
                whereNotNull('distribution_id')
                ->whereNull('reimbursment')
                ->where('check_id', $this->check->id)
                ->get();

        $user_paid_reimburesements =
            Expense::
                // whereNotNull('distribution_id')
                whereNull('paid_by')
                ->whereNotNull('reimbursment')
                ->where('check_id', $this->check->id)
                ->get();

        return view('livewire.checks.show', [
            // 'vendor_paid_expenses' => $vendor_paid_expenses,
            'vendor_expenses' => $vendor_expenses,
            'weekly_timesheets' => $weekly_timesheets,
            'employee_total_timesheets' => $employee_total_timesheets,
            'employee_weekly_timesheets' => $employee_weekly_timesheets,
            'user_paid_expenses' => $user_paid_expenses,
            'user_distributions' => $user_distributions,
            'user_paid_reimburesements' => $user_paid_reimburesements,
        ]);
    }
}

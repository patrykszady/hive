<?php

namespace App\Http\Livewire\Checks;

use App\Models\Check;
use App\Models\Expense;
use App\Models\Timesheet;

use Livewire\Component;

class ChecksShow extends Component
{
    public Check $check;

    public function mount()
    {
        $this->weekly_timesheets = 
            Timesheet::
                where('check_id', $this->check->id)
                ->where('user_id', $this->check->user_id)
                ->get();

        $this->employee_weekly_timesheets =
            Timesheet::
                where('paid_by', $this->check->user_id)
                ->where('check_id', $this->check->id)
                ->get()
                ->groupBy(['user_id', 'date']);
        
        $this->user_paid_expenses = 
            Expense::
                where('paid_by', $this->check->user_id)
                ->where('check_id', $this->check->id)
                ->get();

        $this->user_distributions = 
            Expense::
                whereNotNull('distribution_id')
                ->where('check_id', $this->check->id)
                ->get();
    }

    public function render()
    {
        return view('livewire.checks.show');
    }
}

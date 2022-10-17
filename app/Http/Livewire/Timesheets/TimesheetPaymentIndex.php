<?php

namespace App\Http\Livewire\Timesheets;

use App\Models\Timesheet;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimesheetPaymentIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    public function render()
    {
        $this->authorize('viewPayment', Timesheet::class);
        
        $user_timesheets = 
            Timesheet::
                orderBy('date', 'DESC')
                // ->where('user_id', auth()->user()->id)
                ->whereNull('check_id')
                // ->whereNull('deleted_at')
                ->get()
                ->groupBy('user_id');
                // ->groupBy('date');

        // dd($timesheets);
        return view('livewire.timesheets.payment-index', [
            'user_timesheets' => $user_timesheets,
        ]);
    }
}
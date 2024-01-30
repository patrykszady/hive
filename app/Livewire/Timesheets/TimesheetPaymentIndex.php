<?php

namespace App\Livewire\Timesheets;

use App\Models\Timesheet;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Title;

class TimesheetPaymentIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Title('Timesheet Payments')]
    public function render()
    {
        $this->authorize('viewPayment', Timesheet::class);

        $user = auth()->user();
        $vendor_users = $user->vendor->users()->where('is_employed', 1)->get();

        $user_timesheets =
            Timesheet::
                orderBy('date', 'DESC')
                // ->where('user_id', auth()->user()->id)
                ->whereNull('check_id')
                ->whereNull('paid_by')
                ->get()
                ->groupBy('user_id');
                // ->groupBy('date');

        return view('livewire.timesheets.payment-index', [
            'user_timesheets' => $user_timesheets,
            'vendor_users' => $vendor_users,
        ]);
    }
}

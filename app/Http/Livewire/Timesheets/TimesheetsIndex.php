<?php

namespace App\Http\Livewire\Timesheets;

use Livewire\Component;

use App\Models\Hour;
use App\Models\Timesheet;

use Livewire\WithPagination;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Carbon\Carbon;
// use Carbon\CarbonInterval;

class TimesheetsIndex extends Component
{
    use WithPagination, AuthorizesRequests;
    
    public function render()
    {
        $this->authorize('viewAny', Timesheet::class);
        
        $weekly_hours_to_confirm = 
            Hour::
                orderBy('date', 'DESC')
                ->where('user_id', auth()->user()->id)
                ->whereNull('timesheet_id')->get()
                ->groupBy(function($item) {
                    return Carbon::parse($item->date)->format('W');
                });

        $confirmed_weekly_hours = 
            Timesheet::
                orderBy('date', 'DESC')
                ->where('user_id', auth()->user()->id)
                ->get()
                ->groupBy('date');

        // $confirmed_weekly_hours = 
        //     Timesheet::
        //         orderBy('date', 'DESC')
        //         ->where('user_id', auth()->user()->id)
        //         ->paginate(10);

        // $confirmed_weekly_hours->setCollection($confirmed_weekly_hours->groupBy('date'));

        return view('livewire.timesheets.index', [
            'weekly_hours_to_confirm' => $weekly_hours_to_confirm,
            'confirmed_weekly_hours' => $confirmed_weekly_hours,
        ]);
    }
}

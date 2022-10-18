<?php

namespace App\Http\Livewire\Timesheets;

use App\Models\Hour;
use App\Models\Timesheet;

use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimesheetShow extends Component
{
    use AuthorizesRequests;

    public Timesheet $timesheet;

    public function render()
    {
        $this->authorize('view', $this->timesheet);
        $weekly_hours = 
            Timesheet::
                with('check')->withoutGlobalScopes()
                ->orderBy('date', 'DESC')
                ->where('date', $this->timesheet->date->format('Y-m-d'))
                ->where('user_id', $this->timesheet->user_id)
                ->get();

        // dd($weekly_hours->first()->check_id);

        $timesheet_ids = $weekly_hours->pluck('id')->toArray();

        $daily_hours = 
            Hour::
                orderBy('date', 'ASC')
                ->whereIn('timesheet_id', $timesheet_ids)
                ->get()
                ->groupBy('date');

        return view('livewire.timesheets.show', [
            'timesheet' => $this->timesheet,
            'weekly_hours' => $weekly_hours,
            'daily_hours' => $daily_hours,
        ]);
    }
}

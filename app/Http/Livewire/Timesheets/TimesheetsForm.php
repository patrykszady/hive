<?php

namespace App\Http\Livewire\Timesheets;

use Livewire\Component;

use App\Models\User;
use App\Models\Hour;
use App\Models\Project;
use App\Models\Timesheet;

use Carbon\Carbon;
// use Carbon\CarbonInterval;

class TimesheetsForm extends Component
{
    public Hour $hour;

    public $user;
    public $week;
    public $weekly_hours;

    protected function rules()
    {
        return [
            'user.full_name' => 'required',
            'user.hours' => 'required|numeric|min:.25',
            'user.amount' => 'required|numeric|min:.01',
            'user.hourly' => 'required|numeric|min:.25',
        ];
    }

    public function mount()
    {
        // dd($this->hour);
        //7-6-2022 is this week already complete and have Timesheets?
        $this->week = $this->hour->date;

        //user of Hour called...
        $this->user = $this->hour->user;
        $this->user->full_name = $this->user->full_name;

        // dd($this->user->first_name);

        $this->weekly_hours = Hour::
            with('project')
            ->where('user_id', $this->user->id)
            ->whereNull('timesheet_id')
            ->whereBetween('date', [$this->week->startOfWeek()->format('Y-m-d'), $this->week->endOfWeek()->format('Y-m-d')])
            ->get();

        //send user_id as first variable to auth_user_role
        //$this->user->hourly = $this->user->vendor->auth_user_role;

        // dd($this->user->vendor);
        
        // dd($this->user->hourly);

        if($this->weekly_hours->isEmpty()){
            //7-6-2022 redirect with message ... week either has no hours or has already been confirmed
            return redirect()->route('timesheets.index');
        }else{
            $this->user->hours = $this->weekly_hours->sum('hours');
            // $this->user->amount = $this->weekly_hours->sum('amount');
            $this->user->hourly = $this->user->vendors()->where('vendors.id', 1)->first()->pivot->hourly_rate;
        }
    }

    public function getUserHoursAmountProperty()
    { 
        $total = 0;

        // dd($this->user->hourly);
        $total = $this->user->hours * $this->user->hourly;

        $this->user->amount = $total;
        return $total;
    } 

    public function store()
    {
        $this->validate();
        // $this->authorize('update', $this->expense);

        //use $weekly_hours from below for data but refresh?
        $weekly_projects = $this->weekly_hours->groupBy('project.id');
        $hourly = $this->user->hourly;

        //change $hourly for User under this Vendor
        // $this->user->vendor->auth_user_role->first()->pivot->hourly_rate = $hourly;

        //$this->user->vendor->auth_user_role->first()->pivot->hourly_rate;

        $user_vendor_hourly_database = $this->user->vendor->auth_user_role->first()->pivot;
        $user_vendor_hourly_database->hourly_rate = $hourly;
        $user_vendor_hourly_database->save();

        // dd($user_vendor_hourly_database);
        // $user->vendors()->attach($this->user_add_type->id, [
        //     'role_id' => $this->user->role,
        //     'hourly_rate' => $this->user->hourly_rate,
        //     'start_date' => today(),
        //     'via_vendor_id' => $via_vendor->id,
        // ]);

        foreach($weekly_projects as $project_id => $project_weekly_hours){
            $hours = $project_weekly_hours->sum('hours');

            $timesheet = Timesheet::create([
                'date' => $this->week->startOfWeek()->format('Y-m-d'),
                'user_id' => $this->user->id,
                'vendor_id' => $this->user->vendor->id,
                'project_id' => $project_id,
                'hours' => $hours,
                'amount' => $hourly * $hours,
                'hourly' => $hourly,
                'created_by_user_id' => auth()->user()->id,
                // 'paid_by' => auth()->user()->primary_vendor_id,
                // 'check_id' => auth()->user()->id,
                
                // 'invoice' => ,
                // 'note' => ,
            ]);

            //get $weekly_hours->pluck('id') and associate $timesheet->id with each...
            foreach($project_weekly_hours as $hour){
                $hour->timesheet()->associate($timesheet)->save();
            }
        }
        return redirect()->route('timesheets.show', $timesheet->id);
    }

    public function render()
    {
        $weekly_days = $this->weekly_hours->groupBy(['date', 'project.project_name']);
        $week_date = $this->week->startOfWeek()->toFormattedDateString();

        return view('livewire.timesheets.form', [
            'weekly_days' => $weekly_days,
            'week_date' => $week_date,
        ]);
    }
}
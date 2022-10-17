<?php

namespace App\Http\Livewire\Hours;

use Livewire\Component;

use App\Models\Hour;
use App\Models\Project;
use App\Models\Timesheet;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class HoursForm extends Component
{
    public $hours = [];
    public $projects = [];
    public $selected_date = NULL;

    protected $listeners = ['selectedDate'];

    protected function rules()
    {
        return [    
            'hours.*.amount' => 'nullable|numeric|min:.25|max:16',
        ];
    }

    public function getDays()
    {
        return new \DatePeriod(
            Carbon::parse("21 days ago")->startOfWeek(Carbon::MONDAY),
            CarbonInterval::day(),
            Carbon::parse("1 week")->startOfWeek(Carbon::MONDAY)->next("Week")
        );
    }

    // public function confirmedDays()
    // {

    // }
    

    public function selectedDate($date)
    {
        //if current User doesnt have any hours for this date let them add new project, if they do let them edit if not yet paid (or timesheet created)
        $this->selected_date = Carbon::parse($date);

        $user_day_hours = Hour::where('user_id', auth()->user()->id)->where('date', $date)->get();

        $this->hours = NULL;
        $this->resetValidation();

        if($user_day_hours->isEmpty()){
            $this->view_text = [
                'card_title' => 'Create Daily Hours',
                'button_text' => 'Add Daily Hours',
                'form_submit' => 'store',             
            ];
        }else{
            //insert hours into the projects_id array
            foreach($this->projects as $index => $project){
                $project_user_date = Hour::where('user_id', auth()->user()->id)->where('date', $date)->where('project_id', $project->id)->get();
                if($project_user_date->isEmpty()){

                }else{
                    $this->hours[$index]['amount'] = $project_user_date->first()->hours;
                }                
            }

            $this->view_text = [
                'card_title' => 'Edit Daily Hours',
                'button_text' => 'Update Daily Hours',
                'form_submit' => 'update',             
            ];
        }
    }

    public function getHoursCountProperty()
    {
        return collect($this->hours)->where('amount', '!=', '')->sum('amount');
    }

    public function mount()
    {  
        $this->selected_date = Carbon::parse(today()->format('Y-m-d'));
        $this->selectedDate($this->selected_date);

        //active only
        $this->projects = Project::active()->orderBy('created_at', 'DESC')->get();

        $this->view_text = [
            'card_title' => 'Create Daily Hours',
            'button_text' => 'Add Daily Hours',
            'form_submit' => 'store',             
        ];        
    }

    public function store()
    {
        $this->validate();
        
        foreach($this->hours as $index => $hour){
            // dd($hour['amount']);
            $this_hour = Hour::create([
                'date' => $this->selected_date,
                'hours' => $hour['amount'],
                'project_id' => $this->projects[$index]['id'],
                'user_id' => auth()->user()->id,
                'vendor_id' => auth()->user()->vendor->id,
                'created_by_user_id' => auth()->user()->id,
            ]);
        }

        $this->hours = NULL;
        $this->selected_date = Carbon::parse(today()->format('Y-m-d'));
    }

    public function update()
    {
        $this->validate();

        if($this->hours){
            foreach($this->hours as $index => $hour){
                $user_day_hours = Hour::where('user_id', auth()->user()->id)->where('date', $this->selected_date)->where('project_id', $this->projects[$index]['id'])->first();
         
                if(isset($user_day_hours)){
                    // $user_day_hours = Hour::where('user_id', auth()->user()->id)->where('date', $this->selected_date)->where('project_id', $this->projects[$index]['id'])->first();
                    if((int)$hour['amount'] == 0){
                        $user_day_hours->delete();
                    }else{
                        $user_day_hours->hours = $hour['amount'];
                        $user_day_hours->save();
                    }
                }else{
                    $this_hour = Hour::create([
                        'date' => $this->selected_date,
                        'hours' => $hour['amount'],
                        'project_id' => $this->projects[$index]['id'],
                        'user_id' => auth()->user()->id,
                        'vendor_id' => auth()->user()->vendor->id,
                        'created_by_user_id' => auth()->user()->id,
                    ]);
                }
            }
        }

        $this->hours = NULL;
        $this->selected_date = Carbon::parse(today()->format('Y-m-d'));
    }

    public function render()
    {
        $confirmed_dates =        
            Timesheet::
                orderBy('date', 'DESC')
                ->where('user_id', auth()->user()->id)
                ->first();

        $confirmed_last_week = new \DatePeriod(
            $confirmed_dates->date->startOfWeek(Carbon::MONDAY)->subDays(8),
            CarbonInterval::day(),
            $confirmed_dates->date->endOfWeek(Carbon::SUNDAY)
        );

        foreach($confirmed_last_week as $confirmed_week_day)
        {
            $confirmed_week_days[] = $confirmed_week_day->format('Y-m-d');
        }

        foreach ($this->getDays() as $day) {
            $user_day_hours = Hour::where('user_id', auth()->user()->id)->where('date', $day->format('Y-m-d'))->get();

            $new_days[] = [
                'format' => $day->format('Y-m-d'),
                'day' => $day->day,
                'month' => $day->month,
                'has_hours' => $user_day_hours->isEmpty() ? FALSE : TRUE,
                'confirmed_date' => in_array($day->format('Y-m-d'), $confirmed_week_days) ? TRUE : FALSE
            ];      
        }
        
        return view('livewire.hours.form', [
            'days' => $new_days,
        ]);
    }
}
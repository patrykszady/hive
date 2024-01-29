<?php

namespace App\Livewire\Hours;

use App\Models\Hour;
use App\Models\Project;
use App\Models\Timesheet;

use App\Livewire\Forms\HourForm;

use Livewire\Component;
use Livewire\Attributes\Title;

use Carbon\Carbon;
use Carbon\CarbonInterval;

class HourCreate extends Component
{
    public HourForm $form;

    public $projects = [];
    public $days = [];
    public $hours_count_store = 0;
    public $selected_date = NULL;

    public $view_text = [
        'card_title' => 'Create Daily Hours',
        'button_text' => 'Add Daily Hours',
        'form_submit' => 'save',
    ];

    protected $listeners = ['refreshComponent' => '$refresh', 'selectedDate'];

    public function mount()
    {
        $this->projects = Project::active()->orderBy('created_at', 'DESC')->get();

        $confirmed_weeks =
        Timesheet::
            orderBy('date', 'DESC')
            ->where('user_id', auth()->user()->id)
            ->where('date', '>', today()->subWeeks(3))
            ->get()
            ->groupBy('date');

        if(!$confirmed_weeks->isEmpty()){
            foreach($confirmed_weeks as $confirmed_week)
            {
                $week_days = new \DatePeriod(
                    $confirmed_week->first()->date->startOfWeek(Carbon::MONDAY),
                    CarbonInterval::day(),
                    $confirmed_week->first()->date->endOfWeek(Carbon::SUNDAY)
                );

                foreach($week_days as $confirmed_date){
                    $confirmed_week_days[] = $confirmed_date->format('Y-m-d');
                }
            }
        }else{
            $confirmed_week_days[] = NULL;
        }

        $this->days = collect();
        foreach($this->getDays() as $day){
            $user_day_hours = Hour::where('user_id', auth()->user()->id)->where('date', $day->format('Y-m-d'))->get();

            $this->days->push([
                'format' => $day->format('Y-m-d'),
                'day' => $day->format('d'),
                'month' => $day->format('m'),
                'has_hours' => $user_day_hours->isEmpty() ? FALSE : TRUE,
                'confirmed_date' => in_array($day->format('Y-m-d'), $confirmed_week_days) ? TRUE : FALSE
            ]);
        }

        $this->form->setProjects($this->projects->toArray());
    }

    public function updated($field)
    {
        $this->validate();
    }

    public function getHoursCountProperty()
    {
        $this->hours_count_store = collect($this->form->projects)->where('hours', '!=', NULL)->sum('hours');
        return $this->hours_count_store;
    }

    public function getDays()
    {
        return new \DatePeriod(
            Carbon::parse("2 weeks ago")->startOfWeek(Carbon::MONDAY),
            CarbonInterval::day(),
            Carbon::parse("1 week")->startOfWeek(Carbon::MONDAY)->next("Week")
        );
    }

    public function selectedDate($date)
    {
        //if current User doesnt have any hours for this date let them add new project, if they do let them edit if not yet paid (or timesheet created)
        //if week already paid, dont show.
        $this->selected_date = Carbon::parse($date);

        $user_day_hours = Hour::where('user_id', auth()->user()->id)->where('date', $date)->get();

        $this->resetValidation();

        if($user_day_hours->isEmpty()){
            $this->view_text = [
                'card_title' => 'Create Daily Hours',
                'button_text' => 'Add Daily Hours',
                'form_submit' => 'save',
            ];
        }else{
            //insert hours into the projects_id array
            foreach($this->projects as $index => $project){
                $project_user_date = Hour::where('user_id', auth()->user()->id)->where('date', $date)->where('project_id', $project->id)->get();
                if($project_user_date->isEmpty()){

                }else{
                    $project->hours = $project_user_date->first()->hours;
                    $project->hour_id = $project_user_date->first()->id;
                }
            }

            $this->view_text = [
                'card_title' => 'Edit Daily Hours',
                'button_text' => 'Update Daily Hours',
                'form_submit' => 'edit',
            ];
        }

        $this->form->setProjects($this->projects->toArray());
    }

    public function save()
    {
        if($this->hours_count_store == 0){
            $this->addError('hours_count', 'Daily Hours need at least one entry.');
        }else{
            $this->form->store();
            return redirect()->route('hours.create');
            // $this->dispatch('refreshComponent')->self();
        }
    }

    public function edit()
    {
        if($this->hours_count_store == 0){
            $this->addError('hours_count', 'Daily Hours need at least one entry.');
        }else{
            $this->form->update();
            return redirect()->route('hours.create');
            // $this->dispatch('refreshComponent')->self();
        }
        $this->form->update();
    }

    #[Title('Hours')]
    public function render()
    {
        $first_name = auth()->user()->first_name;
        if(is_null($this->selected_date)){
            //1-27-23 only if the date is not yet paid with a timesheet... see above?
            $this->selected_date = Carbon::parse(today()->format('Y-m-d'));
            $this->selectedDate($this->selected_date);
        }

        return view('livewire.hours.form', [
            'first_name' => $first_name,
        ]);
    }
}

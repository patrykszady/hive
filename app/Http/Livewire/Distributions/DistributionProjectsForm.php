<?php

namespace App\Http\Livewire\Distributions;

use App\Models\Distribution;
use App\Models\Project;

use Livewire\Component;

class DistributionProjectsForm extends Component
{
    public Project $project;
    public $modal_show = FALSE;
    public $distributions = [];
    public $percent_distributions_sum = 0;

    protected $listeners = ['addDis', 'resetModal'];

    protected function rules()
    {
        return [
            'distributions.*.percent' => 'required|numeric|min:10',
            'distributions.*.percent_amount' => 'nullable',
            'percent_distributions_sum' => 'required|numeric|min:100|max:100',
        ];
    }

    protected $messages = 
    [
        'percent_distributions_sum' => 'Percent sum must equal to 100%',
        'expense_splits.*.amount.required_if' => 'The split amount field is required.',
        'expense_splits.*.amount.numeric' => 'The amount field must be numberic.',
    ];

    public function updated($field, $value) 
    {
        $this->validateOnly($field);
        //distributions.0.percent
        $index = substr($field, 14, -8);
        if($field == 'distributions.' . $index . '.percent'){
            if($value == "" || $value == 0 || $value == 0.0 || $value == 0.00){
                $this->distributions[$index]['percent'] = "";
                // $this->distributions[$index]['percent_amount'] = NULL;
            }      
        }
    }

    public function mount()
    {      
        $this->view_text = [
            'card_title' => 'New Distributions',
            'button_text' => 'Save Distributions',
            'form_submit' => 'store',             
        ];

        $this->distributions = Distribution::all();
    }

    public function resetModal()
    {
        // Public functions should be reset here
        $this->distributions->each(function ($item, $key) {
            $item->percent = NULL;
            $item->percent_amount = NULL;
        });
        
        $this->percent_distributions_sum = 0;
    }

    public function getPercentSumProperty()
    {
        $this->percent_distributions_sum = 
            collect($this->distributions)
                ->reject(function($distribution){
                    return !$distribution->percent;
                })->sum('percent');

        foreach($this->distributions as $distribution){
            if($distribution->percent != "" && $distribution->percent != 0 && $distribution->percent != NULL){
                $percent = '.' . $distribution->percent;
                $distribution->percent_amount = round($this->project->finances['profit'] * $percent, 2);
            }else{
                $distribution->percent_amount = NULL;
            }
        }

        return $this->percent_distributions_sum;
    }

    public function addDis(Project $project)
    {   
        $this->project = $project;

        $this->modal_show = TRUE;
    }

    public function store()
    {
        $this->validate();
        foreach($this->distributions as $distribution){
            $distribution->projects()->attach($this->project->id, array(
                'percent' => $distribution->percent,
                'amount' => $distribution->percent_amount,
            ));
        }
        
        $this->modal_show = FALSE;
        //emit and refresh so distributions.index removes/refreshes projects_doesnt_dis
        $this->emitTo('distributions.distributions-index', 'refreshComponent');
        //reset modal data
        $this->emitSelf('resetModal');
        
        //NOTIFICATIONS!
    }

    public function render()
    {
        return view('livewire.distributions.projects-form', [
            // 'distributions' => $distributions,
        ]);
    }
}
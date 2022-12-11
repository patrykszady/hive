<?php

namespace App\Http\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectStatus;

use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectsShow extends Component
{
    use AuthorizesRequests;

    public Project $project;

    public $finances = [];
    public $project_status = NULL;
    
    // protected function rules()
    // {
    //     return [
    //         'project_status' => 'nullable',
    //     ];
    // }

    public function mount()
    {
        $this->project_status = $this->project->project_status ? $this->project->project_status->title : NULL;

        $expenses_sum = $this->project->expenses()->where('reimbursment', 'Client')->sum('amount');
        $splits_sum = $this->project->expenseSplits()->where('reimbursment', 'Client')->sum('amount');

        $this->finances['estimate'] = $this->project->bids()->where('vendor_id', auth()->user()->vendor->id)->where('type', 1)->sum('amount');
        $this->finances['change_orders'] = $this->project->bids()->where('vendor_id', auth()->user()->vendor->id)->where('type', 2)->sum('amount');
        $this->finances['reimbursments'] = $splits_sum + $expenses_sum;
        $this->finances['total_project'] = $this->finances['reimbursments'] + $this->finances['estimate'] + $this->finances['change_orders'];
        $this->finances['expenses'] = $this->project->expenses->sum('amount') + $this->project->expenseSplits->sum('amount');
        $this->finances['timesheets'] = $this->project->timesheets->sum('amount');
        $this->finances['total_cost'] = $this->finances['timesheets'] + $this->finances['expenses'];
        $this->finances['payments'] = $this->project->payments->sum('amount');
        $this->finances['profit'] = $this->finances['total_project'] - $this->finances['total_cost'];
        $this->finances['balance'] = $this->finances['total_project'] - $this->finances['payments'];        
    }

    // public funpction updated($field) 
    // {
    //     dd($this->project_status);
    //     $this->validateOnly($field);
    // }

    //7-20-2022 move to ProjectStatus Livewire component
    public function change_project_status()
    {
        if($this->project->project_status){
            //UPDATE ProjectStatus
            //find this project status and change
            $update_project_status = $this->project->project_status;

            $update_project_status->title = $this->project_status;
            $update_project_status->save();
        }else{
            //new ProjectStatus
            $update_project_status = ProjectStatus::create([
                'project_id' => $this->project->id,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'title' => $this->project_status,
            ]);
        }

        $update_project_status->refresh();
    }

    public function render()
    {
        $this->authorize('view', $this->project);

        return view('livewire.projects.show', [
            // 'project' => $this->project,
        ]);
    }
}
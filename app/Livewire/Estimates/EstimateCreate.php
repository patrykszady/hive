<?php

namespace App\Livewire\Estimates;

use Livewire\Component;

use App\Models\Project;
use App\Models\Estimate;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EstimateCreate extends Component
{
    use AuthorizesRequests;

    public Project $project;

    public function mount(){
        //authorize, make sure logged in vendor can create estimates for this project.
        // dd($this->project);
        // $this->validate();

        $estimate = Estimate::create([
            'project_id' => $this->project->id,
            'belongs_to_vendor_id' => auth()->user()->vendor->id,
        ]);

        // dd($estimate->project);

        return(redirect(route('estimates.show', $estimate->id)));
        //create new estimate and send to estimates.show view
    }

    public function render()
    {
        dd('in rendor of estimate_form');
        return view('livewire.estimates.form');
    }
}

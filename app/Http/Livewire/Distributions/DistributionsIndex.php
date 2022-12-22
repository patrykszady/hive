<?php

namespace App\Http\Livewire\Distributions;

use App\Models\User;
use App\Models\Project;
use App\Models\Distribution;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DistributionsIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {      
        $this->view_text = [
            'card_title' => 'Distributions',
            'button_text' => 'New Distribution',
            'form_submit' => 'store',             
        ];
    }

    public function render()
    {
        $this->authorize('viewAny', Distribution::class);

        //12/14/2022 update these (->refresh()) when projects_doesnt_dis creates new distributions
        $projects_has_dis = 
            Project::with('distributions')
                ->whereHas('distributions')
                ->orderBy('created_at', 'DESC')
                ->paginate(5, ['*'], 'projectYesDistributions');

        //where status = complete
        $projects_doesnt_dis = 
            Project::with(['distributions', 'project_status'])
                ->whereDoesntHave('distributions')
                ->whereHas('project_status', function($query) {
                    $query->where('title', 'Complete');
                })                
                ->orderBy('created_at', 'DESC')
                ->paginate(5, ['*'], 'projectNoDistributions');
        
        $distributions = Distribution::all();

        return view('livewire.distributions.index', [
            'projects_has_dis' => $projects_has_dis,
            'projects_doesnt_dis' => $projects_doesnt_dis,
            'distributions' => $distributions,
        ]);
    }
}
<?php

namespace App\Http\Livewire\Projects;

use App\Models\Project;
use App\Models\Client;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectsIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    public $project_name = '';
    public $client_id = '';
    public $project_status_title = '';
    public $view;
    
    protected $queryString = [
        'project_name' => ['except' => ''],
        'client_id' => ['except' => ''],
        'project_status_title' => ['except' => '']
    ];

    public function mount()
    {              
        $this->clients = Client::orderBy('created_at', 'DESC')->get();
    }

    public function updating($field)
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('viewAny', Project::class);
        
        $client_id = $this->client_id;

        $projects = Project::orderBy('created_at', 'DESC')
            ->with('project_status', 'client')
            //8-23-2022 or where address ..... where 'name' (appended)
  
            ->when($client_id != NULL, function ($query, $client_id) {
                return $query->where('client_id', 'like', "{$this->client_id}");
            })            
            ->when($this->project_status_title != NULL, function($query) {
                return $query->whereHas('project_status', function ($query) {
                    return $query->where('title', $this->project_status_title);
                  });
            })

            // 8-23-2022 orWhere works on ClientsIndex
            ->where('project_name', 'like', "%{$this->project_name}%")
            // ->orWhere(function ($query) {
            //     $query->where('address', 'like', "{$this->project_name}");
            // })
            // ->orWhere('address', 'like', "%{$this->project_name}%")
            ->paginate(10);

        // dd($projects);

        return view('livewire.projects.index', [
            'projects' => $projects,
        ]);
    }
}
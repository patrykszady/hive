<?php

namespace App\Http\Livewire\Projects;

// use App\Models\User;
use App\Models\Project;
use App\Models\Client;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectsForm extends Component
{
    use AuthorizesRequests;

    // public Client $client;
    public $client = NULL;
    public Project $project;
    
    public $address = NULL;
    public $clients = NULL;

    public $modal_show = NULL;

    protected $listeners = ['createProject'];

    protected function rules()
    {
        return [
            'project.project_name' => 'required|min:3',
            'project.address' => 'required|min:4',
            'project.address_2' => 'nullable',
            'project.city' => 'required|min:4',
            'project.state' => 'required|min:2|max:2',
            'project.zip_code' => 'required|digits:5',
            'client' => 'nullable',
            'client.id' => 'nullable',
            'client.address' => 'nullable',
            'client.type' => 'nullable',
            'address' => 'nullable'
        ];
    }

    protected $messages = 
    [

    ];

    public function updated($field) 
    {
        // $this->validate();
        $this->validateOnly($field);
        
        // if($field == 'project.project_name'){
        //     if(!is_null($this->project->project_name)){
        //         $this->address = TRUE;
        //     }else{
        //         $this->address = NULL;
        //     }
        // }  
        
        if($field == 'client.id'){
            if(isset($this->client->id)){
                $this->client = Client::find($this->client->id);
                // $this->client->type = 'Existing';
            }
        }

        // dd($this->client);
    }

    public function mount()
    {
        $this->view_text = [
            'card_title' => 'Create Project',
            'button_text' => 'Add Project to User',
            'form_submit' => 'store',             
        ];

        $this->clients = Client::all();   
        $this->project = Project::make();  
        $this->client = Client::make(); 
    }

    public function createProject($client_id = NULL)
    {
        // dd($client_id);
        if(isset($client_id)){
            $this->client = Client::find($client_id);      
            // dd($this->client->address);
            $this->client->type = 'Existing'; 
        }else{
            $this->client = Client::make();
        }

        $this->modal_show = TRUE;
    }

    public function store()
    {
        $this->validate();

        dd($this);

        $project = Project::create([
            'project_name' => $this->project->project_name,
            'client_id' => $this->client->id,
            'address' => $this->client->address,
            'address_2' => $this->client->address_2,
            'city' => $this->client->city,
            'state' => $this->client->state,
            'zip_code' => $this->client->zip_code,
            'belonges_to_vendor_id' => auth()->user()->vendor->id,
        ]);

        //12-4-2022 NOTIFICATIONS PLEASE!!! EASYYY~~
        return redirect(route('projects.show', $project->id));
    }

    public function render()
    {
        return view('livewire.projects.form', [
            // 'user' => $user,
        ]);
    }
}

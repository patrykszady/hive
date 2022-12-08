<?php

namespace App\Http\Livewire\Projects;

use App\Models\ProjectStatus;
use App\Models\Project;
use App\Models\Client;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectsForm extends Component
{
    use AuthorizesRequests;

    public Client $client;
    public Project $project;
    
    public $address = FALSE;
    public $new_address = FALSE;
    public $clients = NULL;
    public $client_project_id_address = NULL;
    public $addresses = [];

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

            'client.id' => 'nullable',
            'client.address' => 'nullable',
            'client.addresses' => 'nullable',
            'client.project_id' => 'nullable'
        ];
    }

    protected $messages = 
    [

    ];

    public function updated($field) 
    {
        // $this->validate();
        $this->validateOnly($field);
        
        if($field == 'project.project_name'){
            if(!is_null($this->project->project_name)){
                $this->address = TRUE;
            }else{
                $this->address = NULL;
            }
        }  
        
        if($field == 'client.id'){
            $this->project->project_name = NULL;

            if(!is_null($this->client->id)){
                $this->client = Client::find($this->client->id);
                $this->addresses = $this->client->projects;
                $this->address = TRUE;
                // $this->client->project_id = $this->client_project_id_address;
                // dd($this->client->addresses);
            }
            
            // else{
            //     $this->client = Client::make();
            //     $this->addresses = [];
            //     $this->project = Project::make();
            //     $this->address = FALSE;
            // }            
        }

        if($field == 'client_project_id_address'){
            if($this->client_project_id_address == 'NEW'){
                $this->new_address = TRUE;
            }else{
                $this->new_address = FALSE;
                //if is_numeric($client_project_id_address);
            }
        }
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
        if(isset($client_id)){
            $this->client = Client::find($client_id);      
        }
        
        $this->modal_show = TRUE;
    }

    public function store()
    {
        if(is_numeric($this->client_project_id_address)){
            $project_address = Project::find($this->client_project_id_address);
            $this->project->address = $project_address->address;
            $this->project->address_2 = $project_address->address_2;
            $this->project->city = $project_address->city;
            $this->project->state = $project_address->state;
            $this->project->zip_code = $project_address->zip_code;
        }else{
            //if $this->client_project_id_address = NEW;
            //use $this->project;
        }

        $this->validate();

        $project = Project::create([
            'project_name' => $this->project->project_name,
            'client_id' => $this->client->id,
            'address' => $project_address->address,
            'address_2' => $project_address->address_2,
            'city' => $project_address->city,
            'state' => $project_address->state,
            'zip_code' => $project_address->zip_code,
            'belongs_to_vendor_id' => auth()->user()->vendor->id,
        ]);
        
        ProjectStatus::create([
            'project_id' => $project->id,
            'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
            'title' => 'Estimate',
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

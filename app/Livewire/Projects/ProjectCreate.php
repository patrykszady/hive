<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectForm;

use App\Models\Project;
use Livewire\Component;

use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectCreate extends Component
{
    use AuthorizesRequests;

    public ProjectForm $form;

    public $view_text = [
        'card_title' => 'Create Project',
        'button_text' => 'Create',
        'form_submit' => 'save',
    ];

    public $clients;
    public $client_addresses = [];

    public $modal_show = FALSE;

    protected $listeners = ['newProject'];

    public function mount($clients)
    {
        $this->clients = $clients;
    }

    public function updated($field, $value)
    {
        $this->validateOnly($field);
        if($field == 'form.client_id'){
            if($value){
                $this->resetAddress();
                $client = $this->clients->where('id', $value)->first();
                $this->client_addresses = $client->projects;

                if($this->client_addresses->isEmpty()){
                    $this->client_addresses =
                        collect([
                            collect([
                                // 'CLIENT_PROJECT' => 'CLIENT_PROJECT',
                                'address' => $client['address'],
                                'address_2' => $client['address_2'],
                                'city' => $client['city'],
                                'state' => $client['state'],
                                'zip_code' => $client['zip_code'],
                            ])
                        ]);
                        // Project::make([
                        //     // 'CLIENT_PROJECT' => 'CLIENT_PROJECT',
                        //     'address' => $client['address'],
                        //     'address_2' => $client['address_2'],
                        //     'city' => $client['city'],
                        //     'state' => $client['state'],
                        //     'zip_code' => $client['zip_code'],
                        // ]);
                }else{
                    $this->client_addresses->unique('address');
                }
            }else{
                $this->form->reset();
                $this->resetValidation();
            }

            // dd($this->client_addresses);
        }

        if($field == 'form.project_existing_address'){
            if($value && $value != 'NEW'){
                if($value == 'CLIENT_PROJECT'){
                    $project_address = $this->client_addresses->first();
                }else{
                    $project_address = $this->client_addresses->where('id', $value)->first();
                }

                // dd($project_address);

                $this->form->address = $project_address['address'];
                $this->form->address_2 = $project_address['address_2'];
                $this->form->city = $project_address['city'];
                $this->form->state = $project_address['state'];
                $this->form->zip_code = $project_address['zip_code'];
            }elseif($value == 'NEW'){
                $this->form->reset('address', 'address_2', 'city', 'zip_code', 'state');
                $this->resetValidation();
            }else{
                $this->resetAddress();
            }
        }
    }

    public function resetAddress()
    {
        $this->form->reset('address', 'address_2', 'city', 'zip_code', 'state', 'project_existing_address');
        $this->resetValidation();
    }

    public function resetModal()
    {
        $this->form->reset();
        $this->resetValidation();
    }

    public function newProject()
    {
        $this->resetModal();

        // //coming from clients.show view / $client already set
        // if(isset($client_id)){
        //     $this->client = Client::find($client_id);
        //     $this->getAddresses();
        //     // $this->address = TRUE;
        // }

        $this->modal_show = TRUE;
    }

    public function save()
    {
        $project = $this->form->store();

        //9-1-2023 NOTIFICATIONS when we redirect with Livewire...
        return redirect(route('projects.show', $project->id));
    }

    public function render()
    {
        return view('livewire.projects.form', [

        ]);
    }
}

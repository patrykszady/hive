<?php

namespace App\Http\Livewire\Clients;

use App\Models\Client;
use App\Models\User;

use Livewire\Component;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientsForm extends Component
{
    use AuthorizesRequests;

    public User $user;
    public Client $client;
    public $user_clients = NULL;
    public $address = NULL;

    protected $listeners = ['userClient'];

    protected function rules()
    {
        return [
            'client.business_name' => 'nullable|min:3',
            'client.address' => 'required|min:4',
            'client.address_2' => 'nullable',
            'client.city' => 'required|min:4',
            'client.state' => 'required|min:2|max:2',
            'client.zip_code' => 'required|digits:5',

            'user.id' => 'nullable',
            'client' => 'nullable',
            'address' => 'nullable',
            'user_clients' => 'nullable',
        ];
    }

    protected $messages = 
    [

    ];

    public function userClient(User $user)
    {
        $this->user_clients = $user->clients;
        $this->user = $user;
        $this->address = TRUE;
    }

    public function updated($field) 
    {
        $this->validateOnly($field);
    }

    public function mount()
    {  
        $this->user = User::make();

        if(isset($this->client)){    
            $this->client = Client::find($this->client->add_type);        
            $this->client->add_type = $client->id;
            $this->view_text = [
                'card_title' => 'Update Client',
                'button_text' => 'Update Client',
                'form_submit' => 'update',             
            ];
        }else{
            $this->client = Client::make();
            $this->client->add_type = 'NEW';

            $this->view_text = [
                'card_title' => 'Create Client',
                'button_text' => 'Create Client',
                'form_submit' => 'store',             
            ];
        }
    }

    public function store()
    {
        //12-3-22 authorize
        $this->validate();

        $client = Client::create([
            'business_name' => $this->client->business_name,
            'address' => $this->client->address,
            'address_2' => $this->client->address_2,
            'city' => $this->client->city,
            'state' => $this->client->state,
            'zip_code' => $this->client->zip_code,
        ]);

        //ADD CLIENT TO USER
        $this->user->clients()->attach($client->id);

        return redirect()->route('clients.show', $client->id);
    }
    
    public function render()
    {
        return view('livewire.clients.form');
    }
}

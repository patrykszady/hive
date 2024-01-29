<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\User;

use App\Livewire\Forms\ClientForm;

use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClientCreate extends Component
{
    use AuthorizesRequests;

    public ClientForm $form;

    public Client $client;
    public User $user;

    // public $client = NULL;
    public $client_name = NULL;
    public $user_clients = [];
    public $user_client_id = NULL;

    public $view_text = [
        'card_title' => 'Create Client',
        'button_text' => 'Create Client',
        'form_submit' => 'save',
    ];

    // public $address = NULL;
    public $team_member = FALSE;

    public $modal_show = FALSE;

    protected $listeners = ['addUser', 'resetModal', 'editClient'];

    // public function rules()
    // {
    //     return [
    //         'user_client_id' => 'nullable',
    //         'user.full_name' => 'nullable',
    //         'client.name' => 'nullable',
    //     ];
    // }

    public function mount()
    {

    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function addUser(User $user, $client_id)
    {
        if(is_numeric($client_id)){
            $this->client = Client::findOrFail($client_id);
            $this->user_client_id = $this->client->id;
            $this->client_name = $this->client->name;

            $this->view_text = [
                'card_title' => 'Add User to Client',
                'button_text' => 'Add User',
                'form_submit' => 'add_user_to_client',
            ];
        }else{
            $this->user_clients = $user->clients()->withoutGlobalScopes()->with('vendors')->get()->keyBy('id');

            if($this->user_clients->isEmpty()){
                $this->user_clients = NULL;
            }
        }

        $this->form->setUser($user);
        // if(is_numeric($team_member)){
        //     $this->team_member = $team_member;

        //     $this->user = User::findOrFail($this->team_member);
        // }else{
        //     //role and hourly here for new vendor?
        //     $this->team_member = 'index';
        // }

        $this->modal_show = TRUE;
    }

    public function newClient()
    {
        $this->user_client_id = NULL;
        $this->user_client_id = 'NEW';

        // if(is_numeric($team_member)){
        //     $this->team_member = $team_member;

        //     $this->user = User::findOrFail($this->team_member);
        // }else{
        //     //role and hourly here for new vendor?
        //     $this->team_member = 'index';
        // }

        // $this->modal_show = TRUE;
        // $this->address = TRUE;
    }

    // // Everthing in top pulbic should be reset here
    // public function resetModal()
    // {
    //     // $this->modal_show = FALSE;
    //     // $this->client = Client::make();
    //     // $this->user = NULL;
    //     // $this->address = NULL;
    // }

    public function add_user_to_client()
    {
        //ADD USER TO CLIENT
        $this->form->user->clients()->attach($this->client->id);

        $this->dispatch('notify',
            type: 'success',
            content: 'User Added to Client',
            route: 'clients/' . $this->client->id
        );

        $this->modal_show = FALSE;

        $this->dispatch('refreshComponent')->to('clients.clients-show');
    }

    public function editClient(Client $client)
    {

        // dd('in editClient');
        // $this->resetModal();

        $this->client = $client;

        // if(!$expense->splits->isEmpty()){
        //     $this->hasSplits($expense->splits);
        // }

        $this->form->setClient($this->client);

        $this->view_text = [
            'card_title' => 'Update Client',
            'button_text' => 'Update',
            'form_submit' => 'edit',
        ];
        // $this->form->setExpense($expense);

        // $this->view_text = [
        //     'card_title' => 'Update Expense',
        //     'button_text' => 'Update',
        //     'form_submit' => 'edit',
        // ];

        $this->modal_show = TRUE;
    }

    public function edit()
    {
        $client = $this->form->update();

        $this->modal_show = FALSE;

        $this->dispatch('notify',
            type: 'success',
            content: 'Client Updated',
            route: 'clients/' . $client->id
        );

        $this->dispatch('refreshComponent')->to('clients.clients-show');
    }

    public function save()
    {
        //12-3-22 authorize
        if(!is_numeric($this->user_client_id)){
            $client = $this->form->store();

            $this->dispatch('notify',
                type: 'success',
                content: 'Client Created',
                route: 'clients/' . $client->id
            );
        }else{
            $auth_user_vendor = auth()->user()->vendor;
            $client = $this->user_clients[$this->user_client_id];
            $client_vendors = $client->vendors()->pluck('vendors.id')->toArray();

            $auth_vendor_in_client = in_array($auth_user_vendor->id, $client_vendors);

            if($auth_vendor_in_client){
                $this->dispatch('notify',
                    type: 'success',
                    content: 'This Client Exists',
                    route: 'clients/' . $client->id
                );
            }else{
                $auth_user_vendor->clients()->attach($client->id);
                $this->dispatch('notify',
                    type: 'success',
                    content: 'Client Added',
                    route: 'clients/' . $client->id
                );
            }
        }

        //resetModal
        $this->modal_show = FALSE;
        // $this->resetModal();
        $this->dispatch('refreshComponent')->to('clients.clients-index');
    }

    public function render()
    {
        return view('livewire.clients.form');
    }
}

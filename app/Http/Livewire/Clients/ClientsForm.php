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

    public $user_cell = NULL;
    public $user_form = NULL;
    public $client_user_form = NULL;

    protected function rules()
    {
        return [
            'user_cell' => 'required|digits:10',
            'user.first_name' => 'required|min:2',
            'user.last_name' => 'required|min:2',
            'user.full_name' => 'nullable',
            'user.cell_phone' => [
                'required',
                'digits:10',
                Rule::unique('users', 'cell_phone')->ignore($this->user->id),
            ],
            'user.email' => [
                'required',
                'email',
                'min:6',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
        ];
    }

    protected $messages = 
    [
        'user_cell.digits' => 'Phone number must be 10 digits',
    ];

    public function updated($field) 
    {
        $this->validateOnly($field);
    }

    //SAME AS UsersForm::user_cell
    public function user_cell()
    {
        $this->user_form = NULL;
        $this->client_user_form = NULL;
        $this->validateOnly('user_cell');

        $user_exists = User::where('cell_phone', $this->user_cell)->first();

        if($user_exists){
            //show existing clients and/or vendors if any
            // dd($user_exists->vendors);
            $this->user = $user_exists;
            $this->user->full_name = $user_exists->full_name;
            $this->client_user_form = TRUE;
        }else{
            //new user
            $this->user = User::make();
            $this->user->cell_phone = $this->user_cell;

            $this->user_form = TRUE;
            $this->client_user_form = TRUE;
        }
    }

    public function mount()
    {  
        $this->user = User::make();
        if(isset($this->client)){            
            $this->view_text = [
                'card_title' => 'Update Client',
                'button_text' => 'Update Client',
                'form_submit' => 'update',             
            ];
        }else{
            $this->view_text = [
                'card_title' => 'Create Client',
                'button_text' => 'Create Client',
                'form_submit' => 'store',             
            ];
        }
    }

    public function store()
    {
        //authorize

        //create new user
        if(!$this->user->id){
            $user = User::create([
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'cell_phone' => $this->user->cell_phone,
                'email' => $this->user->email
            ]);
        }else{
            //existing User
            $user = $this->user;
        }

        $client = Vendor::create([
            'business_name' => $this->via_vendor->business_name,
            'address' => $this->via_vendor->address,
            'address_2' => $this->via_vendor->address_2,
            'city' => $this->via_vendor->city,
            'state' => $this->via_vendor->state,
            'zip_code' => $this->via_vendor->zip_code,
        ]);

        //ADD VIA VENDOR TO VENDOR
        $user->clients()->attach($client->id);

        return ;
    }
    
    public function render()
    {
        return view('livewire.clients.form');
    }
}

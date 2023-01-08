<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Client;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UsersForm extends Component
{
    use AuthorizesRequests;

    public User $user;
    public $user_cell = NULL;
    public $model = NULL;
    public $user_form = NULL;
    public $vendor_user_form = NULL;
    public $client_user_form = NULL;

    public $modal_show = NULL;

    protected $listeners = ['newMember', 'removeMember', 'resetModal'];

    protected function rules()
    {
        return [
            'user_cell' => 'required|digits:10',
            'user.cell_phone' => [
                'required',
                'digits:10',
                Rule::unique('users', 'cell_phone')->ignore($this->user->id),
            ],
            'user.first_name' => 'required|min:2',
            'user.last_name' => 'required|min:2',
            'user.email' => [
                'required',
                'email',
                'min:6',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'user.role' => 
                Rule::requiredIf(function(){
                    if($this->model['type'] == 'vendor'){
                        return true;
                    }else{
                        return false;
                    }
                }),
            'user.hourly_rate' => 
                Rule::requiredIf(function(){
                    if($this->model['id'] == 'NEW' && $this->model['type'] == 'vendor'){
                        return false;
                    }elseif($this->model['type'] == 'client'){
                        return false;
                    }else{
                        return true;
                    }
                }),            
        ];
    }

    protected $messages = 
    [
        'user_cell.digits' => 'Phone number must be 10 digits',
    ];

    public function updated($field) 
    {
        // $this->validate();
        $this->validateOnly($field);
        // if($field == 'user.type'){
        //     $this->via_vendor = Vendor::make();
        //     $this->via_vendor->new = TRUE;
        //     //w9 = open vendor form with FULL_NAME as the Business Name DISABLED
        //     $this->via_vendor->business_name = $this->user->full_name;

        //     if($this->user->type == 'W9' || $this->user->type == 'DBA'){
        //         $this->via_vendor->business_name = $this->user->full_name;
        //     }else{
        //         $this->via_vendor->business_name = NULL;
        //     }
            
        //     $this->via_vendor->business_type = $this->user->type;
        // }

        // if($field == 'user_vendor_id'){
        //     if($this->user_vendor_id == "NEW"){
        //         $this->via_vendor->new = TRUE;
        //         $this->via_vendor->business_name = FALSE;
        //     }else{
        //         $user_via_vendor = Vendor::withoutGlobalScopes()->findOrFail($this->user_vendor_id);
        //         $this->via_vendor = $user_via_vendor;   
        //         $this->via_vendor->new = NULL;
        //         $this->user->type = NULL;
        //     }                     
        // }
    }
    
    public function mount()
    {              
        if(isset($this->user)){
            $this->view_text = [
                'card_title' => 'Update User',
                'button_text' => 'Update User',
                'form_submit' => 'update',             
            ];
        }else{
            $this->user = User::make();
            $this->view_text = [
                'card_title' => 'Create User',
                'button_text' => 'Add User',
                'form_submit' => 'store',             
            ];
        }
    }

    public function user_cell()
    {
        $this->user = User::make();
        $this->validateOnly('user_cell');

        $user_exists = User::where('cell_phone', $this->user_cell)->first();
        
        if($user_exists){
            $this->user = $user_exists;
            $this->user->full_name = $user_exists->full_name;
        }else{
            $this->user = User::make();
            $this->user->cell_phone = $this->user_cell;
        }

        if($this->model['type'] == 'vendor'){
            $this->vendor_user_form = TRUE;

            if($this->model['id'] == 'NEW'){
                $this->user->role = 1; //Admin
            }
        }elseif($this->model['type'] == 'client'){
            $this->client_user_form = TRUE;
        }else{
            dd('in user_cell else');
            abort(404);
        }

        $this->resetErrorBag();
        $this->user_form = TRUE;
    }
    
    public function newMember($model)
    {
        //$model[0] = type; (client, vendor)
        //$model[1] = id; (NEW, or existing(numeric))
        $this->model = $model;        

        //creating new Vendor or Client or adding Team Member to existing Vendor or Client
        if($this->model[0] == 'client'){
            $this->model['type'] = $model[0];
            $this->model['id'] = $model[1];
        }elseif($this->model[0] == 'vendor'){
            $this->model['type'] = $model[0];
            $this->model['id'] = $model[1];
        }else{
            dd('in newMember else');
            abort(404);
        }        
                
        $this->modal_show = TRUE;

        return view('livewire.users.form', [
        ]);
    }

    public function removeMember(User $user)
    {
        // 2-7-22 need REMOVAL MODAL to confirm
        dd('in removeMember Livewire/Users/UsersForm');

        $this->modal_show = true;
        return view('livewire.users.show');
    }

    public function resetModal(User $user)
    {
        // Everthing in top pulbic should be reset here
        $this->user_cell = null;
        $this->user_form = null;
    }

    public function store()
    {   
        $this->validate();

        //create New User
        if(!$this->user->id){
            $user = User::create([
                'first_name' => $this->user->first_name,
                'last_name' => $this->user->last_name,
                'cell_phone' => $this->user->cell_phone,
                'email' => $this->user->email
            ]);

        //existing User
        }else{            
            $user = $this->user;
        }

        //create Vendor
        if($this->vendor_user_form){
            //when creating new vendor.?
            if($this->model['id'] == 'NEW'){
                $this->emit('userVendor', $this->user);
            }else{
                $vendor = Vendor::findOrFail($this->model['id']);
                if($vendor->users()->where('user_id', $user->id)->get()->isEmpty()){
                    $user->vendors()->attach(
                        $this->model['id'], [
                            'role_id' => $this->user->role, 
                            'hourly_rate' => $this->user->hourly_rate, 
                            'start_date' => today()->format('Y-m-d')
                        ]
                    );
    
                    $this->modal_show = FALSE;    
                    return redirect(route('vendors.show', $this->model['id']));
                }else{
                    $this->addError('user_exists_on_model', 'User already belongs to Vendor.');
                }
            }
        }elseif($this->client_user_form){
            if($this->model['id'] == 'NEW'){
                $this->emit('userClient', $user->id);
            }else{
                $client = Client::findOrFail($this->model['id']);
                if($client->users()->where('user_id', $user->id)->get()->isEmpty()){
                    $user->clients()->attach($this->model['id']);

                    $this->modal_show = FALSE;
                    return redirect(route('clients.show', $this->model['id']));
                }else{
                    $this->addError('user_exists_on_model', 'User already belongs to Client.');
                }               
            }
        }else{
            dd('in last else of store in UsersForm...log this error');
        }
    }

    public function update()
    {
        dd('in update');
    }

    public function render()
    {
        return view('livewire.users.form');
    }
}

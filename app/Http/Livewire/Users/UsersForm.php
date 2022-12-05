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
                    if($this->model['add_type'] == 'NEW' && $this->model['type'] == 'vendor'){
                        return false;
                    }elseif($this->model['type'] == 'client'){
                        return false;
                        // return ($this->expense->reimbursment == 'Client' || $this->split == true);
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
        $this->validate();
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

        // $this->validateOnly($field);
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

    //SAME AS ClientsForm::user_cell
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

            if($this->model['add_type'] == 'NEW'){
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
        //->getTable()
        $this->model = $model;

        //creating new Vendor or Client
        if($this->model['type'] == 'client'){
            //when creating new Client
            if(isset($model['id'])){
                $this->model['add_type'] = $model['id'];
            }else{
                $this->model['add_type'] = "NEW";
            }
        }elseif($this->model['type'] == 'vendor'){
            //when creating new Vendor
            if(isset($model['id'])){
                $this->model['add_type'] = $model['id'];                
            }else{
                $this->model['add_type'] = "NEW";
                $this->user->role = 1;
            }
        }else{
            dd('in newMember else');
            abort(404);
        }        
        // dd($this->model);
                
        $this->modal_show = true;

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

        if($this->vendor_user_form){
            if($this->model['add_type'] == 'NEW'){
                $this->emit('userVendor', $user->id);
            }else{
                $user->vendors()->attach(
                    $this->model['id'], [
                        'role_id' => $user->role, 
                        'hourly_rate' => $user->hourly_rate, 
                        'start_date' => today()->format('Y-m-d')
                    ]
                );

                return redirect(route('vendors.show', $this->model['id']));
            }
        }elseif($this->client_user_form){
            if($this->model['add_type'] == 'NEW'){
                $this->emit('userClient', $user->id);
            }else{
                $user->clients()->attach($this->model['id']);

                return redirect(route('clients.show', $this->model['id']));
            }
        }else{
            dd('in last else of store in UsersForm...log this error');
        }

        $this->modal_show = false;
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

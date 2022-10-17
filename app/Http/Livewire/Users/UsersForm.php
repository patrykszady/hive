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
    public Vendor $via_vendor;
    public $user_cell = null;
    public $user_form = null;
    public $vendor_user_form = null;
    public $client_user_form = null;

    public $modal_show = null;
    //the 2 below are almost the same
    public $user_add_type = null;
    public $add_type = NULL;
    public $user_vendor_id = NULL;

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
            'user.full_name' => 'nullable',
            'user.email' => [
                'required',
                'email',
                'min:6',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'user.role' => 'required',
            'user.type' => 'required_with:via_vendor.new',
            'user.hourly_rate' => 'required|numeric',

            'via_vendor.business_name' => 'required|min:3',
            'via_vendor.address' => 'required|min:3',
            'via_vendor.address_2' => 'nullable|min:3',
            'via_vendor.state' => 'required|min:2|max:2',
            'via_vendor.city' => 'required|min:3',
            'via_vendor.zip_code' => 'required|digits:5',

            'via_vendor.new' => 'nullable',

            'user_vendor_id' => 'nullable',
        ];
    }

    protected $messages = 
    [
        'user_cell.digits' => 'Phone number must be 10 digits',
        'user.type.required_with' => 'User Type is required.',
    ];

    public function updated($field) 
    {
        if($field == 'user.type'){
            $this->via_vendor = Vendor::make();
            $this->via_vendor->new = TRUE;
            //w9 = open vendor form with FULL_NAME as the Business Name DISABLED
            $this->via_vendor->business_name = $this->user->full_name;

            if($this->user->type == 'W9' || $this->user->type == 'DBA'){
                $this->via_vendor->business_name = $this->user->full_name;
            }else{
                $this->via_vendor->business_name = NULL;
            }
            
            $this->via_vendor->business_type = $this->user->type;
        }

        if($field == 'user_vendor_id'){
            if($this->user_vendor_id == "NEW"){
                $this->via_vendor->new = TRUE;
                $this->via_vendor->business_name = FALSE;
            }else{
                $user_via_vendor = Vendor::withoutGlobalScopes()->findOrFail($this->user_vendor_id);
                $this->via_vendor = $user_via_vendor;   
                $this->via_vendor->new = NULL;
                $this->user->type = NULL;
            }                     
        }

        $this->validateOnly($field);
    }
    
    public function mount()
    {              
        if(isset($this->user)){
            $this->view_text = [
                'card_title' => 'Update User',
                'button_text' => 'Update',
                'form_submit' => 'update',             
            ];
        }else{
            $this->user = User::make();
            $this->via_vendor = Vendor::make();
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
            $this->resetErrorBag();

            if($this->user_add_type->getTable() == 'vendors'){
                $this->user_vendor_id = $this->user_add_type->id;
                $this->user->type = NULL;

                $data = [
                    'user_id' => $user_exists->id,
                    'vendor_id' => $this->user_vendor_id,
                ];
                
                $vendor_id = $this->user_vendor_id;                
                $validator = Validator::make($data, [
                    'user_id' => Rule::unique('user_vendor')->where(function ($query) use ($vendor_id) {
                        $query->where('vendor_id', $vendor_id);
                    }) 
                ]);

                if ($validator->fails()) {
                    $this->addError('user_vendor_validate', $user_exists->first_name . ' already works at ' . $this->user_add_type->business_name . ' and cannot be added.');
                }else{
                    $this->resetErrorBag();
                    $this->vendor_user_form = TRUE;
                    // $this->user_vendor_id = $vendor_id;
                    // $this->user->type = NULL;
                }
            }elseif($this->user_add_type->getTable() == 'clients'){
                $this->client_user_form = TRUE;
            }else{
                dd('shouldnt be here. user_cell $user_exists UsersForm else. Log this.');
            }
        }else{
            $this->user = User::make();
            $this->user->cell_phone = $this->user_cell;

            $this->resetErrorBag();
            $this->user_form = TRUE;

            if($this->user_add_type->getTable() == 'vendors'){
                $this->vendor_user_form = TRUE;
            }elseif($this->user_add_type->getTable() == 'clients'){
                $this->client_user_form = TRUE;
            }
        }

        // repatitive with above?
        //check if vendor or client adding this user .. or if new client or new vendor
        if($this->add_type == 'NEW_VENDOR'){
            $this->user->role = 1;

            $this->resetErrorBag();
            $this->user_form = TRUE;
        }
    }
    
    //$type = vendor or client.
    public function newMember($type, $id)
    {
        //if numeric, adding to existing, if NEW, creating new Vendor or Client
        if($type == 'client'){
            $user_add_type = Client::findOrFail($id);
        }elseif($type == 'vendor'){
            $user_add_type = Vendor::findOrFail($id);
        }
                
        $this->user_add_type = $user_add_type;
        $this->modal_show = true;

        return view('livewire.users.form', [
            'user_add_type' => $user_add_type,
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
        $this->vendor_user_form = null;
        $this->client_user_form = NULL;

        $this->vendor = null;
        $this->add_type = NULL;
        $this->modal_show = false;
        $this->user = User::make();
        $this->via_vendor = Vendor::make();
    }

    public function store()
    {   
        //5-25-22 what about client_user_form validation?
        //5-25-22 user_add_type has this info too..more realiable?
        if($this->vendor_user_form){
            $this->validate();
        }
    
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
            //create vendor for (W9) User and link  (User) to GS via the W9 Vendor.
            //EXISTING VIA VENDOR
            if($this->via_vendor->id){
                $via_vendor = $this->via_vendor;
            
            //NEW VIA VENDOR
            }else{
                $via_vendor = Vendor::create([
                    'business_type' => $this->user->type,
                    'business_name' => $this->via_vendor->business_name,
                    'address' => $this->via_vendor->address,
                    'address_2' => $this->via_vendor->address_2,
                    'city' => $this->via_vendor->city,
                    'state' => $this->via_vendor->state,
                    'zip_code' => $this->via_vendor->zip_code,
                ]);

                //ADD VIA VENDOR TO VENDOR
                $user->vendors()->attach($via_vendor->id);
            }

            //user_add_type = Client modal
            if(isset($this->user_add_type->id)){
                //1/18/2022 if for new user add to vendor, add to client, add user without pivots
                //ONLY attach ROLE to new $vendor with role_id of 1/admin (default on Model)
                //1/18/2022 get get vendor_id from form.
                //1/18/2022 what about re-activation of Team Member?
                
                $user->vendors()->attach($this->user_add_type->id, [
                    'role_id' => $this->user->role,
                    'hourly_rate' => $this->user->hourly_rate,
                    'start_date' => today(),
                    'via_vendor_id' => $via_vendor->id,
                ]);

                $this->user_add_type->vendors()->attach($via_vendor->id);

                $this->modal_show = false;
                //session()->flash('notify-saved'); with amount of new expense and href to go to it route('expenses.show', $expense->id)
            }else{
                //5-25-2022 else if new vendor
                //close modal / emit event
                //return $user/id to VendorCreate component. emit
                $this->emit('userVendor', $user->id);
                $this->modal_show = false;            
            }
        }elseif($this->client_user_form){
            //user_add_type = Client modal
            $user->clients()->attach($this->user_add_type);
            $this->modal_show = false;  
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

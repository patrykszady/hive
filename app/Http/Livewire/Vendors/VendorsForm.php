<?php

namespace App\Http\Livewire\Vendors;

use App\Models\User;
use App\Models\Vendor;

use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VendorsForm extends Component
{
    use AuthorizesRequests;

    public Vendor $vendor;
    
    public $vendors_found = NULL;

    public $address = NULL;
    public $user = NULL;
    public $user_vendors = NULL;
    public $vendor_id = NULL;
    public $user_vendor_id = NULL;
    public $retail = NULL;

    protected $listeners = ['userVendor'];

    protected function rules()
    {
        return [
            'vendor.business_type' => 'required',
            'vendor.business_name' => 'required|min:3',
            'vendor.address' => 'required_unless:vendor.business_type,Retail|nullable|min:4',
            'vendor.address_2' => 'nullable',
            'vendor.city' => 'required_unless:vendor.business_type,Retail|nullable|min:4',
            'vendor.state' => 'required_unless:vendor.business_type,Retail|nullable|min:2|max:2',
            'vendor.zip_code' => 'required_unless:vendor.business_type,Retail|nullable|digits:5',
            'address' => 'nullable',
            'user' => 'nullable',
            'vendors_found' => 'nullable',
            'vendor_id' => 'nullable',
            'user_vendor_id' => 'nullable',
        ];
    }

    // protected $messages = 
    // [

    // ];

    public function updated($field) 
    {
        if($field == 'vendor.business_name'){
            if(strlen($this->vendor->business_name) >= 3){
                $this->vendors_found = 
                    Vendor::withoutGlobalScopes()
                    ->orderBy('business_name', 'DESC')
                    ->where('business_name', 'like', "%{$this->vendor->business_name}%")
                    ->get();

                if($this->vendors_found->isEmpty()){
                    $this->vendors_found = NULL;
                }
            }else{
                $this->vendors_found = NULL;
                $this->user = NULL;
                $this->user_vendors = NULL;
                $this->vendor->business_type = NULL;
                $this->retail = FALSE;
            }
        }

        if($field == 'vendor_id'){
            if($this->vendor_id == 'NEW'){
                $this->vendors_found = "NONE";
            }
        }

        if($field == 'user_vendor_id'){
            if($this->user_vendor_id == 'NEW_USER'){
                $this->address = TRUE;
            }else{
                $this->address = NULL;
            }
        }

        if($field == 'vendor.business_type'){
            if($this->vendor->business_type == 'Sub'){
                $this->user = TRUE;
                $this->retail = FALSE;
            }elseif($this->vendor->business_type == 'Retail'){
                $this->user = NULL;
                $this->retail = TRUE;
            }else{
                $this->user = NULL;
                $this->retail = NULL;
            }
        }

        $this->validateOnly($field);
    }

    public function mount()
    {              
        if(isset($this->vendor)){
            $this->vendor = $this->vendor;
            $this->view_text = [
                'card_title' => 'Update vendor',
                'button_text' => 'Update',
                'form_submit' => 'update',             
            ];
        }else{
            $this->vendor = Vendor::make();
            $this->view_text = [
                'card_title' => 'Create vendor',
                'button_text' => 'Create',
                'form_submit' => 'store',             
            ];
        }
    }

    public function userVendor(User $user)
    {
        $this->user_vendors = $user->vendors;
        $this->user = $user;
        $this->address = TRUE;
    }

    public function store()
    {   
        $this->validate();

        //NEW VENDOR
        $vendor = Vendor::create([
            'business_type' => $this->vendor->business_type,
            'business_name' => $this->vendor->business_name,
            'address' => $this->vendor->address,
            'address_2' => $this->vendor->address_2,
            'city' => $this->vendor->city,
            'state' => $this->vendor->state,
            'zip_code' => $this->vendor->zip_code,
            'business_phone' => $this->vendor->business_phone,
            'business_email' => $this->vendor->business_email,
        ]);

        //Add existing Vendor to the logged-in-vendor
        //add $vendor to currently logged in vendor
        auth()->user()->vendor->vendors()->attach($vendor->id);

        if($vendor->business_type != 'Retail'){
            if(!$this->user->id){
                //create new User
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

            // attach to new $vendor with role_id of 1/admin (default on Model)
            $user->vendors()->attach($vendor->id);
        }

        //session()->flash('notify-saved'); with amount of new expense and href to go to it route('expenses.show', $expense->id)
        return redirect()->route('vendors.show', $vendor->id);
    }

    public function render()
    {
        return view('livewire.vendors.form');
    }
}
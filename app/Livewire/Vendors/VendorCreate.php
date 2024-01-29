<?php

namespace App\Livewire\Vendors;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Client;

use Livewire\Component;
use App\Livewire\Forms\VendorForm;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VendorCreate extends Component
{
    use AuthorizesRequests;

    public VendorForm $form;

    public $view_text = [
        'card_title' => 'Create Vendor',
        'button_text' => 'Create Vendor',
        'form_submit' => 'store',
    ];

    public Vendor $vendor;
    public $user = NULL;
    public $vendor_add_type = NULL;

    public $via_vendor = NULL;

    public $address = NULL;
    public $team_member = '';
    public $user_vendors = NULL;
    public $vendor_id = NULL;
    public $user_vendor_id = NULL;

    public $business_name_text = NULL;
    public $existing_vendors = NULL;
    public $add_vendors_vendor = NULL;

    public $modal_show = FALSE;

    protected $listeners =
        [
            'refreshComponent' => '$refresh',
            'userVendor',
            'addVendorToVendor',
            'newVendor',
            'vendorModal',
            'editVendor',
            'resetModal',
            'viaVendor',
        ];

    public function mount()
    {
        if(isset($this->vendor->id)){
            $this->vendor = $this->vendor;
            $this->vendor_add_type = $this->vendor_id;
            // $this->view_text = [
            //     'card_title' => 'Update Vendor',
            //     'button_text' => 'Update Vendor',
            //     'form_submit' => 'update',
            // ];
        }else{
            $this->vendor = Vendor::make();
            $this->vendor_add_type = 'NEW';
            // $this->view_text = [
            //     'card_title' => 'Create Vendor',
            //     'button_text' => 'Create Vendor',
            //     'form_submit' => 'store',
            // ];
        }
    }

    public function vendorModal($team_member = NULL)
    {
        // $this->form->reset();
        // $this->business_name_text = NULL;
        // $this->vendor = Vendor::make();
        // $this->vendor->business_name = NULL;
        // $this->business_name_text = NULL;
        // $this->modal_show = FALSE;
        // $this->resetModal();
        // dd($this);
        //5-18-2023 to reset modal if was clicked away and not CANCEL was clicked...whyyyyy
        // $this->resetModal();

        // if(is_numeric($team_member)){
        //     $this->team_member = $team_member;

        //     $user_info = [
        //         'id' => $team_member,
        //         'hourly_rate' => 0,
        //         'role' => 1
        //     ];

        //     $this->userVendor($user_info);

        //     $this->vendor->business_name = $this->user->full_name;
        //     $this->business_name_text = $this->vendor->business_name;
        // }else{

        //     //role and hourly here for new vendor?
        //     // $this->team_member = 'index';
        //     // $this->user = User::make();
        // }

        // dd($this->user);
        $this->team_member = 'index';
        $this->modal_show = TRUE;
    }

    public function viaVendor(User $user, $business_name)
    {
        $this->user = $user;
        $this->form->business_name = $business_name;
        $this->business_name_text = $business_name;
        $this->form->business_type = '1099';

        //similar to $this->userVendor($user_info);
        $this->form->user_hourly_rate = 0;
        $this->form->user_role = 1;

        $this->user_vendors = $this->user->vendors;
        $this->address = TRUE;

        $this->via_vendor = TRUE;

        $this->modal_show = TRUE;
    }

    public function editVendor(Vendor $vendor)
    {
        //5-18-2023 to reset modal if was clicked away and not CANCEL was clicked...whyyyyy
        // $this->resetModal();
        $this->vendor = $vendor;

        $this->form->setVendor($this->vendor);
        $this->user = $this->vendor->users()->first();
        $this->business_name_text = $vendor->business_name;

        if($this->vendor->business_type != 'Retail'){
           $this->address = TRUE;
        }

        $this->view_text = [
            'card_title' => 'Update Vendor',
            'button_text' => 'Update',
            'form_submit' => 'edit',
        ];

        $this->modal_show = TRUE;
    }

    public function UpdatedBusinessNameText($value)
    {
        $existing_vendor_ids = auth()->user()->vendor->vendors->pluck('id')->toArray();

        $this->existing_vendors =
            Vendor::withoutGlobalScopes()
                ->orderBy('business_name', 'DESC')
                ->where('business_name', 'like', "%{$this->business_name_text}%")
                ->whereIn('id', $existing_vendor_ids)
                ->get();

        $this->add_vendors_vendor =
            Vendor::withoutGlobalScopes()
                ->orderBy('business_name', 'DESC')
                ->where('business_name', 'like', "%{$this->business_name_text}%")
                ->whereNotIn('id', $existing_vendor_ids)
                ->get();

        $this->form->business_name = $value;
    }

    public function updated($field)
    {
        $this->validateOnly($field);

        if($field == 'vendor.business_type'){
            if(in_array($this->vendor->business_type, ['Sub', '1099', 'DBA'])){
                if(isset($this->user->id)){
                    $this->address = TRUE;
                }
                // $this->user = $this->user;
            // }elseif($this->vendor->business_type == 'Retail'){
            //     $this->user = NULL;
            }elseif($this->vendor->business_type == 'Retail'){
                $this->user = NULL;
                $this->address = NULL;
                $this->user_vendors = NULL;
            }else{
                $this->address = NULL;
            }
        }
    }

    // Everthing in top pulbic should be reset here
    public function resetModal()
    {
        $this->form->reset();
        $this->vendor = Vendor::make();
        // $this->vendor->business_name = NULL;
        $this->business_name_text = NULL;
        $this->modal_show = FALSE;
        $this->user = NULL;
        $this->address = NULL;
        $this->user_vendors = NULL;
        $this->vendor_id = NULL;
        $this->user_vendor_id = NULL;
    }

    public function newVendor()
    {
        // $this->resetModal();
        $this->vendor->business_name = $this->business_name_text;

        // dd($this->vendor->business_name);
        // dd($this->vendor);
        // dd('in new vendor');
        //remove existing and add vendor and top textbox AND open rest of form
    }

    //add Existing Vendor to auth->user->vendor
    public function addVendorToVendor($vendor_id)
    {
        //Add existing Vendor to the logged-in-vendor
        //add $vendor to currently logged in vendor
        auth()->user()->vendor->vendors()->attach($vendor_id);

        // $this->vendor_id = $vendor_id;
        // $this->mount();
        // $this->render();
        $this->modal_show = FALSE;
        // $this->vendor = Vendor::make();
        // $this->resetModal();
        $this->dispatch('refreshComponent')->to('vendors.vendors-index');

        //notification
        $this->dispatch('notify',
            type: 'success',
            content: 'Vendor Added',
            route: 'vendors/' . $vendor_id
        );
    }

    //when Creating NEW Vendor
    public function userVendor($user_info)
    {
        $this->user = User::findOrFail($user_info['id']);
        $this->form->user_hourly_rate = $user_info['hourly_rate'];
        $this->form->user_role = $user_info['role'];

        $this->user_vendors = $this->user->vendors;
        $this->address = TRUE;
    }

    public function edit()
    {
        $vendor = $this->form->update();

        $this->modal_show = FALSE;

        $this->dispatch('refreshComponent')->to('vendors.vendor-details');

        $this->dispatch('notify',
            type: 'success',
            content: 'Vendor Updated',
            route: 'vendors/' . $vendor->id
        );
    }

    public function store()
    {
        $this->validate();

        if(isset($this->vendor->id)){
            dd('if $this->vendor->id');
            //attach vendor to auth->user->vendor (logged in/working vendor)
            $vendor = $this->vendor;
            auth()->user()->vendor->vendors()->attach($vendor);
        }else{
            //NEW VENDOR
            $vendor = Vendor::create([
                'business_type' => $this->form->business_type,
                'business_name' => $this->form->business_name,
                'address' => $this->form->address,
                'address_2' => $this->form->address_2,
                'city' => $this->form->city,
                'state' => $this->form->state,
                'zip_code' => $this->form->zip_code,
                // 'business_phone' => $this->form->business_phone,
                // 'business_email' => $this->form->business_email,
            ]);

            //Add existing Vendor to the logged-in-vendor || add $vendor to currently logged in vendor
            auth()->user()->vendor->vendors()->attach($vendor->id);

            if($vendor->business_type != 'Retail'){
                $user = $this->user;

                // attach to new $vendor with role_id of 1/admin (default on Model)
                $user->vendors()->attach(
                    $vendor->id, [
                        'role_id' => $this->form->user_role, //default on Model table
                        'hourly_rate' => $this->form->user_hourly_rate,
                        'start_date' => today()->format('Y-m-d')
                    ]
                );
            }
        }

        if($this->via_vendor){
            //dispatch back to UserCreate
            $this->dispatch('ViaVendorId', via_vendor_id: $vendor->id)->to('users.user-create');
        }

        //reset component
        $this->modal_show = FALSE;
        $this->dispatch('refreshComponent')->self();
        // $this->resetModal();
        $this->form->reset();
        // $this->dispatch('via', 'vendor')->to('users.users-form');
        $this->dispatch('refreshComponent')->to('vendors.vendors-index');

        $this->dispatch('notify',
            type: 'success',
            content: 'Vendor Added',
            route: 'vendors/' . $vendor->id
        );
    }

    public function render()
    {
        return view('livewire.vendors.form', [
            'view_text' => $this->view_text,
        ]);
    }
}

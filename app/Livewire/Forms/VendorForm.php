<?php

namespace App\Livewire\Forms;

use App\Models\Vendor;

use Livewire\Attributes\Rule;
use Livewire\Form;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VendorForm extends Form
{
    use AuthorizesRequests;

    public ?Vendor $vendor;

    #[Rule('required|min:3')]
    public $business_name = NULL;

    #[Rule('required')]
    public $business_type = NULL;

    #[Rule('required|min:3')]
    public $address = NULL;

    #[Rule('nullable|min:2')]
    public $address_2 = NULL;

    #[Rule('required|min:3')]
    public $city = NULL;

    #[Rule('required|min:2|max:2')]
    public $state = NULL;

    #[Rule('required|digits:5', as: 'zip code')]
    public $zip_code = NULL;

    #[Rule('nullable|email|min:5', as: 'business email')]
    public $business_email = NULL;

    #[Rule('nullable|digits:10', as: 'business phone')]
    public $business_phone = NULL;

    #[Rule('nullable')]
    public $user_hourly_rate = NULL;

    #[Rule('nullable')]
    public $user_role = NULL;

    public function setVendor(Vendor $vendor)
    {
        $this->vendor = $vendor;
        $this->business_name = $this->vendor->business_name;
        $this->business_type = $this->vendor->business_type;
        $this->address = $this->vendor->address;
        $this->address_2 = $this->vendor->address_2;
        $this->city = $this->vendor->city;
        $this->state = $this->vendor->state;
        $this->zip_code = $this->vendor->zip_code;
        $this->business_phone = $this->vendor->business_phone;
        $this->business_email = $this->vendor->business_email;
    }

    public function update()
    {
        $this->authorize('create', Vendor::class);
        $this->validate();

        $this->vendor->update([
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'address' => $this->address,
            'address_2' => $this->address_2,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
        ]);

        return $this->vendor;
    }

    public function store()
    {
        dd('form store');
    }
    // protected function rules()
    // {
    //     return [
    //         'vendor.id' => 'nullable',
    //         'business_name_text' => 'required_without:user.id|nullable|min:3',
    //         'vendor.business_type' => 'required',
    //         'vendor.business_name' => 'required|min:3',
    //         'vendor.address' => 'required_unless:vendor.business_type,Retail|nullable|min:4',
    //         'vendor.address_2' => 'nullable',
    //         'vendor.city' => 'required_unless:vendor.business_type,Retail|nullable|min:4',
    //         'vendor.state' => 'required_unless:vendor.business_type,Retail|nullable|min:2|max:2',
    //         'vendor.zip_code' => 'required_unless:vendor.business_type,Retail|nullable|digits:5',
    //         'address' => 'nullable',
    //         'user' => 'nullable',
    //         'existing_vendors' => 'nullable',
    //         'add_vendors_vendor' => 'nullable',
    //         'vendor_id' => 'nullable',
    //         'user_vendor_id' => 'nullable',
    //     ];
    // }

    // protected $messages =
    // [
    //     'business_name_text' => 'Business Name must be at least 3 characters.'
    // ];
}

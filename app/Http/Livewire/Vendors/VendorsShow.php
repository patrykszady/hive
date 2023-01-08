<?php

namespace App\Http\Livewire\Vendors;

use App\Models\Vendor;
use App\Models\User;
use Livewire\Component;

class VendorsShow extends Component
{
    public Vendor $vendor;

    // protected $listeners = ['userVendor'];

    public function mount()
    {
        $this->users = $this->vendor->users()->where('is_employed', 1)->get();

        $this->vendor_add_type = $this->vendor->id;
    }

    // public function userVendor(User $user)
    // {
    //     $this->user_vendors = $user->vendors;
    //     $this->user = $user;
    //     // $this->users = $this->vendor->users()->where('is_employed', 1)->get();
    //     $this->address = TRUE;
    // }

    public function render()
    {
        return view('livewire.vendors.show', [
        ]);
    }
}
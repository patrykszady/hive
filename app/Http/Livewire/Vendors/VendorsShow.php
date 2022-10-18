<?php

namespace App\Http\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;

class VendorsShow extends Component
{
    public Vendor $vendor;
    
    public function render()
    {
        return view('livewire.vendors.show', [
            'users' => $this->vendor->users()->where('is_employed', 1)->get(),
        ]);
    }
}
<?php

namespace App\Http\Livewire\Entry;

use App\Models\User;
use App\Models\Vendor;
use Livewire\Component;

class VendorSelection extends Component
{
    public $vendor_id = '';
    public $vendor_name = '';
    public $roles = ['0' => 'Not Associated', '1' => 'Owner', '2' => 'Team Member'];

    public function mount()
    {
        $user = auth()->user();
      
        $this->vendor_id = isset($user->primary_vendor_id) ? $user->vendor->id : '';

        $this->vendor_name = isset($user->primary_vendor_id) ? $user->vendors()->withoutGlobalScopes()->find($this->vendor_id)->business_name : 'Should Never See This';
    }

    public function updatedVendorId() 
    {
        $user = auth()->user();
        
        $this->vendor_name = isset($this->vendor_id) ? $user->vendors()->withoutGlobalScopes()->find($this->vendor_id)->business_name : 'Should Never See This';
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.entry.vendor-selection', [
            'vendors' => $user->vendors()->withoutGlobalScopes()->get(),
        ]);
    }

    //public function save() = change primary_vendor_id on User::id
    public function save()
    {
        $user = auth()->user();
        $user->primary_vendor_id = $this->vendor_id;
        $user->save();

        return redirect()->route('dashboard');
    }
}

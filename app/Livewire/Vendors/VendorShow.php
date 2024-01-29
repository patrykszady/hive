<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;

use Livewire\Component;
use Livewire\Attributes\Title;

class VendorShow extends Component
{
    public Vendor $vendor;
    public $users = [];
    public $vendor_docs = [];

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        //1/4/24 move to another onion layer ... middleware? gates?
        if($this->vendor->id == auth()->user()->vendor->id){
            return redirect(route('dashboard'));
        }

        $this->vendor_docs = $this->vendor->vendor_docs()->orderBy('expiration_date', 'DESC')->with('agent')->get()->groupBy('type')->toBase();

        foreach($this->vendor_docs as $type_certificates)
        {
            if($type_certificates->first()->expiration_date <= today()){
                $this->vendor->expired_docs = TRUE;
            }
        }

        $this->users = $this->vendor->users()->where('is_employed', 1)->get();
    }

    #[Title('Vendor')]
    public function render()
    {
        return view('livewire.vendors.show');
    }
}

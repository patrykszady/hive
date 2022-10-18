<?php

namespace App\Http\Livewire\Vendors;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Models\Vendor;

class VendorsIndex extends Component
{
    use WithPagination, AuthorizesRequests;
    
    public $business_name = '';
    public $vendor_type = '';

    protected $queryString = [
        'business_name' => ['except' => ''],
        'vendor_type' => ['except' => '']
    ];

    public function updating($field)
    {
        $this->resetPage();
    }

    public function render()
    {
        $vendors = Vendor::orderBy('created_at', 'DESC')
            ->where('business_name', 'like', "%{$this->business_name}%")
            ->where('business_type', 'like', "%{$this->vendor_type}%")
            ->paginate(10);
        
        return view('livewire.vendors.index', [
            'vendors' => $vendors,
        ]);
    }
}

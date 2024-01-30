<?php

namespace App\Livewire\Vendors;

use Livewire\Component;
use Livewire\Attributes\Title;

use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Models\Vendor;

class VendorsIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    public $business_name = '';
    public $vendor_type = '';
    public $view;

    protected $queryString = [
        'business_name' => ['except' => ''],
        'vendor_type' => ['except' => '']
    ];

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function updating($field)
    {
        $this->resetPage();
    }

    #[Title('Vendors')]
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

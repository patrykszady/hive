<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;

class VendorSheetsTypeIndex extends Component
{
    public $vendors = [];

    protected function rules()
    {
        return [
            'vendors.*.sheets_type' => 'nullable',
        ];
    }

    public function mount()
    {
        $this->vendors = Vendor::where('business_type', 'Retail')->orderBy('created_at', 'DESC')->get();
    }

    public function updatedVendors($value, $key)
    {
        $index = substr($key, 0, strpos($key, "."));
        $vendor = $this->vendors[$index];
        $vendor->sheets_type = $value == "" ? NULL : $value;
        $vendor->save();

        $this->dispatch('notify',
            type: 'success',
            content: $vendor->name . ' Changed'
        );
    }

    public function render()
    {
        return view('livewire.vendors.sheets-type-index');
    }
}

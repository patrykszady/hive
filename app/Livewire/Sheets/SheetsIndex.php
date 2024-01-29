<?php

namespace App\Livewire\Sheets;

use App\Models\Check;
use App\Models\Payment;

use Livewire\Component;
use Livewire\Attributes\Title;

class SheetsIndex extends Component
{
    public $year = 2024;
    public $cost_of_labor = 0;
    public $revenue = 0;

    protected $queryString = [
        'year' => ['except' => ''],
    ];

    public function mount()
    {
        $vendor_admins = auth()->user()->vendor->users()->employed()->wherePivot('role_id', 1)->pluck('user_id')->toArray();
        // dd($vendor_admins);
        //1-22-24 do not show CASH when preparing TAXES
        //->whereDoesntHave('transaction')
        //->whereNotNull('vendor_id')->where('vendor_id', '!=', auth()->user()->vendor->id)
        // ->whereNotIn('user_id', $vendor_admins)
        // ->whereNull('user_id')->orWhereNotIn('user_id', $vendor_admins)
        //>where('vendor_id', '!=', 1)
        // ::whereYear('date', 2023)->where('vendor_id', '!=', auth()->user()->vendor->id)->orWhereNotIn('user_id', $vendor_admins)->pluck('user_id');

        $this->cost_of_labor =
            Check::
                whereYear('date', $this->year)
                ->where(function($query) use($vendor_admins){
                    $query->whereNotIn('user_id', $vendor_admins)->orWhere('user_id', NULL);
                })
                ->sum('amount');

        $this->revenue = (float) Payment::whereYear('date', $this->year)->sum('amount');
        // dd($this->cost_of_labor);

        // dd($payments->sum('amount') - $cost_of_labor->sum('amount'));
    }

    #[Title('Sheets')]
    public function render()
    {
        return view('livewire.sheets.index');
    }
}

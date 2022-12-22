<?php

namespace App\Http\Livewire\Distributions;

use App\Models\Distribution;
use App\Models\Vendor;
use App\Models\Expenses;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DistributionsShow extends Component
{
    use AuthorizesRequests, WithPagination;

    public Distribution $distribution;

    public function render()
    {
        // dd($this->distribution->expenses->sum('amount') + $this->distribution->splits->sum('amount'));
        //group expenses by vendor where this Distribution, then sum those vendor expenses
        //each group/vendor sum all expenses

        $distribution_expenses_vendors = 
            $this->distribution->expenses()->with(['vendor'])
                ->whereYear('date', today('Y'))
                ->get();

        $distribution_splits_vendors =
            $this->distribution->splits()->with(['expense'])
                ->join('expenses', 'expenses.id', '=', 'expense_splits.expense_id')
                ->whereYear('date', today('Y'))
                ->get(['expense_splits.*', 'expenses.date', 'expenses.vendor_id']);

        $distribution_vendors = 
            $distribution_expenses_vendors->merge($distribution_splits_vendors)
                ->groupBy('vendor_id');
        
        $distribution_get_vendors = Vendor::whereIn('id', $distribution_vendors->keys())->get();

        $distribution_vendors = 
            $distribution_expenses_vendors->merge($distribution_splits_vendors)
                ->groupBy('vendor_id')
                ->each(function ($item, $key) use ($distribution_get_vendors) {
                    $item->sum = $item->sum('amount');
                    $item->vendor = $distribution_get_vendors->where('id', $key)->first();
                })
                ->sortByDesc('sum');

        $distribution_sum = 0;
        foreach($distribution_expenses_vendors as $distribution_vendor)
        {
            $distribution_sum += $distribution_vendor->sum;
            $distribution_expenses_vendors->vendors_sum = $distribution_sum;
        }

        $distribution_projects = 
            $this->distribution->projects()
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

        
        $this->distribution->paid = $distribution_vendors->sum('sum');

        return view('livewire.distributions.show', [
            'distribution_vendors' => $distribution_vendors,
            'distribution_projects' => $distribution_projects,
        ]);
    }
}

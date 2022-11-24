<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Expense;
use App\Models\Project;

use Livewire\Component;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpensesShow extends Component
{   
    use AuthorizesRequests;

    public Expense $expense;
 
    public function render()
    {
        $this->authorize('view', $this->expense);

        $receipt = $this->expense->receipts()->latest()->first();
        $splits = $this->expense->splits()->with('project')->get();
    
        return view('livewire.expenses.show', [
            'expense' => $this->expense,
            'receipt' => $receipt,
            'splits' => $splits,
        ]);
    }
}

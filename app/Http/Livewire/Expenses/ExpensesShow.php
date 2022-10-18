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

        $receipts = $this->expense->receipts()->latest()->get();
        $splits = $this->expense->splits()->with('project')->get();
    
        return view('livewire.expenses.show', [
            'expense' => $this->expense,
            'receipts' => $receipts,
            'splits' => $splits,
        ]);
    }
}

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

    protected $listeners = ['refreshComponent' => '$refresh'];
 
    public function mount()
    {
        $this->authorize('view', $this->expense);

        $this->receipt = $this->expense->receipts()->latest()->first();
        $this->splits = $this->expense->splits()->with('project')->get();
    }

    public function render()
    {    
        return view('livewire.expenses.show', [
        ]);
    }
}
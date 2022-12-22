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

    // protected $listeners = ['showExpense'];
 
    // public function showExpense()
    // {
    //     dd('in showExpense');
    //     $this->emit('showExpense');
    //     // dd($expense);
    //     //emit showExpense expenses.show

    //     //open expense.show modal
    // }

    public function render()
    {
        $this->authorize('view', $this->expense);

        $receipt = $this->expense->receipts()->latest()->first();
        $splits = $this->expense->splits()->with('project')->get();
    
        return view('livewire.expenses.show', [
            'receipt' => $receipt,
            'splits' => $splits,
        ]);
    }
}

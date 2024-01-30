<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Models\Project;
use App\Models\User;

use Livewire\Component;
use Livewire\Attributes\Title;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseShow extends Component
{
    use AuthorizesRequests;

    public Expense $expense;

    protected $listeners = ['refreshComponent' => '$refresh'];

    #[Title('Expense')]
    public function render()
    {
        $this->authorize('view', $this->expense);

        $splits = $this->expense->splits()->get();

        return view('livewire.expenses.show', [
            'receipt' => $this->expense->receipts()->latest()->first(),
            'splits' => $splits,
        ]);
    }
}

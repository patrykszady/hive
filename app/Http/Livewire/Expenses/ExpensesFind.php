<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Expense;
use App\Models\ExpenseSplits;
use App\Models\Transaction;

use Livewire\Component;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpensesFind extends Component
{
    use AuthorizesRequests;

    public $amount = NULL;
    public $expenses_found = NULL;
    public $transactions_found = NULL;
    public $found = NULL;

    // protected $listeners = ['createExpenseFromTransaction'];

    protected function rules()
    {
        return [
            'amount' => 'required|numeric|regex:/^-?\d+(\.\d{1,2})?$/',
        ];
    }

    public function mount()
    {      
        $this->view_text = [
            'card_title' => 'Search Amount',
            'button_text' => 'Search Amount',
            'form_submit' => 'find_amount',             
        ];
    }

    public function find_amount()
    {
        // 2-4-2022 ..account for splits and transactions same as ExpenseIndex render/search method
        $this->expenses_found = 
            Expense::
                orderBy('date', 'DESC')
                ->with(['project', 'vendor', 'splits'])
                ->where('amount', 'like', "{$this->amount}%")
                ->get();

        $this->transactions_found = 
            Transaction::
                orderBy('transaction_date', 'DESC')
                ->where('amount', 'like', "{$this->amount}%")
                // ->whereNotNull('vendor_id')
                ->whereNull('expense_id')
                ->whereNull('check_id')
                ->whereNull('deposit')
                ->get();

        if($this->expenses_found->isEmpty()){
            $this->expenses_found = NULL;
        }else{
            $this->found = TRUE;
        }

        if($this->transactions_found->isEmpty()){
            $this->transactions_found = NULL;
        }else{
            $this->found = TRUE;
        }
    }

    public function render()
    {
        return view('livewire.expenses.find');
    }
}

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
    public $expense_form = NULL;

    protected $listeners = ['newExpense'];

    protected function rules()
    {
        return [
            'amount' => 'numeric|regex:/^-?\d+(\.\d{1,2})?$/',
        ];
    }

    protected $messages = 
    [
        'amount' => 'Amount format is incorrect. Format is 2145.36. No commas and only two digits after decimal allowed. If amount is under $1.00, use 0.XX',
    ];
   
    public function mount()
    {      
        $this->view_text = [
            'card_title' => 'Search Amount',
            'button_text' => 'Search Amount',
            'form_submit' => 'find_amount',             
        ];
    }

    public function updated($field) 
    {
        $this->found = NULL;
        $this->emit('createNewExpense', NULL, $this->amount);
        $this->validateOnly($field);
        
    }

    public function find_amount()
    {
        $this->emit('createNewExpense', NULL, $this->amount);

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
        
        $this->found = TRUE;

        if($this->expenses_found->isEmpty()){
            $this->expenses_found = NULL;
        }

        if($this->transactions_found->isEmpty()){
            $this->transactions_found = NULL;
        }
    }

    public function newExpense($id = NULL, $id_type = NULL)
    {
        if($id_type == 'transaction'){
            $this->emit('createExpenseFromTransaction', $id, $this->amount);
        }elseif($id_type == 'reset_form'){
            $this->found = FALSE;
            $this->expense_form = FALSE;
            $this->found = FALSE;
            $this->amount = NULL;
        }elseif($id_type == 'expense'){
            $this->emit('editExpense', TRUE, $id);
        }else{
            $this->emit('createNewExpense', TRUE, $this->amount);
        }

        if($id_type != 'reset_form'){
            $this->found = FALSE;
            $this->expense_form = TRUE; 
        }               
    }

    public function render()
    {
        $this->authorize('create', Expense::class);
        return view('livewire.expenses.find');
    }
}

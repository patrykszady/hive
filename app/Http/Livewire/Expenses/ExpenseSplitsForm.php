<?php

namespace App\Http\Livewire\Expenses;

use Livewire\Component;

use App\Models\Expense;
use App\Models\Distribution;
use App\Models\ExpenseSplits;
use App\Models\Project;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseSplitsForm extends Component
{
    use AuthorizesRequests;
    
    //keep track of expense_splits.*.amount sum 
    public $expense_splits = [];
    public $splits_count = 0;
    public $modal_show = NULL;
    public $expense_total = 0;

    protected $listeners = ['addSplits', 'addSplit', 'removeSplit', 'resetSplits'];

    protected function rules()
    {
        return [
            'expense_splits.*.amount' => 'required|numeric|regex:/^-?\d+(\.\d{1,2})?$/',
            'expense_splits.*.project_id' => 'required',
            'expense_splits.*.reimbursment' => 'nullable',
            'expense_splits.*.note' => 'nullable',
        ];
    }

    protected $messages = 
    [
        'expense_splits.*.amount.regex' => 'Amount format is incorrect. Format is 2145.36. No commas and only two digits after decimal allowed. If amount is under $1.00, use 0.XX',
        'expense_splits.*.amount.required_if' => 'The split amount field is required.',
        'expense_splits.*.amount.numeric' => 'The amount field must be numberic.',
    ];

    public function updated($field) 
    {
        // $this->validate();
        $this->validateOnly($field);
    }

    public function mount()
    {                   
        $this->projects = Project::orderBy('created_at', 'DESC')->get();
        $this->distributions = Distribution::all();
        $this->view_text = [
            'button_text' => 'Save Splits',
            'form_submit' => 'split_store',             
        ];
    }

    public function getSplitsSumProperty()
    {
        $splits_total = collect($this->expense_splits)->where('amount', '!=', '')->sum('amount');
        //expense_total amount - $splits_total MUST = 0.00
        return $this->expense_total - $splits_total;
    }

    public function split_store()
    {   
        $this->validate();
        if($this->splits_sum != 0){
            $this->addError('expense_splits_total_match', 'Expense Amount and Splits Amounts must match');
        }else{
            // //save without expense_id..send split_ids to ExpenseForm
            // //send all SPLITS data back to ExpenseForm view!!!
            // //send back to ExpenseForm... all validated and tested here
            $this->emit('hasSplits', $this->expense_splits);
            $this->modal_show = NULL;
        }
    }

    public function addSplits($expense_total)
    {
        $this->expense_total = $expense_total;
        
        //if splits isset / comign from Expense.Update form.. otherwire 
        if(empty($this->expense_splits)){
            $this->expense_splits = [];
            array_push($this->expense_splits, $this->splits_count++);
            array_push($this->expense_splits, $this->splits_count++);
        }else{
            $this->splits_count = count($this->expense_splits) - 1;
        }
        
        $this->modal_show = TRUE;
    }

    public function addSplit()
    {
        // $this->splits_count = $this->splits_count + 1;
        // // $count = $this->splits_count;
        // dd($this->splits_count);
        // $this->expense_splits->put($this->splits_count++, array());
        // $this->expense_splits->push(ExpenseSplits::make());
       
        // dd($this->expense_splits->all());
        if(!is_array($this->expense_splits)){
            $this->expense_splits = $this->expense_splits->toArray();
        }
        $this->splits_count = $this->splits_count + 1;
        array_push($this->expense_splits, $this->splits_count);
    }

    public function removeSplit($index)
    {
        unset($this->expense_splits[$index]);
    }

    public function resetSplits()
    {
        $this->splits_count = 0;
        $this->expense_splits = [];
    }

    public function render()
    {
        return view('livewire.expenses.splits-form');
    }
}

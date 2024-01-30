<?php

namespace App\Livewire\Expenses;

use Livewire\Component;

use App\Models\Project;
use App\Models\ExpenseSplits;

use App\Livewire\Forms\ExpenseSplitForm;

class ExpenseSplitsCreate extends Component
{
    public ExpenseSplitForm $form;
    //keep track of expense_splits.*.amount sum
    public $expense_splits = [];
    public $splits_count = 0;
    public $splits_total = 0;
    public $expense_total = 0;

    public $projects;
    public $distributions;

    public $modal_show = FALSE;

    protected $listeners = ['refreshComponent' => '$refresh', 'addSplits', 'addSplit', 'removeSplit', 'resetSplits'];

    public function updated($field, $value)
    {
        $this->validateOnly($field);
    }

    public function getSplitsSumProperty()
    {
        $this->splits_total = collect($this->form->expense_splits)->where('amount', '!=', '')->sum('amount');
        return round($this->expense_total - $this->splits_total, 2);
    }

    public function addSplits($expense_total, $expense)
    {
        // $this->resetSplits();
        $this->expense_total = $expense_total;
        $this->expense_splits = $expense['splits'];

        //if splits isset / comign from Expense.Update form.. otherwire
        if(empty($this->expense_splits)){
            $this->expense_splits = collect();
            $this->expense_splits->push(['amount' => NULL, 'project_id' => NULL]);
            $this->expense_splits->push(['amount' => NULL, 'project_id' => NULL]);
            $this->splits_count = 2;
        }else{
            $this->expense_splits = $this->expense_splits;
            $this->splits_count = count($this->expense_splits) - 1;
        }

        $this->form->setSplits($this->expense_splits);

        $this->getSplitsSumProperty();
        $this->modal_show = TRUE;
    }

    public function addSplit()
    {
        if(!is_array($this->expense_splits)){
            $this->expense_splits = $this->expense_splits->toArray();
        }
        $this->splits_count = $this->splits_count + 1;
        array_push($this->expense_splits, $this->splits_count);
    }

    public function removeSplit($index)
    {
        $this->splits_count = $this->splits_count - 1;

        if(isset($this->expense_splits[$index]['id'])){
            $split_to_remove = ExpenseSplits::findOrFail($this->expense_splits[$index]['id']);
            $split_to_remove->delete();
        }

        unset($this->expense_splits[$index]);
        unset($this->form->expense_splits[$index]);
    }

    public function resetSplits()
    {
        $this->splits_count = 0;
        $this->splits_total = 0;
        $this->expense_splits = [];
        $this->form->reset();

        // if(empty($this->expense_splits)){
        //     $this->splits_count = 0;
        //     $this->expense_splits = [];
        // }
    }

    public function split_store()
    {
        // $splits = $this->form->store();

        // dd($splits);
        // dd($this);

        if(round($this->expense_total - $this->splits_total, 2) != 0.0){
            $this->addError('expense_splits_total_match', 'Expense Amount and Splits Amounts must match');
        }else{
            //send all SPLITS data back to ExpenseForm view
            //send back to ExpenseForm... all validated and tested here
            $this->dispatch('hasSplits', $this->form->expense_splits)->to(ExpenseCreate::class);
            $this->modal_show = FALSE;
            $this->expense_splits = NULL;
            // $this->resetSplits();
        }
    }

    public function render()
    {
        // dd('in render ExoeseSokutsCrtete');
        $view_text = [
            'button_text' => 'Save Splits',
            'form_submit' => 'split_store',
        ];

        return view('livewire.expenses.splits-form', [
            'view_text' => $view_text,
        ]);
    }
}

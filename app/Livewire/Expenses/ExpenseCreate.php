<?php

namespace App\Livewire\Expenses;

use App\Models\Project;
use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Check;
use App\Models\Distribution;
use App\Models\Transaction;
use App\Models\BankAccount;
use App\Models\ExpenseSplits;
use App\Models\ExpenseReceipts;
use App\Models\Receipt;

use App\Livewire\Forms\ExpenseForm;

use Livewire\Component;
use Livewire\WithFileUploads;

use Illuminate\Support\Facades\Route;

use Carbon\Carbon;

class ExpenseCreate extends Component
{
    use WithFileUploads;

    public ExpenseForm $form;

    public $view_text = [
        'card_title' => 'Create Expense',
        'button_text' => 'Create',
        'form_submit' => 'save',
    ];

    public $split = FALSE;
    public $splits = FALSE;
    public $expense = NULL;
    public $expense_update = FALSE;
    public $expense_splits = [];

    // public $via_vendor_employees = NULL;

    public $modal_show = FALSE;

    protected $listeners = ['resetModal', 'editExpense', 'newExpense', 'createExpenseFromTransaction', 'hasSplits'];

    public function updated($field, $value)
    {
        // if SPLIT checked vs if unchecked
        if($field == 'split'){
            if($this->split == TRUE){
                $this->form->project_id = NULL;
            }
        }

        if($field == 'form.reimbursment'){
            // dd($value);
            // if($value == NULL){
            //     $this->form->reimbursment = NULL;
            // }elseif($value == 'client_reimbursement'){
            //     // dd('Client');
            //     $this->form->reimbursment = 'client_reimbursement';
            // }
            // $title = Project::findOrFail($this->form->project_id)->project_status->title;

            // if($title == 'Complete' && $this->form->reimbursment == 'Client'){
            //     $this->addError('form.reimbursment', 'No Client reimbursment allowed when Project is Complete.');
            // }
            // $this->validate();
            $this->validateOnly('form.receipt_file');
        }

        if($field == 'form.project_id' && is_numeric($value)){
            $project_title = Project::findOrFail($value)->project_status->title;
            if($project_title == 'Complete'){
                $this->form->project_completed = TRUE;
            }else{
                $this->form->project_completed = FALSE;
            }
        }else{
            $this->form->project_completed = FALSE;
        }

        $this->validateOnly($field);
    }

    //$saved_splits
    public function hasSplits($saved_splits)
    {
        $this->expense_splits = $saved_splits;

        $this->splits = TRUE;
        $this->split = TRUE;
    }

    public function newExpense($amount)
    {
        $this->resetModal();
        $this->form->amount = $amount;
        $this->view_text = [
            'card_title' => 'Create Expense',
            'button_text' => 'Create',
            'form_submit' => 'save',
        ];

        $this->modal_show = TRUE;
    }

    public function editExpense(Expense $expense)
    {
        $this->resetModal();

        $this->expense = $expense;
        $this->expense_update = TRUE;

        if(!$expense->splits->isEmpty()){
            $this->hasSplits($expense->splits);
        }

        $this->form->setExpense($expense);

        $this->view_text = [
            'card_title' => 'Update Expense',
            'button_text' => 'Update',
            'form_submit' => 'edit',
        ];

        $this->modal_show = TRUE;
    }

    public function resetModal()
    {
        $this->form->reset();
        $this->split = FALSE;
        $this->splits = FALSE;
        $this->expense_splits = [];
        $this->expense_update = FALSE;
        // Public functions should be reset here
        // $this->dispatch('resetSplits')->to('expenses.expenses-splits-form');
        // $this->dispatch('refreshComponent')->to('expenses.expenses-splits-form');
        // $this->dispatch('resetSplits');
        // $this->modal_show = FALSE;

        // $this->transaction = NULL;

        // $this->check_input = FALSE;
        // $this->check_input_existing = FALSE;
        // $this->check = Check::make();
        // $this->resetValidation();
    }

    public function createExpenseFromTransaction(Transaction $transaction)
    {
        $this->resetModal();
        // {
            //6-14-2022 this only works for Retail vendors.. really need a Modal from MatchVendor or CreateNewVendor forms and taken back here
            //create Retail vendor here if doesnt exist yet
            // if(is_null($transaction->vendor_id)){
            //     $vendor = Vendor::create([
            //         'business_type' => 'Retail',
            //         'business_name' => $transaction->plaid_merchant_name,
            //     ]);

            //     $vendor_id = $vendor->id;

            //     //USED IN MULTIPLE OF PLACES TransactionController@add_vendor_to_transactions, MatchVendor@store
            //     //add if vendor is not part of the currently logged in vendor
            //     if(!$transaction->bank_account->vendor->vendors->contains($vendor_id)){
            //         $transaction->bank_account->vendor->vendors()->attach($vendor_id);
            //     }

            //     //add this vendor to the existing $this->vendors collection
            //     $this->vendors->add($vendor);

            //     //6-8-2022 run in a queue?
            //     app('App\Http\Controllers\TransactionController')->add_vendor_to_transactions();
            // }else{
            //     $vendor_id = $transaction->vendor_id;
            // }
        // }

        // $this->expense_splits = [];

        //2/18/2023 if check_number .. expense->vendor_id = GS Construction / logged in vendor?
        if($transaction->check_number){
            // $this->check_input_existing = TRUE;
            if($transaction->check_number == '1010101'){
                $check_type = 'Transfer';
            }elseif($transaction->check_number == '2020202'){
                $check_type = 'Cash';
            }else{
                $check_type = 'Check';
            }

            $this->form->bank_account_id = $transaction->bank_account_id;
            $this->form->check_type = $check_type;
            $this->check_input = TRUE;

            //2/18/2023 dont allow changes to $this->check if coming from a transaction...
            if($check_type == 'Check'){
                $this->form->check_number = $transaction->check_number;
                // $this->check_number = TRUE;
            }
            // else{
            //     // $this->check_number = NULL;
            //     $this->check_input = NULL;
            // }
        }

        $this->form->transaction = $transaction;

        $this->view_text = [
            'card_title' => 'Create Expense from Transaction',
            'button_text' => 'Create',
            'form_submit' => 'save',
        ];

        $this->form->amount = $transaction->amount;
        $this->form->date = $transaction->transaction_date->format('Y-m-d');

        if(is_null($transaction->vendor_id)){
            $this->form->vendor_id = NULL;
        }else{
            $this->form->vendor_id = $transaction->vendor_id;
        }

        $this->modal_show = TRUE;
    }

    public function edit()
    {
        $expense = $this->form->update();

        $this->modal_show = FALSE;
        $this->resetModal();

        $this->dispatch('refreshComponent')->to('expenses.expense-show');
        $this->dispatch('refreshComponent')->to('expenses.expense-index');

        $this->dispatch('notify',
            type: 'success',
            content: 'Expense Updated',
            route: 'expenses/' . $expense->id
        );
    }

    public function remove()
    {
        // if($this->expense->amount == 0.00){
            $expense = $this->form->delete();

            $url = url()->previous();
            $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();

            if($route == 'expenses.show'){
                session()->flash('notify', ['success', 'Expense Deleted']);

                $this->redirect(ExpenseIndex::class);
            }else{
                $this->dispatch('refreshComponent')->to('expenses.expense-index');

                $this->dispatch('notify',
                    type: 'success',
                    content: 'Expense Deleted'
                );
            }
        // }
    }

    public function save()
    {
        // return $this->dispatch('validateCheck')->to(CheckCreate::class);
        $expense = $this->form->store();

        $this->modal_show = FALSE;

        // if(isset($this->transaction)){
        //     if(!$this->transaction->vendor_id){
        //         $this->transaction->vendor_id = $expense->vendor_id;
        //     }
        //     $this->transaction->expense_id = $expense->id;
        //     $this->transaction->check_id = $check_id;
        //     $this->transaction->save();
        // }

        // if($this->receipt_file){
        //     $this->upload_receipt_file($expense->amount, $expense->id);
        // }


        $this->resetModal();

        // $this->dispatch('resetSplits')->to('expenses.expenses-splits-form');
        // $this->dispatch('resetSplits');

        //emit and refresh so expenses-new-form removes/refreshes
        //1-13-2023 coming from different components expenses-show, expenses-index....
        $this->dispatch('refreshComponent')->to('expenses.expense-show');
        $this->dispatch('refreshComponent')->to('expenses.expense-index');

        $this->dispatch('notify',
            type: 'success',
            content: 'Expense Created',
            route: 'expenses/' . $expense->id
        );
    }

    public function render()
    {
        $this->authorize('create', Expense::class);

        $vendors = Vendor::orderBy('business_name')->get(['id', 'business_name']);
        $projects =
            Project::
                // where('created_at', '>', Carbon::now()->subYears(4)->format('Y-m-d'))
                orderBy('created_at', 'DESC')
                // ->whereHas('project_status', function ($query) {
                //     $query->whereIn('project_status.title', ['Active', 'Complete']);
                // })
                ->get();
        $distributions = Distribution::all(['id', 'name']);
        $team_members = auth()->user()->vendor->users()->employed();
        $employees = $team_members->get();
        $via_vendor_employees = $team_members->wherePivotNotNull('via_vendor_id')->get();

        //->whereNot('users.id', auth()->user()->id)
        $employees = auth()->user()->vendor->users()->where('is_employed', 1)->get();

        $bank_accounts =
            BankAccount::with('bank')->where('type', 'Checking')
                ->whereHas('bank', function ($query) {
                    return $query->whereNotNull('plaid_access_token');
                })->get();

        return view('livewire.expenses.form', [
            'vendors' => $vendors,
            'projects' => $projects,
            'distributions' => $distributions,
            'employees' => $employees,
            'via_vendor_employees' => $via_vendor_employees,
            'employees' => $employees,
            'bank_accounts' => $bank_accounts,
            'employees' => $employees,
        ]);
    }
}


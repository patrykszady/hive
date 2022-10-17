<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Check;
use App\Models\Distribution;
use App\Models\Expense;
use App\Models\ExpenseSplits;
use App\Models\ExpenseReceipts;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Vendor;

use Livewire\WithFileUploads;
use Livewire\Component;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpensesForm extends Component
{
    use AuthorizesRequests, WithFileUploads;
    
    public Expense $expense;

    public $transaction = NULL;
    public $check = NULL;
    public $check_input = FALSE;
    // public $has_check_indication = NULL;

    public $expenses_found = NULL;
    public $transactions_found = NULL;

    public $check_number = NULL;

    public $bank_account = NULL;
    public $payment_type = NULL;

    public $receipt_file = NULL;

    public $split = NULL;
    public $splits = NULL;
    public $expense_splits = [];

    protected $listeners = ['hasSplits', 'createExpenseFromTransaction'];

    protected function rules()
    {
        return [
            'expense.amount' => 'required|numeric|regex:/^-?\d+(\.\d{1,2})?$/',
            'expense.date' => 'required|date|before_or_equal:today|after:2017-01-01',
            'expense.project_id' => 'required_unless:split,true',
            'expense.vendor_id' => 'required|numeric',
            'expense.reimbursment' => 'nullable',
            'expense.invoice' => 'nullable',
            'expense.note' => 'nullable',
            'expense.paid_by' => 'nullable',
            'receipt_file' => [
                Rule::requiredIf(function(){
                    if($this->expense->receipts()->exists()){
                        return false;
                    }else{
                        return ($this->expense->reimbursment == 'Client' || $this->split == true);
                    }
                }),
                    'nullable',
                    'mimes:jpeg,jpg,png,pdf'
                ],

            'split' => 'nullable',
            'splits' => 'nullable',
            'expenses_found' => 'nullable',
            'transactions_found' => 'nullable',

            //USED in MULTIPLE OF PLACES TimesheetPaymentForm and VendorPaymentForm
            //required_without:check.paid_by
            'check.bank_account_id' => 'nullable',
            'check.check_type' => 'required_with:check.bank_account_id',
            'check.check_number' => 'required_if:check.check_type,Check',  
        ];
    }

    protected $messages = 
    [
        'expense.amount.regex' => 'Amount format is incorrect. Format is 2145.36. No commas and only two digits after decimal allowed. If amount is under $1.00, use 00.XX',
        'expense.project_id.required_unless' => 'Project is required unless Expense is Split.',
        'expense.date.before_or_equal' => 'Date cannot be in the future. Make sure Date is before or equal to today.',
        'expense.date.after' => 'Date cannot be before the year 2017. Make sure Date is after or equal to 01/01/2017.',
        // 'receipt_file.requiredif' => 'Receipt is required if Expense is Reimbursed or has Splits',
    ];

    public function updated($field) 
    {
        if($field == 'expense.amount'){
            if(is_null($this->expense->id)){
                if($this->expense->amount != NULL){
                    // 2-4-2022 ..account for splits and transactions same as ExpenseIndex render/search method
                    $this->expenses_found = 
                        Expense::orderBy('date', 'DESC')
                        ->with(['project', 'vendor', 'splits'])
                        ->where('amount', 'like', "{$this->expense->amount}%")
                        ->get();

                    $this->transactions_found = 
                        Transaction::orderBy('transaction_date', 'DESC')
                        ->where('amount', 'like', "{$this->expense->amount}%")
                        // ->whereNotNull('vendor_id')
                        ->whereNull('expense_id')
                        ->whereNull('check_id')
                        ->whereNull('deposit')
                        ->get();

                    // $this->amounts_found = $expenses_found->merge($transactions_found)->sortBy('updated_at');
                    if($this->expenses_found->isEmpty()){
                        $this->expenses_found = NULL;
                    }

                    if($this->transactions_found->isEmpty()){
                        $this->transactions_found = NULL;
                    }
                }else{
                    $this->expenses_found = NULL;
                    $this->transactions_found = NULL;
                    $this->expense->date = NULL;
                }
            }
        }

        if($field == 'expense.date'){
            $this->expenses_found = NULL;
            $this->transactions_found = NULL;
        }

        // if SPLIT checked vs if unchecked
        if($field == 'split'){
            if($this->split == true){
                // $this->validateOnly('expense.project_id');
                $this->expense->project_id = null;
                $this->expense->reimbursment = null; 
            }else{
                // return redirect(route('expenses.edit', $this->expense));

                if($this->expense_splits){
                    $this->splits = TRUE;
                }else{
                    $this->splits = NULL;
                }

                //remove all splits.
                // $this->expense_splits = [];
                // $this->splits_count == 0;
                // $this->emit('resetSplits');
                // $this->validateOnly('expense.project_id');
            }
        }

        if($field == 'expense.paid_by'){
            $this->check->bank_account_id = NULL;
            $this->check->check_type = NULL;
            $this->check->check_number = NULL;
            $this->check_input = FALSE;
        }

        if($field == 'check.check_type'){
            if($this->check->check_type == 'Check'){
                $this->check_input = TRUE;
            }else{
                $this->check->check_number = NULL;
                $this->check_input = FALSE;
            }
        }

        if($field == 'check.bank_account_id'){
            if($this->check->bank_account_id == NULL){
                $this->check->bank_account_id = NULL;
                $this->check->check_type = NULL;
                $this->check->check_number = NULL;
                $this->check_input = FALSE;
            }
        }

        $this->validateOnly($field);
    }

    public function mount()
    {      
        // 11-10-21 there shouldnt be any view/blade text data in a controller, move to blade, have a placeholder view after the render method
            // $this->expense = isset($this->expense) ? $this->expense : Expense::make();

        $this->vendors = Vendor::orderBy('business_name')->get();
        

        if(isset($this->expense)){
            $this->expense = $this->expense;
            //11-27-21 if $expense->has('receipts') ... HERE

            if($this->expense->distribution){
                $this->expense->project_id = 'D:' . $this->expense->distribution_id;
            }

            if($this->expense->splits()->exists()){
                $this->split = TRUE;
                $this->splits = TRUE;
                $this->expense_splits = $this->expense->splits;

                foreach($this->expense_splits as $split){
                    if($split->distribution){
                        $split->project_id = 'D:' . $split->distribution_id;
                    }
                }
            }

            if($this->expense->check){
                // $this->emit('hasCheck');
                $this->check = $this->expense->check;
                if($this->check->check_number){
                    $this->check_input = TRUE;
                }
                
            }else{
                $this->check = Check::make();
            }
            
            $this->view_text = [
                'card_title' => 'Update Expense',
                'button_text' => 'Update',
                'form_submit' => 'update',             
            ];
        }else{
            $this->check = Check::make();
            $this->expense = Expense::make();
            $this->view_text = [
                'card_title' => 'Create Expense',
                'button_text' => 'Create',
                'form_submit' => 'store',             
            ];
        }
    }

    public function hasSplits($splits)
    {
        $this->expense_splits = $splits;
        $this->splits = TRUE;
    }

    public function createExpenseFromTransaction(Transaction $transaction)
    {
        //6-14-2022 this only works for Retail vendors.. really need a Modal from MatchVendor or CreateNewVendor forms and taken back here
        //create Retail vendor here if doesnt exist yet
        if(is_null($transaction->vendor_id)){
            $vendor = Vendor::create([
                'business_type' => 'Retail',
                'business_name' => $transaction->plaid_merchant_name,
            ]);

            $vendor_id = $vendor->id;

            //USED IN MULTIPLE OF PLACES TransactionController@add_vendor_to_transactions, MatchVendor@store
            //add if vendor is not part of the currently logged in vendor
            if(!$transaction->bank_account->vendor->vendors->contains($vendor_id)){
                $transaction->bank_account->vendor->vendors()->attach($vendor_id);
            }

            //add this vendor to the existing $this->vendors collection
            $this->vendors->add($vendor);
            
            //6-8-2022 run in a queue?
            app('App\Http\Controllers\TransactionController')->add_vendor_to_transactions();
        }else{
            $vendor_id = $transaction->vendor_id;
        }

        $this->transaction = $transaction;
        $this->expense->date = $transaction->transaction_date;
        $this->expense->vendor_id = $vendor_id;
        $this->transactions_found = NULL;
        $this->expenses_found = NULL;
    }

    public function store()
    {   
        $this->validate();
        $this->authorize('create', Expense::class);

        if(is_numeric($this->expense->project_id)){
            $project_id = $this->expense->project_id;
            $vendor_id = $this->expense->vendor_id;
            $distribution_id = NULL;
            $dist_user = NULL;
        }elseif(is_null($this->expense->project_id)){
            $project_id = NULL;
            $distribution_id = NULL;
            $vendor_id = NULL;
            $dist_user = NULL;
        }else{
            $distribution_id = substr($this->expense->project_id, 2);
            $dist_user = Distribution::findOrFail($distribution_id)->user_id;
            $project_id = NULL;
            $vendor_id = NULL;
        }

        // dd($this->expense->paid_by ? $this->expense->paid_by : NULL);

        if($this->check){
            $check = Check::create([
                'check_type' => $this->check->check_type,
                'check_number' => $this->check->check_number,
                'date' => $this->expense->date,
                'bank_account_id' => $this->check->bank_account_id,
                //user_id if expense project = distribution
                'user_id' => $dist_user,
                'vendor_id' => $vendor_id,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'created_by_user_id' => auth()->user()->id,                
            ]);
            
            $check_id = $check->id;
        }else{
            $check_id = NULL;
        }

        $expense = Expense::create([
            'amount' => $this->expense->amount,
            'date' => $this->expense->date,
            'invoice' => $this->expense->invoice,
            'note' => $this->expense->note,
            //if $split true, project_id = NULL || if expense_splits isset/true, project_id by default is NULL as expected.
            'project_id' => $project_id,
            'distribution_id' => $distribution_id,
            'vendor_id' => $this->expense->vendor_id,
            'check_id' => $check_id,
            'paid_by' => $this->expense->paid_by ? $this->expense->paid_by : NULL,
            'reimbursment' => $this->expense->reimbursment,
            'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
            'created_by_user_id' => auth()->user()->id,
        ]);

        if($this->transaction){
            $this->transaction->expense_id = $expense->id;
            $this->transaction->save();
        }

        foreach($this->expense_splits as $expense_split){
            if(is_numeric($expense_split['project_id'])){
                $split_project_id = $expense_split['project_id'];
                $split_distribution_id = NULL;
            }else{
                $split_distribution_id = substr($expense_split['project_id'], 2);
                $split_project_id = NULL;
            }

            $split = ExpenseSplits::create([
                'amount' => $expense_split['amount'],
                'expense_id' => $expense->id,
                'project_id' => $split_project_id,
                'distribution_id' => $split_distribution_id,
                'reimbursment' => isset($expense_split['reimbursment']) ? $expense_split['reimbursment'] : null,
                'note' => isset($expense_split['note']) ? $expense_split['note'] : null,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'created_by_user_id' => auth()->user()->id,
            ]);
        }

        if($this->receipt_file){
            $filename = $expense->id . '_' . date('Y-m-d-H-i-s') . '.' . $this->receipt_file->getClientOriginalExtension();

            $this->receipt_file->storeAs('receipts', $filename, 'files');

            $ocr_path = 'files/receipts/' . $filename;
            $result = app('App\Http\Controllers\ReceiptController')->ocr_space($ocr_path);

            ExpenseReceipts::create([
                'expense_id' => $expense->id,
                'receipt_filename' => $filename,
                'receipt_html' => $result,
            ]);
            //1/3/2022 Laravel queue $receipt_file HTML... 
        }

        //session()->flash('notify-saved'); with amount of new expense and href to go to it route('expenses.show', $expense->id)
        return redirect()->route('expenses.show', $expense);
    }

    public function update()
    {
        $this->validate();
        $this->authorize('update', $this->expense);

        // dd($this);

        if(is_numeric($this->expense->project_id)){
            $project_id = $this->expense->project_id;
            $distribution_id = NULL;
        }elseif(is_null($this->expense->project_id)){
            $project_id = NULL;
            $distribution_id = NULL;
        }else{
            $distribution_id = substr($this->expense->project_id, 2);
            $project_id = NULL;
        }

        $this->expense->fill($this->expense->getAttributes());
        $this->expense->project_id = $project_id;
        $this->expense->distribution_id = $distribution_id;
        $this->expense->save();

        //if existing expense has a check... update exisitng check. if existing expense DOESNT have a Check but is added.. create a new one

        if($this->check->bank_account_id){
            if($this->expense->check){
                //09-28-2022 edit existing check?
            }else{            
                $check = Check::create([
                    'check_type' => $this->check->check_type,
                    'check_number' => $this->check->check_number,
                    'date' => $this->expense->date,
                    'bank_account_id' => $this->check->bank_account_id,
                    //user_id
                    'vendor_id' => $this->expense->vendor_id,
                    'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                    'created_by_user_id' => auth()->user()->id,                
                ]);    
                
                $this->expense->check_id = $check->id;
                $this->expense->save();
            }
        }else{
            //disassociate
            $this->expense->check->delete();
        }



        // if($this->check){
        //     $check = Check::create([
        //         'check_type' => $this->check->check_type,
        //         'check_number' => $this->check->check_number,
        //         'date' => $this->expense->date,
        //         'bank_account_id' => $this->check->bank_account_id,
        //         //user_id if expense project = distribution
        //         'user_id' => $dist_user,
        //         'vendor_id' => $vendor_id,
        //         'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
        //         'created_by_user_id' => auth()->user()->id,                
        //     ]);
            
        //     $check_id = $check->id;
        // }else{
        //     $check_id = NULL;
        // }




        if($this->split){
            $expense_split_database = collect($this->expense_splits)->pluck('id')->toArray();

            //if $expense->split no longer in $this->expense_splits (removed by enduser in the update form)
            foreach($this->expense->splits as $split){
                if(!in_array($split->id, $expense_split_database)){
                    $split->delete();
                }
            }
    
            //new splits created during Expense Update ($this->expense_splits) that do not have an ID (taken care of above) ELSE update existing $expense->splits
            foreach($this->expense_splits as $expense_split){
                if(is_numeric($expense_split['project_id'])){
                    $split_project_id = $expense_split['project_id'];
                    $split_distribution_id = NULL;
                }else{
                    $split_project_id = NULL;
                    $split_distribution_id = substr($expense_split['project_id'], 2);                    
                }

                if(isset($expense_split['id'])){
                    $expense_split_database = ExpenseSplits::findOrFail($expense_split['id']);
                    $expense_split_database->update([
                        'amount' => $expense_split['amount'],
                        'project_id' => $split_project_id,
                        'distribution_id' => $split_distribution_id,
                        'reimbursment' => isset($expense_split['reimbursment']) ? $expense_split['reimbursment'] : null,
                        'note' => isset($expense_split['note']) ? $expense_split['note'] : null,
                        //12-1-21 really updaed_by_user_id now in update.. can of worms for another time... track ALL Model updates over time..
                        'created_by_user_id' => auth()->user()->id,
                    ]);
                }else{
                    ExpenseSplits::create([
                        'amount' => $expense_split['amount'],
                        'expense_id' => $this->expense->id,
                        'project_id' => $split_project_id,
                        'distribution_id' => $split_distribution_id,
                        'reimbursment' => isset($expense_split['reimbursment']) ? $expense_split['reimbursment'] : null,
                        'note' => isset($expense_split['note']) ? $expense_split['note'] : null,
                        'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                        'created_by_user_id' => auth()->user()->id,
                    ]);
                }
            }
        }else{
            foreach($this->expense->splits as $split){
                $split->delete();
            }
        }

        //new/first or additional receipt files added during Update
        //1/3/2022 DUPLICATE FROM CREATE!
        if($this->receipt_file){
            $filename = $this->expense->id . '_' . date('Y-m-d-H-i-s') . '.' . $this->receipt_file->getClientOriginalExtension();

            $this->receipt_file->storeAs('receipts', $filename, 'files');

            $ocr_path = 'files/receipts/' . $filename;
            $result = app('App\Http\Controllers\ReceiptController')->ocr_space($ocr_path);

            ExpenseReceipts::create([
                'expense_id' => $this->expense->id,
                'receipt_filename' => $filename,
                'receipt_html' => $result,
            ]);
            //1/3/2022 Laravel queue $receipt_file HTML... 
        }

        //session()->flash('notify-saved'); "This expense was updated.. go back to results href with button)
        return redirect()->route('expenses.show', $this->expense);
    }

    public function render()
    {
        //11-10-21 or authorize UPDATE if update method
        $this->authorize('create', Expense::class);
        // $bank_accounts = BankAccount::where('type', 'Checking')->get();
        $bank_accounts = BankAccount::with('bank')->where('type', 'Checking')
            ->whereHas('bank', function ($query) {
                return $query->whereNotNull('plaid_access_token');
            })->get();
            
        $employees = auth()->user()->vendor->users()->where('is_employed', 1)->get();

        return view('livewire.expenses.form', [
            'projects' => Project::orderBy('created_at', 'DESC')->get(),
            'distributions' => Distribution::all(),
            'bank_accounts' => $bank_accounts,
            'employees' => $employees,
        ]);
    } 
}
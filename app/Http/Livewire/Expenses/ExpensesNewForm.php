<?php

namespace App\Http\Livewire\Expenses;

use App\Models\Project;
use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Check;
use App\Models\Distribution;

use Livewire\WithFileUploads;
use Livewire\Component;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpensesNewForm extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public Expense $expense;
    public $split = NULL;
    public $splits = NULL;
    public $expense_splits = [];

    public $modal_show = FALSE;
    // public bool $loadData = TRUE;

    //resetModal
    protected $listeners = ['editExpense', 'resetModal'];

    protected function rules()
    {
        return [
            'expense.amount' => 'required|numeric|regex:/^-?\d+(\.\d{1,2})?$/',
            'expense.date' => 'required|date|before_or_equal:today|after:2017-01-01',
            'expense.project_id' => 'required_unless:split,true',
            'expense.vendor_id' => 'required',
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

            //USED in MULTIPLE OF PLACES TimesheetPaymentForm and VendorPaymentForm
            //required_without:check.paid_by
            'check.bank_account_id' => 'nullable',
            'check.check_type' => 'required_with:check.bank_account_id',
            // 'check.check_number' => 'required_if:check.check_type,Check',  
            'check.check_number' => [
                //ignore if vendor_id of Check is same as request()->vendor_id
                'required_if:check.check_type,Check',
                'nullable',
                'numeric',
                Rule::unique('checks', 'check_number')->where(function ($query) {
                    return $query->where('deleted_at', NULL)->where('bank_account_id', $this->check->bank_account_id)->where('vendor_id', '!=', $this->expense->vendor_id);
                }),         
                //->ignore(request()->get('check_id_id'))
            ],
        ];
    }

    protected $messages = 
    [
        'expense.amount.regex' => 'Amount format is incorrect. Format is 2145.36. No commas and only two digits after decimal allowed. If amount is under $1.00, use 00.XX',
        'expense.project_id.required_unless' => 'Project is required unless Expense is Split.',
        'expense.date.before_or_equal' => 'Date cannot be in the future. Make sure Date is before or equal to today.',
        'expense.date.after' => 'Date cannot be before the year 2017. Make sure Date is after or equal to 01/01/2017.',
        'receipt_file.required_if' => 'Receipt is required if Expense is Reimbursed or has Splits',
    ];

    // public function init()
    // {
    //     $this->loadData = TRUE;
    // }

    public function mount()
    {   
        // $this->vendors = $this->vendors;
        $this->vendors = Vendor::orderBy('business_name')->get(['id', 'business_name']);
        $this->projects = Project::orderBy('created_at', 'DESC')->get(['id', 'project_name']);
        $this->distributions = Distribution::all(['id', 'name']);

        $this->view_text = [
            'card_title' => 'Create Expense',
            'button_text' => 'Create',
            'form_submit' => 'store',             
        ];

        // 11-10-21 there shouldnt be any view/blade text data in a controller, move to blade, have a placeholder view after the render method
        // if(isset($this->expense)){
        //     $this->expense = $this->expense;
        //     //11-27-21 if $expense->has('receipts') ... HERE

        //     $this->view_text = [
        //         'card_title' => 'Update Expense',
        //         'button_text' => 'Update',
        //         'form_submit' => 'update',             
        //     ];
        // }else{
        //     $this->expense = Expense::make();
        //     $this->check = Check::make();
        //     $this->view_text = [
        //         'card_title' => 'Create Expense',
        //         'button_text' => 'Create',
        //         'form_submit' => 'store',             
        //     ];
        // }
    }

    public function updated($field) 
    {
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

        // if($field == 'expense.paid_by'){
        //     $this->check->bank_account_id = NULL;
        //     $this->check->check_type = NULL;
        //     $this->check->check_number = NULL;
        //     $this->check_input = FALSE;
        // }

        // if($field == 'check.check_type'){
        //     if($this->check->check_type == 'Check'){
        //         $this->check_input = TRUE;
        //     }else{
        //         $this->check->check_number = NULL;
        //         $this->check_input = FALSE;
        //     }
        // }

        // if($field == 'check.bank_account_id'){
        //     if($this->check->bank_account_id == NULL){
        //         $this->check->bank_account_id = NULL;
        //         $this->check->check_type = NULL;
        //         $this->check->check_number = NULL;
        //         $this->check_input = FALSE;
        //     }
        // }

        // $this->validateOnly($field);
    }

    public function hasSplits($splits)
    {
        $this->expense_splits = $splits;
        $this->splits = TRUE;
    }

    public function editExpense(Expense $expense)
    {
        $this->expense = $expense;

        $this->view_text = [
            'card_title' => 'Update Expense',
            'button_text' => 'Update',
            'form_submit' => 'update',             
        ];

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

        $this->modal_show = TRUE;
    }

    public function resetModal()
    {
        // Public functions should be reset here
        $this->modal_show = FALSE;
        $this->expense = Expense::make();
        $this->split = NULL;
        $this->splits = NULL;
        $this->expense_splits = [];
    }

    public function update()
    {

    }

    public function render()
    {        
        return view('livewire.expenses.new-form', [
            // 'projects' => Project::orderBy('created_at', 'DESC')->get(),
            // 'distributions' => Distribution::all(),
            // 'bank_accounts' => $bank_accounts,
            // 'employees' => $employees,
            // 'vendors' => $vendors
        ]);
    }
}

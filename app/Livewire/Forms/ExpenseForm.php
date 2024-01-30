<?php

namespace App\Livewire\Forms;

use App\Models\Check;
use App\Models\Distribution;
use App\Models\Expense;
use App\Models\ExpenseSplits;
use App\Models\Project;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

use setasign\Fpdi\Fpdi;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseForm extends Form
{
    use AuthorizesRequests;

    public ?Expense $expense;

    public $expense_transactions_sum = FALSE;

    public $receipts = FALSE;

    #[Validate]
    public $amount = NULL;

    #[Validate]
    public $date = NULL;

    #[Validate]
    public $vendor_id = NULL;

    #[Validate]
    public $project_id = NULL;

    #[Validate]
    public $project_completed = FALSE;

    #[Validate]
    public $reimbursment = NULL;

    #[Validate]
    public $invoice = NULL;

    #[Validate]
    public $note = NULL;

    #[Validate]
    public $paid_by = NULL;

    #[Validate]
    public $notes = NULL;

    #[Validate]
    public $merchant_name = NULL;

    // required_without:form.paid_by
    #[Validate]
    public $bank_account_id = NULL;

    // required_with:check.bank_account_id
    #[Validate]
    public $check_type = NULL;

    // required_if:check.check_type,Check
    #[Validate]
    public $check_number = NULL;

    #[Validate]
    public $transaction = NULL;

    // #[Rule('sometimes|required_unless:reimbursment,null|mimes:jpeg,jpg,png,pdf')]
    //('required_if:reimbursment,Client')
    #[Validate]
    public $receipt_file = NULL;

    public function rules()
    {
        return [
            'amount' => 'required|numeric|regex:/^-?\d+(\.\d{1,2})?$/',
            'date' => 'required|date|before_or_equal:today|after:2017-01-01',
            'vendor_id' => 'required',
            'project_id' => 'required_unless:split,true',
            'reimbursment' => 'nullable',
            'invoice' => 'nullable',
            'project_completed' => 'nullable',
            'note' => 'nullable',
            'paid_by' => 'nullable',
            'notes' => 'nullable',
            'merchant_name' => 'nullable',
            'bank_account_id' => 'nullable',
            'check_type' => 'required_with:bank_account_id',
            'check_number' => 'required_if:check_type,Check',
            'transaction' => 'nullable',
            // 'reimbursment' => [
            //     Rule::requiredIf(function(){
            //         //client_reimbursement
            //         // dd($this->reimbursment == 'client_reimbursement');
            //         // dd(Project::findOrFail($this->project_id)->project_status->title == "Complete");
            //         $title = Project::findOrFail($this->project_id)->project_status->title;

            //         // return $title == 'Complete' && $this->reimbursment == 'client_reimbursement' ? false : true;
            //         if($title == 'Complete' && $this->reimbursment == 'client_reimbursement'){
            //             Rule::notIn(['client_reimbursement']);
            //         }else{
            //             //false = continue. true = validation error!
            //             return false;
            //             // || $this->split == true
            //             // return $this->reimbursment != NULL ? true : false;
            //         }
            //     }),
            //     // 'nullable',
            //     // 'mimes:jpeg,jpg,png,pdf'
            //     ],
            // 'reimbursment' => [
            //     Rule::notIn(['client_reimbursement']),
            //     // 'nullable',
            //     // 'mimes:jpeg,jpg,png,pdf'
            //     ],
            'receipt_file' => [
                Rule::requiredIf(function(){
                    if($this->receipts != FALSE){
                        return false;
                    }else{
                        // || $this->split == true
                        return $this->reimbursment != NULL && !is_numeric($this->reimbursment) ? true : false;
                    }
                }),
                'nullable',
                'mimes:jpeg,jpg,pdf'
                ],
        ];
    }

    // $this->form->reimbursment
    // if($value == 'Client'){
    //     $project = Project::findOrFail($this->form->project_id);
    //     // dd($project);
    //     //return with validation error for Reimbursment ... no Client Reimbursment allowed if Project_status = Complete
    //     //$this->reimbursment
    //     if($value == 'Client' && $project->project_status->title == 'Complete'){
    //         // errorBag reimbursment = "Cannot add Expense as Reimbursment for a project already Completed."
    //         $this->addError('testtest', 'Cannot add Expense as Reimbursment for a project already Completed.');
    //     }
    // }

    protected $messages =
    [
        'expense.amount.regex' => 'Amount format is incorrect. Format is 2145.36. No commas and only two digits after decimal allowed. If amount is under $1.00, use 00.XX',
        'expense.project_id.required_unless' => 'Project is required unless Expense is Split.',
        'expense.date.before_or_equal' => 'Date cannot be in the future. Make sure Date is before or equal to today.',
        'expense.date.after' => 'Date cannot be before the year 2017. Make sure Date is after or equal to 01/01/2017.',
        'receipt_file.required_if' => 'Receipt is required if Expense is Reimbursed or has Splits',
    ];

    //     return [

    //         'split' => 'nullable',
    //         'splits' => 'nullable',
    //         'amount_disabled' => 'nullable',

    //         //USED in MULTIPLE OF PLACES TimesheetPaymentForm and VendorPaymentForm
    //         //required_without:check.paid_by
    //         'check.bank_account_id' => 'nullable',
    //         'check.check_type' => 'required_with:check.bank_account_id',
    //         // 'check.check_number' => 'required_if:check.check_type,Check',
    //         //check_number is unique on Checks table where bank_account_id and check_number must be unique
    //         //02-21-2023 - used in MILTIPLE of places... VendorPaymentForm...
    //         'check.check_number' => [
    //             //ignore if vendor_id of Check is same as request()->vendor_id
    //             'required_if:check.check_type,Check',
    //             'nullable',
    //             'numeric',
    //             Rule::unique('checks', 'check_number')->where(function ($query) {
    //                 return $query->where('deleted_at', NULL)->where('bank_account_id', $this->check->bank_account_id)->where('vendor_id', '!=', $this->expense->vendor_id);
    //             }),
    //             //->ignore(request()->get('check_id_id'))
    //         ],
    //         'via_vendor_employees' => 'nullable',
    //     ];

    public function setExpense(Expense $expense)
    {
        $this->expense = $expense;

        if($this->expense->receipts){
            $receipt = $this->expense->receipts()->latest()->first();

            if(!is_null($receipt)){
                $this->receipts = TRUE;
                $this->notes = $receipt->notes;
                // if(!is_null($receipt->receipt_html)){
                // if(isset($receipt->receipt_items->handwritten_notes)){
                //     $this->handwritten = implode(", ", $receipt->receipt_items->handwritten_notes);
                // }

                // if(isset($receipt->receipt_items->purchase_order)){
                //     $this->purchase_order = $receipt->receipt_items->purchase_order;
                // }

                if(isset($receipt->receipt_items->merchant_name)){
                    $this->merchant_name = $receipt->receipt_items->merchant_name;
                }
            }
        }

        $this->amount = $this->expense->amount;
        $this->date = $expense->date->format('Y-m-d');
        $this->vendor_id = $expense->vendor_id;

        // 8-29-23 this can go into Expense model... getter ... get
        if($expense->distribution_id){
            $this->project_id = 'D:' . $expense->distribution_id;
        }else{
            $this->project_id = $expense->project_id;
            //if existing project is not SPLIT
            if(!is_null($this->project_id) && $this->project_id != 0){
                $project_title = Project::findOrFail($this->project_id)->project_status->title;
                if($project_title == 'Complete'){
                    $this->project_completed = TRUE;
                }
            }
        }

        $this->reimbursment = $expense->reimbursment;
        $this->invoice = $expense->invoice;
        $this->note = $expense->note;
        $this->paid_by = $expense->paid_by;

        if($this->expense->check){
            $this->bank_account_id = $this->expense->check->bank_account_id;
            $this->check_type = $this->expense->check->check_type;
            $this->check_number = $this->expense->check->check_number;
        }

        //09-05-2023 need to get the file extention here... not a boolen
        // $this->receipt_file = $this->expense->receipts()->exists();

        $this->expense_transactions_sum = $this->expense->transactions->sum('amount') == $this->expense->amount ? true : false;
    }

    public function expenseDetails()
    {
        if(is_numeric($this->project_id)){
            $project_id = $this->project_id;
            $distribution_id = NULL;
            $dist_user = NULL;
        }elseif($this->component->splits){
            $project_id = NULL;
            $distribution_id = NULL;
            $dist_user = NULL;
        }elseif(is_null($this->project_id)){
            dd('in elseif');
            $project_id = NULL;
            $distribution_id = NULL;
            $dist_user = $this->vendor_id;
        }else{
            $project_id = NULL;
            $distribution_id = substr($this->project_id, 2);
            $dist_user = NULL;

            //for checks
            // $distribution = Distribution::findOrFail($distribution_id)->user_id;
            // if($distribution != 0){
            //     $dist_user = $distribution;
            // }else{
            //     $dist_user = NULL;
            // }
        }

        return [
            'project_id' => $project_id,
            'distribution_id' => $distribution_id,
            'dist_user' => $dist_user,
        ];
    }

    public function save_splits($expense_id)
    {
        $expense_details = $this->expenseDetails();
        //if no splits / splits removed and project/distrubtuion entered...
        if(!$this->expense->splits->isEmpty() && (!is_null($expense_details['project_id']) || !is_null($expense_details['distribution_id']))){
            foreach($this->expense->splits as $split_to_remove){
                $split_to_remove = ExpenseSplits::findOrFail($split_to_remove->id);
                $split_to_remove->delete();
            }
        }else{
            foreach(collect($this->component->expense_splits) as $split){
                if(is_numeric($split['project_id'])){
                    $project_id = $split['project_id'];
                    $distribution_id = NULL;
                }else{
                    $project_id = NULL;
                    $distribution_id = substr($split['project_id'], 2);
                }

                if(isset($split['id'])){
                    $update_split = ExpenseSplits::findOrFail($split['id']);
                    $update_split->update([
                        'amount' => $split['amount'],
                        'expense_id' => $expense_id,
                        'project_id' => $project_id,
                        'distribution_id' => $distribution_id,
                        'reimbursment' => isset($split['reimbursment']) ? $split['reimbursment'] : NULL,
                        'note' => isset($split['note']) ? $split['note'] : NULL,
                        'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                        'created_by_user_id' => auth()->user()->id,
                    ]);
                }else{
                    $split = ExpenseSplits::create([
                        'amount' => $split['amount'],
                        'expense_id' => $expense_id,
                        'project_id' => $project_id,
                        'distribution_id' => $distribution_id,
                        'reimbursment' => isset($split['reimbursment']) ? $split['reimbursment'] : NULL,
                        'note' => isset($split['note']) ? $split['note'] : NULL,
                        'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                        'created_by_user_id' => auth()->user()->id,
                    ]);
                }
            }
        }
    }

    public function delete()
    {
        //12/13/23 what about !! ASSOCIATED EXPENSES ?
        $associated_expenses = $this->expense->associated;
        foreach($associated_expenses as $associated_expenses){
            $associated_expenses->parent_expense_id = NULL;
            $associated_expenses->save();
        }

        //TRANSACTIONS
        $transactions = $this->expense->transactions;
        foreach($transactions as $transaction){
            $transaction->expense_id = NULL;
            $transaction->save();
        }

        $this->expense->delete();
    }

    public function update()
    {
        $this->authorize('create', Expense::class);
        $this->validate();

        $expense_details = $this->expenseDetails();

        $this->expense->update([
            'amount' => $this->amount,
            'date' => $this->date,
            'invoice' => $this->invoice,
            'note' => $this->note,
            //if $split true, project_id = NULL || if expense_splits isset/true, project_id by default is NULL as expected.
            'project_id' => $expense_details['project_id'],
            'distribution_id' => $expense_details['distribution_id'],
            'vendor_id' => $this->vendor_id,
            // 'check_id' => $check_id,
            'paid_by' => empty($this->paid_by) ? NULL : $this->paid_by,
            'reimbursment' => $this->reimbursment,
            // 'belongs_to_vendor_id' => $vendor->id,
            'created_by_user_id' => auth()->user()->id,
        ]);

        //check...

        $this->save_splits($this->expense->id);

        if($this->receipt_file){
            $this->upload_receipt_file($this->expense->amount, $this->expense->id);
        }

        return $this->expense;
    }

    public function store()
    {
        $this->authorize('create', Expense::class);
        $this->validate();
        //validate check...
        // dd($this);

        $expense_details = $this->expenseDetails();

        if(empty($this->paid_by) && isset($this->bank_account_id)){
            if($expense_details['distribution_id']){
                $distribution_user_id = Distribution::findOrFail($expense_details['distribution_id'])->user_id;
                if($distribution_user_id != 0){
                    $dist_user = $distribution_user_id;
                }else{
                    $dist_user = NULL;
                }
            }else{
                $dist_user = NULL;
            }

            $check = Check::create([
                'check_type' => $this->check_type,
                'check_number' => $this->check_number,
                'date' => $this->date,
                'bank_account_id' => $this->bank_account_id,
                'amount' => $this->amount,
                //user_id if expense project = distribution
                'user_id' => $dist_user,
                'vendor_id' => $this->vendor_id,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'created_by_user_id' => auth()->user()->id,
            ]);
        }

        // $expense = Expense::create($this->only(['amount', 'date', 'vendor_id', 'project_id', 'reimbursment', 'invoice', 'note', 'paid_by']));
        $expense = Expense::create([
            'amount' => $this->amount,
            'date' => $this->date,
            'invoice' => $this->invoice,
            'note' => $this->note,
            //if $split true, project_id = NULL || if expense_splits isset/true, project_id by default is NULL as expected.
            'project_id' => $expense_details['project_id'],
            'distribution_id' => $expense_details['distribution_id'],
            'vendor_id' => $this->vendor_id,
            'check_id' => !isset($check) ? NULL : $check->id,
            'paid_by' => empty($this->paid_by) ? NULL : $this->paid_by,
            'reimbursment' => $this->reimbursment,
            'belongs_to_vendor_id' => auth()->user()->vendor->id,
            'created_by_user_id' => auth()->user()->id,
        ]);

        if($this->transaction){
            $this->transaction->expense_id = $expense->id;
            $this->transaction->save();
        }

        if($this->receipt_file){
            $this->upload_receipt_file($expense->amount, $expense->id);
        }

        return $expense;
    }

    public function upload_receipt_file($expense_amount, $expense_id)
    {
        $ocr_filename = date('Y-m-d-H-i-s') . '-' . rand(10,99) . '.' . $this->receipt_file->getClientOriginalExtension();
        $ocr_path = 'files/_temp_ocr/' . $ocr_filename;
        $this->receipt_file->storeAs('_temp_ocr', $ocr_filename, 'files');

        $location = storage_path($ocr_path);
        $post_data = file_get_contents($location);

        $doc_type = $this->receipt_file->getClientOriginalExtension();
        // dd($doc_type);

        if($doc_type == 'pdf'){
            //if $width under 180mm($width), prebuilt-receipt, otherwise if wider, use prebuilt-invoice
            $pdf = new Fpdi();
            $pdf->setSourceFile($location);
            $pageId = $pdf->importPage(1);
            //unit = mm
            $width = $pdf->getTemplateSize($pageId)['width'];

            //$document_model = based on file dimensions. receipt vs invoice
            if($width < 180 ){
                $document_model = 'prebuilt-receipt';
            }else{
                $document_model = 'prebuilt-invoice';
            }
        }else{
            //13/13/23 if img file is invoice v/s receipt!
            $document_model = 'prebuilt-receipt';
        }

        $doc_type = '.' . $doc_type;
        //send to ReceiptController@azure_receipts with $location and $document_model
        $ocr_receipt_extracted = app('App\Http\Controllers\ReceiptController')->azure_receipts($post_data, $doc_type, $document_model);
        // dd($ocr_receipt_extracted);

        //pass receipt info to ocr_extract method
        $ocr_receipt_data = app('App\Http\Controllers\ReceiptController')->ocr_extract($ocr_receipt_extracted, $expense_amount);
        // dd($ocr_receipt_data);

        //ATTACHMENT
        //send to ReceiptController@add_attachments_to_expense
        app('App\Http\Controllers\ReceiptController')->add_attachments_to_expense($expense_id, NULL, $ocr_receipt_data, $ocr_filename);

        $this->receipt_file = NULL;
    }
}

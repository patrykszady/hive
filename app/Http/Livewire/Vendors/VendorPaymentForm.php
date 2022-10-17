<?php

namespace App\Http\Livewire\Vendors;

use App\Models\Check;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Vendor;
use App\Models\BankAccount;

use Livewire\Component;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VendorPaymentForm extends Component
{
    use AuthorizesRequests;

    public Vendor $vendor;

    //project_id = 0 default? so disable works?
    public $project_id = NULL;
    public $project = NULL;
    public $payment_projects = [];
    public $check = NULL;
    public $check_input = NULL;

    protected $listeners = ['addProject', 'removeProject', 'updateProjectBids'];

    protected function rules()
    {
        return [    
            'payment_projects.*.amount' => 'required|numeric|min:0.01|regex:/^-?\d+(\.\d{1,2})?$/',
            'project_id' => 'nullable',

            'check.paid_by' => 'required_without:check.bank_account_id',
            'check.date' => 'required|date|before_or_equal:today|after:2017-01-01',
            'check.bank_account_id' => 'required_without:check.paid_by',
            'check.check_type' => 'required_with:check.bank_account_id',
            //1/3/2022 check_number is unique on Checks table where bank_account_id and check_number must be unique
            'check.check_number' => 'required_if:check.check_type,Check',  
            'check.invoice' => 'required_with:check.paid_by',          
        ];
    }

    protected $messages = 
    [
        'payment_projects.*.amount.required' => 'Project Amount is required if included.',
        'payment_projects.*.amount.numeric' => 'Project Amount must be a number if included.',
        'payment_projects.*.amount.min' => 'Project Amount must be at least $0.01 if included.',
        'payment_projects.*.amount.regex' => 'Amount format is incorrect. Format is 2145.36. No commas and only two digits after decimal allowed. If amount is under $1.00, use 0.XX',
        'check.check_number' => 'Check Number is required if Payment Type is Check',
    ];

    public function mount()
    {     
        // $this->payment_projects = collect();        

        $this->check = Check::make();
        $this->check->date = today()->format('Y-m-d');

        $this->view_text = [
            'card_title' => 'Create Vendor Payments',
            'button_text' => 'Create Vendor Check',
            'form_submit' => 'store',             
        ];

        $this->bank_accounts = BankAccount::with('bank')->where('type', 'Checking')
            ->whereHas('bank', function ($query) {
                return $query->whereNotNull('plaid_access_token');
            })->get();
    }

    public function getVendorCheckSumProperty()
    {
        return collect($this->payment_projects)->where('amount', '!=', '')->sum('amount');
    }

    public function updatedProjectId()
    {
        $this->project = Project::findOrFail($this->project_id);
    }

    public function updated($field)
    {    
        if(substr($field, 0, 16) == 'payment_projects'){
            $project_id = preg_replace("/[^0-9]/", '', $field);

            $balance = $this->payment_projects[$project_id]['vendor_sum'] - $this->payment_projects[$project_id]['bids'];
            $amount = $this->payment_projects[$project_id]['amount'];

            if($amount > 0){
                $project_balance = $balance - $amount;
            }else{
                $project_balance = $balance;
            }

            $this->payment_projects[$project_id]['balance'] = $project_balance;
        }

        if($field == 'check.check_type'){
            if($this->check->check_type == 'Check'){
                $this->check_input = TRUE;
            }else{
                $this->check->check_number = NULL;
                $this->check_input = FALSE;
            }
        }
 
        $this->validateOnly($field);     
    }

    public function addProject()
    {
        //if $project is already in $payment_projects collection (IGNORE / CONTINUE)
        if(in_array($this->project_id, collect($this->payment_projects)->pluck('id')->toArray())){
            //Does Not reset $this->payment_projects.*.AMOUNT
            $this->addError('project_id', 'Project already in Vendor Payment. Cannot add.');
        }else{
            $project = $this->project;

            $project_vendor_expenses = $project->expenses()->where('vendor_id', $this->vendor->id)->get();
            $project_bids = $project->bids()->where('vendor_id', $this->vendor->id)->get();

            $project->vendor_expenses = $project_vendor_expenses;
            $project->vendor_sum = $project_vendor_expenses->sum('amount');
            $project->balance = $project_vendor_expenses->sum('amount') - $project_bids->sum('amount');
            $project->bids = $project->bids()->where('vendor_id', $this->vendor->id)->sum('amount');
    
            //add this Model to $payment_projects collection
            $this->payment_projects += [$project->id => $project];        
            $this->project_id = NULL;
        }
    }

    public function updateProjectBids($project_id)
    {
        $this->payment_projects[$project_id]['bids'] = Project::findOrFail($project_id)->bids()->where('vendor_id', $this->vendor->id)->sum('amount');

        $balance = $this->payment_projects[$project_id]['vendor_sum'] - $this->payment_projects[$project_id]['bids'];
        $this->payment_projects[$project_id]['balance'] = $balance;
    }

    public function updateProjectBalance($project_id)
    {
        $balance = $this->payment_projects[$project_id]['vendor_sum'] - $this->payment_projects[$project_id]['bids'];
        $amount = $this->payment_projects[$project_id]['amount'];

        if($amount > 0){
            $project_balance = $balance - $amount;
        }else{
            $project_balance = $balance;
        }

        $this->payment_projects[$project_id]['balance'] = $project_balance;
    }

    public function removeProject($project_id_to_remove)
    {
        //project_id = key in $this->payment_projects
        //remove this Model from $payment_projects collection
        unset($this->payment_projects[$project_id_to_remove]);
        // $this->payment_projects->forget($project_id_to_remove);
    }

    public function store()
    {
        $this->validate();
        //validate check total is greater than $0

        //create expense for each $payment_projects. create one Check for all Expenses and associate with the Check.
        if(!$this->check['paid_by']){
            $check = Check::create([
                'check_type' => $this->check['check_type'],
                'check_number' => $this->check['check_number'],
                'date' => $this->check['date'],
                'bank_account_id' => $this->check['bank_account_id'],
                'vendor_id' => $this->vendor->id,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'created_by_user_id' => auth()->user()->id,                
            ]);
        }

        //create $expense
        foreach($this->payment_projects as $project){
            $expense = Expense::create([
                'amount' => $project['amount'],
                // 'date' => today()->format('Y-m-d'),
                'date' => $this->check['date'],
                'project_id' => $project['id'],
                'vendor_id' => $this->vendor->id,
                'check_id' => isset($check) ? $check->id : NULL,
                'paid_by' => isset($check) ? NULL : $this->check['paid_by'],
                'invoice' => isset($check) ? NULL : $this->check['invoice'],
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'created_by_user_id' => auth()->user()->id,
            ]);
        }

        if(isset($check)){
            return redirect()->route('checks.show', $check->id);
        }else{
            return redirect()->route('vendors.show', $this->vendor->id);
        }        
    }

    public function render()
    {
        //8/22/22 projects where id not in $this->payment_projects (key/id)
        $projects = Project::active()->orderBy('created_at', 'DESC')->get();

        //->whereNot('users.id', auth()->user()->id)
        $employees = auth()->user()->vendor->users()->where('is_employed', 1)->get();

        return view('livewire.vendors.payment-form', [
            'projects' => $projects,
            'employees' => $employees,
        ]);
    }
}

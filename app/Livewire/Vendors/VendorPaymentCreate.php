<?php

namespace App\Livewire\Vendors;

use App\Models\Project;
use App\Models\Vendor;
use App\Models\BankAccount;

use App\Mail\VendorPaymentMade;

use Livewire\Component;
use Livewire\Attributes\Title;

use App\Livewire\Forms\VendorPaymentForm;

use Carbon\Carbon;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VendorPaymentCreate extends Component
{
    use AuthorizesRequests;

    public VendorPaymentForm $form;

    public Vendor $vendor;

    public $projects = [];
    public $payment_projects = [];
    public $saved_expenses = [];

    protected $listeners = ['addProject', 'removeProject', 'updateProjectBids'];

    protected function rules()
    {
        return [
            'projects.*.show' => 'nullable',
            'projects.*.vendor_expenses_sum' => 'nullable',
            'projects.*.vendor_bids_sum' => 'nullable',
            'projects.*.balance' => 'nullable',
            'projects.*.amount' => 'required|numeric|min:0.01|regex:/^-?\d+(\.\d{1,2})?$/',
        ];
    }

    public function mount()
    {
        //09-05-2023 if proejct not active ...add in dropdown
        // $projects = Project::active()->orderBy('created_at', 'DESC')->get();
        //whereNotIn('id', $existing_projects)

        // $vendor_id = $this->vendor->id;
        $this->projects =
            Project::where('created_at', '>', Carbon::now()->subYears(2)->format('Y-m-d'))
                ->orderBy('created_at', 'DESC')
                ->whereHas('project_status', function ($query) {
                    $query->whereIn('project_status.title', ['Active', 'Complete']);
                })
                // ->with(['expenses' => function ($query) {
                //     return $query->where('vendor_id', '4');
                //     }])
                ->get()
                ->each(function ($item, $key) {
                    $item->show = false;
                    // $item->show_timestamp = now();
                })
                ->keyBy('id');

        $this->form->date = today()->format('Y-m-d');
    }

    public function updated($field, $value)
    {
        // if(substr($field, 0, 8) == 'projects'){
        //     $project_id = preg_replace("/[^0-9]/", '', $field);

        //     // $this->updateProjectBalance($project_id);

        //     // $balance = $this->projects[$project_id]->balance;
        //     // $amount = $this->projects[$project_id]->amount;
        // }

        if(substr($field, 0, 8) == 'projects'){
            $project_id = preg_replace("/[^0-9]/", '', $field);

            $this->updateProjectBalance($project_id);
        }

        // if($field == 'check.check_type'){
        //     if($this->check->check_type == 'Check'){
        //         $this->check_input = TRUE;
        //     }else{
        //         $this->check->check_number = NULL;
        //         $this->check_input = FALSE;
        //     }
        // }

        $this->validateOnly($field);

        if(in_array($field, ['form.bank_account_id', 'form.paid_by'])){
            $this->validateOnly('form.bank_account_id');
            $this->validateOnly('form.paid_by');
        }
    }

    public function addProject()
    {
        $project = $this->projects[$this->form->project_id];
        $project->show = true;
        $project->vendor_expenses_sum = $project->expenses()->where('vendor_id', $this->vendor->id)->sum('amount');
        $project->vendor_bids_sum = $project->bids()->vendorBids($this->vendor->id)->sum('amount');
        // $project->show_timestamp = now();
        $project->balance = $project->vendor_bids_sum - $project->vendor_expenses_sum;

        $this->form->project_id = "";
    }

    public function updateProjectBids($project_id)
    {
        $project = $this->projects[$project_id];
        $project['vendor_bids_sum'] = Project::findOrFail($project_id)->bids()->vendorBids($this->vendor->id)->sum('amount');

        $this->updateProjectBalance($project_id);

        // $this->payment_projects[$project_id]['bids'] = Project::findOrFail($project_id)->bids()->vendorBids($this->vendor->id)->sum('amount');

        // $balance = $this->payment_projects[$project_id]['bids'] - $this->payment_projects[$project_id]['vendor_sum'];

        // $this->payment_projects[$project_id]['balance'] = $balance;
    }

    public function updateProjectBalance($project_id)
    {
        if($this->projects[$project_id]->amount == NULL || $this->projects[$project_id]->amount <= 0){
            $amount = 0;
        }else{
            $amount = $this->projects[$project_id]->amount;
        }

        $total_paid = $this->projects[$project_id]->vendor_expenses_sum;
        $bids_amount = $this->projects[$project_id]->vendor_bids_sum;
        $balance = ($bids_amount - $total_paid) - $amount;

        $this->projects[$project_id]->balance = $balance;
    }

    public function removeProject($project_id_to_remove)
    {
        $project = $this->projects->where('id', $project_id_to_remove)->first();
        $project->show = false;

        $this->form->project_id = "";
    }

    public function getVendorCheckSumProperty()
    {
        $total = 0;
        $total += $this->projects->where('show', true)->where('amount', '>' , 0)->sum('amount');
        return $total;
    }

    public function save()
    {
        //validate check total is greater than $0
        //if less than or equal to 0... send back with error
        if($this->getVendorCheckSumProperty() <= 0){
            return $this->addError('check_total_min', 'Check total needs to be greater than $0 and include at least 1 project.');
        }else{
            $check = $this->form->store();
        }

        //09-06-2023 move somewhere else?
        //send email to vendor being paid...
        if(!is_null($check)){
            //get check total AMOUNT
            // + $check->timesheets->sum('amount')
            $check->amount = $check->expenses->sum('amount');
            $check->save();

            if(env('APP_ENV') == 'production'){
                Mail::to($this->vendor->business_email)
                    ->cc([auth()->user()->vendor->business_email])
                    ->send(new VendorPaymentMade($this->vendor, auth()->user()->vendor, $check));
            }

            return redirect()->route('checks.show', $check->id);
        }else{
            return redirect()->route('vendors.show', $this->vendor->id);
        }
    }

    #[Title('Vendor Payment')]
    public function render()
    {
        $view_text = [
            'card_title' => 'Create Vendor Payments',
            'button_text' => 'Create Vendor Check',
            'form_submit' => 'save',
        ];

        //->whereNot('users.id', auth()->user()->id)
        $employees = auth()->user()->vendor->users()->where('is_employed', 1)->get();

        $bank_accounts = BankAccount::with('bank')->where('type', 'Checking')
            ->whereHas('bank', function ($query) {
                return $query->whereNotNull('plaid_access_token');
            })->get();

        $projects = $this->projects;

        return view('livewire.vendors.payment-form', [
            'view_text' => $view_text,
            'employees' => $employees,
            'bank_accounts' => $bank_accounts,
            'projects' => $projects,
        ]);
    }
}

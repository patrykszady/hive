<?php

namespace App\Http\Livewire\Timesheets;

use Illuminate\Validation\Rule;

use App\Models\BankAccount;
use App\Models\Check;
use App\Models\Expense;
use App\Models\User;
use App\Models\Timesheet;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimesheetPaymentForm extends Component
{
    use WithPagination, AuthorizesRequests;

    public User $user;
    public $check = NULL;
    public $check_input = NULL;
    public $weekly_timesheets = [];
    public $employee_weekly_timesheets = [];
    public $user_paid_expenses = [];

    protected function rules()
    {
        return [
            'user.full_name' => 'nullable',

            // required|date|before_or_equal:today|after:2017-01-01
            'check.date' => 'required|date|before_or_equal:today|after:2017-01-01',
            'check.paid_by' => 'required_without:check.bank_account_id',
            'check.bank_account_id' => 'required_without:check.paid_by',
            'check.check_type' => 'required_with:check.bank_account_id',
            'check.check_number' => 'required_if:check.check_type,Check',   
            'check.invoice' => 'required_with:check.paid_by',  
            
            //7/18/2022 ignore if updating Check  ->ignore(request()->get('check_id_id'))
            // 'check.check_number' => [
            //     'required_if:check.check_type,Check',
            //     'nullable',
            //     Rule::unique('checks', 'check_number')->where(function ($query) {
            //         return $query->whereNull('deleted_at')->where('bank_account_id', $this->check->bank_account_id);
            //     }),
            //     'nullable',
            //     'numeric',
            // ],  
            'weekly_timesheets.*.checkbox' => 'nullable',
            'employee_weekly_timesheets.*.checkbox' => 'nullable',
            'user_paid_expenses.*.checkbox' => 'nullable',
        ];
    }

    public function mount()
    {
        $this->view_text = [
            'card_title' => 'Create Daily Hours',
            'button_text' => 'Pay ' . $this->user->first_name,
            'form_submit' => 'store',             
        ];
        
        // $this->authorize('index', Hour::class);

        //8-26-2022 full_name shoud be pre-loaded
        $this->user->full_name = $this->user->full_name;
        $this->check = Check::make();
        $this->check->date = today()->format('Y-m-d');

        $this->bank_accounts = BankAccount::with('bank')->where('type', 'Checking')
            ->whereHas('bank', function ($query) {
                return $query->whereNotNull('plaid_access_token');
            })->get();

        //groupBy key should be the below in the view..works here not in the view
        $this->weekly_timesheets = 
            Timesheet::
                where('user_id', $this->user->id)
                ->whereNull('check_id')
                ->whereNull('paid_by')
                ->whereNull('deleted_at')
                ->get()
                ->each(function ($item, $key) {
                    $item->checkbox = true;
                })
                ->keyBy('id');
                // ->groupBy('date');
                // ->groupBy(function ($each) {
                //     return $each->date->format('Y-m-d');
                // })
        
        $this->employee_weekly_timesheets =
            Timesheet::
                with('user')
                ->where('paid_by', $this->user->id)
                ->whereNull('check_id')
                ->whereNull('deleted_at')
                ->get()
                ->each(function ($item, $key) {
                    $item->checkbox = true;
                })
                ->keyBy('id');

        $this->user_paid_expenses = 
            Expense::
                where('paid_by', $this->user->id)
                ->whereNull('check_id')
                ->get()
                ->each(function ($item, $key) {
                    $item->checkbox = true;
                })
                ->keyBy('id');

        // dd($this->user_paid_expenses);
    }

    public function updated($field)
    {
        //reset check and reference if paid_by or check items are changed.
        //8-24-2022 - this goes with VendorPaymentForm as well.
        if($field == 'check.check_type'){
            if($this->check->check_type == 'Check'){
                $this->check_input = TRUE;
            }else{
                $this->check->check_number = NULL;
                $this->check_input = FALSE;
            }
        }

        if($field == 'check.paid_by'){
            $this->check->bank_account_id = NULL;
            $this->check->check_type = NULL;
            $this->check->check_number = NULL;
            $this->check_input = FALSE;
        }

        $this->validateOnly($field);
    }

    public function getWeeklyTimesheetsTotalProperty()
    { 
        $total = 0;
        //$this->weekly_timesheets
        foreach($this->weekly_timesheets as $hours){
            if($hours->checkbox == true){
                $total += $hours->amount;
            }
        }

        //$this->employee_weekly_timesheets
        foreach($this->employee_weekly_timesheets as $hours){
            if($hours->checkbox == true){
                $total += $hours->amount;
            }
        }

        //$this->user_paid_expenses
        foreach($this->user_paid_expenses as $expense){
            if($expense->checkbox == true){
                $total += $expense->amount;
            }
        }

        return $total;
    } 

    public function store()
    {        
        // $this->authorize('create', Expense::class);
        $this->validate();
        //8-26-2022 - validate Pay User Total Check is greater than $0 / $this->weekly_timesheets has at least one Item in Collection

        if(!$this->check['paid_by']){
            $check = Check::create([
                'check_type' => $this->check['check_type'],
                'check_number' => $this->check['check_number'],
                'date' => $this->check['date'],
                'bank_account_id' => $this->check['bank_account_id'],
                'user_id' => $this->user->id,
                'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                'created_by_user_id' => auth()->user()->id,                
            ]);
        }

        //update Timesheets
        foreach($this->weekly_timesheets->where('checkbox', 'true') as $weekly_timesheet){
            //ignore 'checkbox'
            $weekly_timesheet->offsetUnset('checkbox');

            $weekly_timesheet->check_id = isset($check) ? $check->id : NULL;
            $weekly_timesheet->paid_by = isset($check) ? NULL : $this->check['paid_by'];
            $weekly_timesheet->invoice = isset($check) ? NULL : $this->check['invoice'];
            $weekly_timesheet->save(); 
        }

        foreach($this->employee_weekly_timesheets->where('checkbox', 'true') as $weekly_timesheet){
            //ignore 'checkbox'
            $weekly_timesheet->offsetUnset('checkbox');
            $weekly_timesheet->check_id = $check->id;
            $weekly_timesheet->save();
        }

        foreach($this->user_paid_expenses->where('checkbox', 'true') as $expense){
            //ignore 'checkbox'
            $expense->offsetUnset('checkbox');
            $expense->check_id = $check->id;
            $expense->save();
        }

        return redirect()->route('timesheets.payment', $this->user->id);
    }

    public function render()
    {
        $this->authorize('viewPayment', Timesheet::class);
        
        $bank_accounts = BankAccount::where('type', 'Checking')->get();

        // dd($this->employee_weekly_timesheets->where('checkbox', 'true'));
        //if $this->employee_weekly_timesheets is not empty... disable "paid_by"
        if(!$this->employee_weekly_timesheets->isEmpty()){
            $employees = [];
        }else{
            $employees = $this->user->vendor->users()->where('is_employed', 1)->whereNot('users.id', $this->user->id)->get();
            // $employees = $payment_vendor->users()->where('is_employed', 1)->whereNot('users.id', $this->user->id)->get();
        }        

        //07/16/2022: what about distributions.. would distributions ever end up here?

        // return view('livewire.timesheets.payment-form');
        return view('livewire.timesheets.payment-form', [
            'bank_accounts' => $bank_accounts,
            'employees' => $employees,
        ]);
    }
}

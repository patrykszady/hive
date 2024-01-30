<?php

namespace App\Livewire\Checks;

use App\Models\Bank;
use App\Models\Check;
use App\Models\BankAccount;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChecksIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    public $banks = [];
    public $bank = '' ;
    public $check_number = '' ;
    public $amount = '' ;
    public $check_type = '';

    protected $queryString = [
        'bank' => ['except' => ''],
        'check_number' => ['except' => ''],
        'check_type' => ['except' => ''],
        'amount' => ['except' => '']
    ];

    public function updating($field)
    {
        $this->resetPage();
    }

    public function mount()
    {
        //where $check->transactions dont equal $check->amount
        // $checks = Check::whereBetween('date', ['2022-09-01', '2023-09-01'])->whereDoesntHave('transactions')->where('check_type', '!=', 'Cash')->get();

        // dd($checks);
        $this->banks =
            Bank::orderBy('created_at', 'DESC')
                ->with('accounts')
                ->whereHas('accounts', function ($query) {
                    return $query->whereIn('type', ['Checking', 'Savings']);
                })->get();
                //->groupBy('plaid_ins_id')
                // $this->banks =
                // Bank::with('accounts')
                //     ->whereHas('accounts', function ($query) {
                //         return $query->whereIn('type', ['Checking', 'Savings']);
                //     })->get()->groupBy('plaid_ins_id');
    }

    #[Title('Checks')]
    public function render()
    {
        //$this->authorize('viewAny', Expense::class);
        if($this->bank){
            $bank_account_ids = Bank::where('id', $this->bank)->first()->plaid_ins_id;
            $bank_account_ids = Bank::where('plaid_ins_id', $bank_account_ids)->pluck('id');

            $bank_accounts = BankAccount::whereIn('bank_id', $bank_account_ids)->pluck('id')->toArray();
        }else{
            $bank_accounts = BankAccount::all()->pluck('id')->toArray();
        }

        $check_number = $this->check_number;
        $amount = $this->amount;
        $checks =
            Check::orderBy('date', 'DESC')
                //distributions
                ->with(['expenses', 'timesheets', 'bank_account'])
                ->whereIn('bank_account_id', $bank_accounts)
                ->where('check_type', 'like', "%{$this->check_type}%")
                ->when($check_number, function ($query) {
                    return $query->where('check_number', 'like', "%{$this->check_number}%");
                })
                ->when($amount, function ($query) {
                    return $query->where('amount', 'like', "{$this->amount}%");
                })
                ->paginate(10);

        return view('livewire.checks.index', [
            'checks' => $checks,
        ]);
    }
}

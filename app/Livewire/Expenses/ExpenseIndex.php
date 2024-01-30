<?php

namespace App\Livewire\Expenses;

use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Project;
use App\Models\Bank;
use App\Models\Distribution;
use App\Models\Transaction;

use Livewire\Component;
use Livewire\Attributes\Title;

use Livewire\WithPagination;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseIndex extends Component
{
    use WithPagination, AuthorizesRequests;

    public $amount = NULL;
    public $project = NULL;
    public $vendor = NULL;
    public $banks = NULL;
    public $bank = NULL;
    public $bank_owners = [];
    public $bank_owner = NULL;
    public $status = NULL;
    public $bank_account_ids = [];

    public $view = NULL;

    protected $listeners = ['refreshComponent' => '$refresh'];

    protected $queryString = [
        'amount' => ['except' => ''],
        'project' => ['except' => ''],
        'vendor' => ['except' => ''],
        'bank' => ['except' => ''],
        'bank_owner' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updating($field)
    {
        $this->resetPage('expenses_page');
        $this->resetPage('transactions_page');
    }

    public function updated($field)
    {
        if($field == 'project'){
            $this->vendor = NULL;
        }

        // if($field == 'vendor'){
        //     $this->project = NULL;
        // }
    }

    public function mount()
    {
        $this->banks = Bank::with('accounts')->get()->groupBy('plaid_ins_id')->toBase();
    }

    #[Title('Expenses')]
    public function render()
    {
        $this->authorize('viewAny', Expense::class);

        if($this->view == NULL){
            $paginate_number = 8;
        }else{
            $paginate_number = 5;
        }

        $expenses = Expense::
            orderBy('date', 'DESC')
            ->with(['project', 'distribution', 'vendor', 'splits', 'transactions', 'receipts'])
            // ->whereBetween('date', [today()->subYear(2), today()])
            ->where('amount', 'like', "{$this->amount}%")
            ->when($this->project == 'SPLIT', function ($query) {
                return $query->has('splits');
            })
            ->when($this->project == 'NO_PROJECT', function ($query, $item) {
                // $expense_ids_excluded += $
                return $query->where('project_id', "0")->whereNull('distribution_id');
            })
            ->when(substr($this->project, 0, 2) == "D-", function ($query) {
                return $query->where('distribution_id', substr($this->project, 2));
            })
            ->when(is_numeric($this->project), function ($query, $project) {
                return $query->where('project_id', $this->project);
            })
            ->when($this->vendor != NULL, function ($query, $vendor) {
                return $query->where('vendor_id', 'like', "{$this->vendor}");
            })
            ->paginate($paginate_number, ['*'], 'expenses_page');

        if($this->bank != NULL){
            $this->bank_account_ids = array();

            // $this->banks->get($this->bank)->each(function($bank, $bank_accounts) { return array_push($bank_accounts, $bank->accounts->pluck('id')->toArray()); });
            foreach($this->banks->get($this->bank) as $bank){
                $this->bank_account_ids = array_merge($this->bank_account_ids, $bank->accounts->pluck('id')->toArray());
            }

            $this->bank_owners =
                Transaction::
                    whereNull('expense_id')
                    ->whereNull('check_id')
                    ->doesntHave('payments')
                    ->whereNotNull('owner')
                    ->whereIn('bank_account_id', $this->bank_account_ids)
                    ->groupBy('owner')
                    ->pluck('owner')
                    ->toArray();
            // dd($owner_transactions);

            // $this->bank_owners = ['286', '4144', '3134'];
        }else{
            $this->bank_account_ids = array();
            $this->bank_owners = array();
            $this->bank_owner = NULL;
        }

        if(in_array($this->project, ["NO_PROJECT", ""])){
            $transactions = Transaction::
                orderBy('transaction_date', 'DESC')
                ->with(['bank_account'])
                // ->whereBetween('transaction_date', [today()->subYear(1), today()])
                ->where('amount', 'like', "%{$this->amount}%")
                ->where('amount', '!=', '0.00')
                // ->whereNotNull('vendor_id')
                ->when($this->vendor != NULL, function ($query, $vendor) {
                    return $query->where('vendor_id', 'like', "{$this->vendor}");
                })
                ->when($this->bank != NULL, function ($query, $bank_account_ids) {
                    return $query->whereIn('bank_account_id', $this->bank_account_ids);
                })
                ->when($this->bank_owner != NULL, function ($query, $bank_owner) {
                    return $query->where('owner', $this->bank_owner);
                })
                //1-28-2023 create scope for Transaction... only query transactions that belong to auth()->user()->vendor->id
                //transaction_date as date
                ->select('transactions.*', 'transaction_date as date')
                ->whereNull('expense_id')
                ->whereNull('check_id')
                ->doesntHave('payments')
                // ->get();
                ->paginate($paginate_number, ['*'], 'transactions_page');
        }else{
            $transactions = collect();
        }

        $expenses->getCollection()->each(function ($expense, $key) use ($expenses){
            if($expense->transactions->isNotEmpty() && ($expense->project_id != "0" || $expense->project_id != NULL) || isset($expense->check_id)){
                $expense->status = 'Complete';
            }else{
                if($expense->project_id != "0" && $expense->transactions->isEmpty()){
                    $expense->status = 'No Transactions';
                }elseif($expense->project_id == "0" && $expense->transactions->isNotEmpty()){
                    $expense->status = 'No Project';
                }else{
                    $expense->status = 'Missing Info';
                }
            }
        });

        //where vendor is in result
        if(is_null($this->project) || $this->project == ""){
            $vendors = Vendor::whereHas('expenses')->orWhereHas('transactions')->orderBy('business_name')->get();
        }else{
            //pluck all project expense vendors
            $project_vendor_ids = Expense::where('project_id', $this->project)->groupBy('vendor_id')->pluck('vendor_id')->toArray();
            $vendors = Vendor::whereIn('id', $project_vendor_ids)->get();
        }
        $projects = Project::whereHas('expenses')->orderBy('created_at', 'DESC')->get();
        $distributions = Distribution::all();

        return view('livewire.expenses.index', [
            'expenses' => $expenses,
            'transactions' => $transactions,
            'vendors' => $vendors,
            'projects' => $projects,
            'distributions' => $distributions,
        ]);
    }
}


<?php

namespace App\Livewire\Transactions;

use App\Models\Vendor;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\Distribution;
use App\Models\TransactionBulkMatch;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Livewire\Component;
use Livewire\Attributes\Title;

class BulkMatch extends Component
{
    use AuthorizesRequests;

    public $split = FALSE;
    public $splits_count = 0;
    public $bulk_splits = [];

    public $vendor = NULL;
    public $vendor_id = NULL;
    public $distribution_id = NULL;
    public $modal_show = FALSE;
    public $amount = NULL;
    public $any_amount = FALSE;
    public $desc = NULL;
    public $amount_type = '=';
    public $vendor_amount_group = [];
    public $vendor_transactions = NULL;
    public $vendor_expenses = NULL;

    protected $listeners = ['refreshComponent' => '$refresh', 'addNewBulk', 'resetModal', 'manualMatch', 'bulkSplits', 'addSplit', 'removeSplit'];

    protected function rules()
    {
        return [
            'vendor_id' => 'required',
            'amount_type' => 'required',
            'distribution_id' => 'required_unless:split,true',
            'vendor_amount_group.*.checkbox' => 'nullable',
            'amount' => 'required_without:any_amount',
            // 'any_amount' => 'required_without:amount',
        ];
    }

    public function updated($field, $value)
    {
        if(substr($field, 0, 19) == 'vendor_amount_group'){
            //toggle checkmark
            $vendor_transactions_key = preg_replace("/[^0-9]/", '', $field);

            if($this->vendor_amount_group[$vendor_transactions_key]['checkbox'] == false){
                //remove from vendor_amount_group
                unset($this->vendor_amount_group[$vendor_transactions_key]);
            }
        }

        // if SPLIT checked vs if unchecked
        if($field == 'split'){
            if($this->split == TRUE){
                $this->distribution_id = NULL;
            }else{
                $this->bulk_splits = [];
            }
        }

        $this->validateOnly($field);
    }

    public function updatedVendorId($value)
    {
        $this->vendor = Vendor::findOrFail($value);
        $this->resetModal();

        // $this->modal_show = TRUE;
    }

    public function updatedAnyAmount($value)
    {
        $this->any_amount = $value;
        $this->amount = NULL;
    }

    public function updatedAmount($value)
    {
        if(empty($value)){
            $this->any_amount = FALSE;
            $this->amount = NULL;
        }
    }

    public function addNewBulk()
    {
        $this->resetModal();
        $this->modal_show = TRUE;
    }

    public function bulkSplits()
    {
        $this->bulk_splits = collect();
        $this->bulk_splits->push(['amount' => NULL, 'amount_type' => '$', 'distribution_id' => NULL]);
        $this->bulk_splits->push(['amount' => NULL, 'amount_type' => '$', 'distribution_id' => NULL]);
        $this->splits_count = 2;
    }

    public function addSplit()
    {
        $this->splits_count = $this->splits_count + 1;
        $this->bulk_splits->push(['amount' => NULL, 'amount_type' => '$', 'distribution_id' => NULL]);
    }

    public function removeSplit($index)
    {
        $this->splits_count = $this->splits_count - 1;
        unset($this->bulk_splits[$index]);
    }

    public function resetModal()
    {
        // $this->vendor = NULL;
        // $this->vendor_id = NULL;
        $this->distribution_id = NULL;
        $this->amount = NULL;
        $this->any_amount = FALSE;
        $this->amount_type = '=';
        $this->vendor_amount_group = [];
        $this->vendor_transactions = NULL;
        // $this->modal_show = FALSE;
        $this->desc = NULL;
        $this->split = FALSE;
        $this->splits_count = 0;
        $this->bulk_splits = [];
    }

    public function manualMatch()
    {
        if(empty($this->distribution_id)){
            //Does Not reset $this->payment_projects.*.AMOUNT
            $this->addError('distribution_id', 'Distribution is required.');
        }else{
            $manual_transactions = [];
            foreach($this->vendor_transactions as $key => $transaction){
                if(in_array($key, array_keys($this->vendor_amount_group))){
                    array_push($manual_transactions, $transaction);
                }
            }

            foreach($manual_transactions as $amount_transactions){
                foreach($amount_transactions as $amount_transaction){
                    $transaction = Transaction::findOrFail($amount_transaction['id']);

                    //create expene from transaction
                    $expense = Expense::create([
                        'amount' => $transaction->amount,
                        'date' => $transaction->transaction_date,
                        'project_id' => NULL,
                        'distribution_id' => $this->distribution_id,
                        'vendor_id' => $transaction->vendor_id,
                        'belongs_to_vendor_id' => auth()->user()->primary_vendor_id,
                        'created_by_user_id' => 0,
                    ]);

                    $transaction->expense_id = $expense->id;
                    $transaction->save();
                }
            }

            //refresh component
            $this->dispatch('refreshComponent');
            $this->vendor_amount_group = [];
            $this->vendor_transactions = NULL;
            $this->distribution_id = NULL;
            //send notification
        }
    }

    public function store()
    {
        // dd($this);
        // $this->validate();

        //any_amount isset? $amount = NULL, NULL = ANY
        if($this->any_amount == true){
            $amount = NULL;
            $options = NULL;
        }else{
            $amount = $this->amount;
            $options['amount_type'] = $this->amount_type;
        }

        if($this->desc){
            $options['desc'] = $this->desc;
        }else{
            $options['desc'] = NULL;
        }

        if(!empty($this->bulk_splits)){
            $options['splits'] = [];

            foreach($this->bulk_splits as $index => $split){
                //2 decimals required for percent %
                $options['splits'][$index]['amount'] = $split['amount_type'] == '%' ? '.' . preg_replace('/\./', '', $split['amount']) : $split['amount'];
                $options['splits'][$index]['amount_type'] = $split['amount_type'];
                $options['splits'][$index]['distribution_id'] = $split['distribution_id'];
            }
        }

        //create new BulkMatch ...
        $bulk_match =
            TransactionBulkMatch::create([
                'amount' => $amount,
                'vendor_id' => $this->vendor_id,
                'distribution_id' => $this->distribution_id,
                'options' => $options,
                'belongs_to_vendor_id' => auth()->user()->vendor->id,
            ]);

        // app('App\Http\Controllers\TransactionController')->transaction_vendor_bulk_match();

        $this->resetModal();
        $this->modal_show = FALSE;
    }

    #[Title('Bulk Transactions')]
    public function render()
    {
        $this->authorize('viewAny', TransactionBulkMatch::class);

        $bulk_matches =
            TransactionBulkMatch::with(['vendor', 'distribution'])
                ->get()
                ->sortBy(function($item, $key) {
                    return $item->vendor->business_name;
                });

        $distributions = Distribution::all();
        $transactions =
            Transaction::whereHas('vendor')->whereDoesntHave('expense')->whereNull('check_number')->whereNotNull('posted_date')->where('posted_date', '<', today()->subDays(3)->format('Y-m-d'))
                ->get()->groupBy('vendor_id');
        // dd($transactions);

        $expenses_no_project =
            Expense::whereHas('vendor')->whereDoesntHave('splits')->where('project_id', "0")->whereNull('distribution_id')
                ->get()->groupBy('vendor_id');
        // dd($expenses_no_project);
        // $vendor_ids_merged = $transactions->merge($expenses_no_project)->groupBy('vendor_id');
        // dd($vendor_ids_merged);
        $vendors = Vendor::whereIn('id', $transactions->keys())->orWhereIn('id', $expenses_no_project->keys())->where('business_type', 'Retail')->orderBy('business_name')->get();

        if($this->vendor){
            //transactions groupBy amount

            //02-24-2024 sortBy/orderBy count of transactions per amount/groupBy
            $this->vendor_transactions =
                $this->vendor->transactions()
                ->whereDoesntHave('expense')
                ->whereDoesntHave('check')
                ->orderBy('amount', 'DESC')
                ->get()
                ->groupBy('amount')
                ->values()
                //converts to array?
                ->toBase();
            // dd($this->vendor_transactions);

            $this->vendor_expenses =
                $this->vendor->expenses()
                ->whereDoesntHave('splits')
                ->where('project_id', "0")
                ->whereNull('distribution_id')
                ->orderBy('amount', 'DESC')
                ->get()
                ->groupBy('amount')
                ->toBase();
            // dd($this->vendor_expenses);
        }
        return view('livewire.transactions.bulk-match', [
            'bulk_matches' => $bulk_matches,
            'distributions' => $distributions,
            'transactions' => $transactions,
            'vendors' => $vendors,
        ]);
    }
}

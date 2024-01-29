<?php

namespace App\Livewire\Transactions;

use App\Models\Vendor;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\VendorTransaction;

use Livewire\Component;
use Livewire\Attributes\Title;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MatchVendor extends Component
{
    use AuthorizesRequests;

    public $vendors = [];
    public $merchant_names = [];
    public $match_merchant_names = [];
    public $match_vendor_names = [];
    public $view_text = [
        'card_title' => 'Save Transactions/Vendor',
        'button_text' => 'Sync Transactions & Vendors',
        'form_submit' => 'store',
    ];

    protected function rules()
    {
        return [
            'match_merchant_names.*.match_desc' => 'required',
            'match_merchant_names.*.vendor_id' => 'required',
        ];
    }

    public function mount()
    {
        $this->vendors = Vendor::withoutGlobalScopes()->orderBy('business_name', 'ASC')->where('business_type', 'Retail')->get();

        $this->match_vendor_names = Transaction::transactionsSinVendor()->get()->groupBy('plaid_merchant_name')->values()->toArray();
    }

    public function updated($field)
    {
        $this->validateOnly($field);
    }

    public function store()
    {
        $this->validate();
        // $this->authorize('create', Expense::class);

        foreach($this->match_merchant_names as $key => $vendor_match){
            if($vendor_match['vendor_id'] == "NEW"){
                //new Retail Vendor
                $vendor = Vendor::create([
                    'business_type' => 'Retail',
                    'business_name' => $vendor_match['match_desc'],
                ]);

                $vendor_id = $vendor->id;
            }else{
                if($vendor_match['vendor_id'] == "DEPOSIT"){
                    $deposit_check = 1;
                    $vendor_id = NULL;
                }elseif($vendor_match['vendor_id'] == "CHECK"){
                    $deposit_check = 2;
                    $vendor_id = NULL;
                }elseif($vendor_match['vendor_id'] == "TRANSFER"){
                    $deposit_check = 3;
                    $vendor_id = NULL;
                }elseif($vendor_match['vendor_id'] == "CASH"){
                    $deposit_check = 4;
                    $vendor_id = NULL;
                }else{
                    $deposit_check = NULL;
                    $vendor_id = $vendor_match['vendor_id'];
                }

                if(isset($vendor_match['bank_specific'])){
                    $institution_id = $this->merchant_names->values()[$key][0]['bank_account']['bank']['plaid_ins_id'];
                }else{
                    $institution_id = NULL;
                }

                if(isset($vendor_match['options'])){
                    $options = json_encode($vendor_match['options'] . '/i');
                }else{
                    $options = json_encode('/i');
                }

                $vendor_transaction = VendorTransaction::create([
                    'vendor_id' => $vendor_id,
                    'deposit_check' => $deposit_check,
                    'desc' => $vendor_match['match_desc'],
                    'plaid_inst_id' => $institution_id,
                    'options' => $options,
                ]);
            }

            //USED IN MULTIPLE OF PLACES TransactionController@add_vendor_to_transactions, ExpesnesForm@createExpenseFromTransaction
            //add if vendor is not part of the currently logged in vendor
            if(!in_array($vendor_id, $this->vendors->pluck('id')->toArray())){
                auth()->user()->vendor->vendors()->attach($vendor_id);
            }
        }

        //add vendor to transaction ...

        //6-8-2022 run in a queue?
        app('App\Http\Controllers\TransactionController')->add_vendor_to_transactions();
        app('App\Http\Controllers\TransactionController')->add_check_deposit_to_transactions();

        return redirect(route('transactions.match_vendor'));
    }

    #[Title('Match Transaction/Vendor')]
    public function render()
    {
        $this->authorize('viewAny', TransactionBulkMatch::class);

        $transaction_bank_accounts = BankAccount::withoutGlobalScopes()->whereNull('deleted_at')->pluck('id')->toArray();
        $this->merchant_names =
            Transaction::transactionsSinVendor()
                ->whereIn('bank_account_id', $transaction_bank_accounts)
                ->with([
                    'bank_account' => fn ($query) => $query->withoutGlobalScopes()->with([
                        'bank' => fn ($query) => $query->withoutGlobalScopes()->with([
                            'vendor' => fn ($query) => $query->withoutGlobalScopes()
                        ])
                    ])
                ])
                ->get()->groupBy('plaid_merchant_description')
                ->toBase();

        return view('livewire.transactions.match-vendor', [
            'merchant_names' => $this->merchant_names,
        ]);
    }
}

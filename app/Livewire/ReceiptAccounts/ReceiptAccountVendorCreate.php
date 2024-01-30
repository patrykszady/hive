<?php

namespace App\Livewire\ReceiptAccounts;

use App\Models\Vendor;
use App\Models\Distribution;
use App\Models\ReceiptAccount;

use Livewire\Component;
use Livewire\Attributes\Validate;

class ReceiptAccountVendorCreate extends Component
{
    protected $listeners = ['refreshComponent' => '$refresh', 'editReceiptVendor'];

    public $distributions = [];
    public $distribution_id = NULL;
    public $vendors = [];
    public $vendor = NULL;
    public $modal_show = FALSE;

    protected function rules()
    {
        return [
            'distribution_id' => 'required',
        ];
    }

    public function mount()
    {
        $this->distributions = Distribution::all();
    }

    public function editReceiptVendor($vendor_id)
    {
        $this->vendor = Vendor::find($vendor_id);

        if(!$this->vendor->receipt_accounts->isEmpty()){
            $receipt_account = $this->vendor->receipt_accounts->first();
            if(!is_null($receipt_account->distribution_id)){
                $this->distribution_id = $receipt_account->distribution_id;
            }else{
                $this->distribution_id = 'NO_PROJECT';  
            }
        }else{
            $this->distribution_id = NULL;  
        }

        $this->modal_show = TRUE;
    }

    public function store()
    {
        $this->validate();

        if(is_numeric($this->distribution_id)){
            $distribution_id = $this->distribution_id;
            $project_id = NULL;
        }else{
            $distribution_id = NULL;
            $project_id = 0;
        }

        if($this->vendor->receipt_accounts->isEmpty()){
            //create new
            $receipt_account = new ReceiptAccount();
            $receipt_account->project_id = $project_id;
            $receipt_account->distribution_id = $distribution_id;
            $receipt_account->belongs_to_vendor_id = auth()->user()->vendor->id;
            $receipt_account->vendor_id = $this->vendor->id;
            $receipt_account->save();
        }else{
            //edit existing
            $receipt_account = $this->vendor->receipt_accounts->first();
            $receipt_account->project_id = $project_id;
            $receipt_account->distribution_id = $distribution_id;
            $receipt_account->save();
        }

        $this->modal_show = FALSE;
        $this->dispatch('refreshComponent')->to('receipt-accounts.receipt-accounts-index');
        $this->dispatch('notify',
            type: 'success',
            content: 'Receipt Account Connected'
        );
    }

    public function render()
    {
        // $this->authorize('create', Expense::class);
        return view('livewire.receipt-accounts.vendor-create');
    }
}

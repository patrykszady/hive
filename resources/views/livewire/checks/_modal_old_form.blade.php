


<x-cards.wrapper class="" :class="'space-y-4 my-4 max-w-2xl'">           
    <x-cards.heading>
        <x-slot name="left">
            <h1>Check Info</h1>
        </x-slot>

        <x-slot name="right">
            <x-cards.button href="#" wire:click="$emit('newCheck')">
                {{$has_check_indication == true ? 'Edit Check Info' : 'Add Check Info'}}
            </x-cards.button>      
        </x-slot>
    </x-cards.heading>
    <div 
        x-data="{ open: @entangle('has_check_indication') }" 
        x-show="open" 
        x-transition.duration.150ms
        >
        @if($check)
            <x-sections.section :cols="3">
                <x-sections.item :title="'Check Number'" :details="$check['check_number']"></x-sections.item>
                <x-sections.item :title="'Check Type'" :details="$check['check_type']"></x-sections.item>
                <x-sections.item :title="'Bank'" :details="$bank_account->getNameAndType()"></x-sections.item>
            </x-sections.section>
        @endif
    </div>
</x-cards.wrapper>
<div 
    x-data="{ open: @entangle('has_check_indication') }" 
    x-show="open" 
    x-transition.duration.150ms
    >
    <x-cards.wrapper class="" :class="'space-y-4 my-4 max-w-2xl'">   
        <x-cards.heading>
            <x-slot name="left">
                <h1>Create Vendor Check</h1>
            </x-slot>

            <x-slot name="right">
                <button 
                    type="submit"
                    {{-- x-bind:disabled="expense.project_id" --}}
                    class="ml-3 inline-flex justify-center disabled:opacity-50 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                    Create Vendor Check
                </button>   
            </x-slot>
        </x-cards.heading>
    </x-cards.wrapper>
</div>

@livewire('checks.checks-form', ['payment_type' => $vendor])



{{-- disabled="" --}}
{{-- {{is_null($project_id) ? 'disabled' : ''}} --}}
{{--  {{is_null($project_id) ? 'disabled="true"' : ''}} --}}
{{-- is_disabled="{{$project_id = true ? true : false}}" --}}
{{-- is_disabled="{{is_null($this->project_id) ? 'true' : 'false'}}" --}}





{{-- <x-modals.modal> --}}
    <form wire:submit.prevent="{{$view_text['form_submit']}}" class="space-y-2"> 
        <x-cards.heading>
            <x-slot name="left">
                <h1>Expense Payment Info</h1>
            </x-slot>
        
            <x-slot name="right">
            </x-slot>
        </x-cards.heading>

        <x-cards.body>
            <x-forms.row
                wire:model="check.bank_account_id"
                errorName="check.bank_account_id"
                name="check.bank_account_id"
                text="Bank"
                type="dropdown"
                >

                <option value="" readonly>Select Bank</option>

                @foreach ($bank_accounts as $index => $bank_account)
                    <option value="{{$bank_account->id}}">
                        {{$bank_account->getNameAndType()}}
                    </option>
                @endforeach
            </x-forms.row>

            <div 
                x-data="{ open: @entangle('check.bank_account_id') }" 
                x-show="open" 
                x-transition.duration.150ms
                class="space-y-2 mt-2"
                >
                <x-forms.row
                    wire:model="check.check_type"
                    errorName="check.check_type" 
                    name="check.check_type"
                    text="Type" 
                    type="dropdown"
                >
                    <option value="" readonly x-text="'Select Payment Type'"></option>
                    <option value="Check" x-text="'Check'"></option>
                    <option value="Transfer" x-text="'Transfer'"></option>
                    <option value="Cash" x-text="'Cash'"></option>
                </x-forms.row>
            
                <div 
                    x-data="{ open: @entangle('check_input') }" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >
                    <x-forms.row 
                        wire:model="check.check_number" 
                        errorName="check.check_number" 
                        name="check.check_number" 
                        text="Check Number"
                        type="number" 
                        placeholder="Check Number"
                        inputmode="numeric" 
                        step="1"
                    >
                    </x-forms.row>
                </div>
            </div>
        </x-cards.body>
        
        <x-cards.footer>
            <button 
                type="button"
                x-on:click="open = false"
                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                > 
                Cancel
            </button>

            <button 
                {{-- disabled="disabled" --}}
                {{-- x-on:click="open = false" --}}
                type="submit"
                class="ml-3 inline-flex justify-center disabled:opacity-50 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                {{$view_text['button_text']}}
            </button>
        </x-cards.footer>
    </form>  
{{-- </x-modals.modal> --}}
















<?php

namespace App\Http\Livewire\Checks;

use App\Models\Check;
use App\Models\BankAccount;

use Livewire\Component;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChecksForm extends Component
{
    use AuthorizesRequests;

    // public Check $check;
    public $check = NULL;

    public $modal_show = NULL;
    public $bank_accounts = NULL;
    public $check_input = NULL;

    public $payment_type = NULL;

    protected $listeners = ['newCheck'];

    protected function rules()
    {
        return [
            'check.bank_account_id' => 'required',
            'check.check_type' => 'required_with:bank_account_id',
            //1/3/2022 check_number is unique on Checks table where bank_account_id and check_number must be unique
            'check.check_number' => 'required_if:check.check_type,Check',
            'check_input' => 'nullable',
        ];
    }
    
    protected $messages = 
    [
        'check.check_number' => 'Check Number is required if Payment Type is Check',
    ];

    public function mount()
    {              
        //where ACtive on bank
        // dd(BankAccount::with('bank')->get());
        $this->bank_accounts = BankAccount::with('bank')->where('type', 'Checking')
        ->whereHas('bank', function ($query) {
            return $query->whereNotNull('plaid_access_token');
        })->get();

        if(isset($this->check)){          
            $this->view_text = [
                // 'card_title' => 'Update user',
                'button_text' => 'Update Payment',
                'form_submit' => 'store',             
            ];

            $this->check_input = TRUE;
        }else{
            $this->check = Check::make();
            
            $this->view_text = [
                // 'card_title' => 'Update user',
                'button_text' => 'Save Payment',
                'form_submit' => 'store',             
            ];
        }
    }

    public function updated($field) 
    {
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

    public function store()
    {
        $this->validate();
        
        if($this->payment_type->getTable() == 'vendors'){
            // dd($this->payment_type->getTable());
            // dd('vendors table');
            //send to VendorPaymentForm
            // dd($this->check);
            $this->emit('vendorHasCheck', $this->check);
        }elseif($this->payment_type->getTable() == 'expenses'){
            $this->emit('hasCheck', $this->check);
        }
        
        $this->modal_show = NULL;
    }

    public function newCheck()
    {
        dd('in newcheck');
        $this->modal_show = TRUE;
    }
    
    public function render()
    {
        return view('livewire.checks.form');
    }
}

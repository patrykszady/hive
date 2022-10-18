{{-- form classes divide-y divide-gray-200 --}}
<div class="xl:relative max-w-xl lg:max-w-5xl sm:px-6 mx-auto">
    <form wire:submit.prevent="{{$view_text['form_submit']}}">
        <x-cards.wrapper class="max-w-3xl mx-auto">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                <x-slot name="right">
                    <x-cards.button href="{{route('expenses.index')}}">
                        All Expenses
                    </x-cards.button>

                    @if(request()->routeIs('expenses.edit'))
                        <x-cards.button href="{{route('expenses.show', $expense->id)}}">
                            Show Expense
                        </x-cards.button>
                    @endif
                </x-slot>
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- AMOUNT --}}
                <x-forms.row 
                    wire:model.debounce.500ms="expense.amount" 
                    errorName="expense.amount" 
                    name="expense.amount"
                    text="Amount"
                    type="number" 
                    hint="$" 
                    textSize="xl" 
                    placeholder="00.00" 
                    inputmode="numeric" 
                    step="0.01"
                    autofocus
                    > 
                </x-forms.row>

                {{-- existing expenses/transactions match from expense.amount --}}
                <div 
                    x-data="{open: @entangle('expense.amount')}" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >

                    {{-- EXPENSES FOUND --}}
                    @if(!is_null($expenses_found) || !is_null($transactions_found))
                        @if(!is_null($expenses_found))
                            <x-misc.hr>
                                Choose Existing Expense
                            </x-misc.hr>
                                <x-lists.ul :class="'mt-4'">
                                    @foreach ($expenses_found as $expense_found)
                                        @php
                                            $line_details = [
                                                1 => [
                                                    'text' => $expense_found->date->format('m/d/Y'),
                                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                            
                                                    ],
                                                2 => [
                                                    'text' => $expense_found->vendor->business_name,
                                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                            
                                                    ],
                                                3 => [
                                                    'text' => $expense_found->distribution ? $expense_found->distribution->name : $expense_found->project->name,
                                                    'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                            
                                                    ],
                                                ];
                                        @endphp
                            
                                        <x-lists.search_li
                                            href="{{route('expenses.show', $expense_found->id)}}"
                                            :line_details="$line_details"
                                            :line_title="money($expense_found->amount)"
                                            :bubble_message="'Expense'"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            @if(is_null($transactions_found))
                                <x-misc.hr>
                                    Or Create New Expense
                                </x-misc.hr>
                            @endif
                        @endif

                        @if(!is_null($transactions_found))
                            <x-misc.hr>
                                Choose Existing Transaction
                            </x-misc.hr>
                                <x-lists.ul :class="'mt-4'">
                                    @foreach ($transactions_found as $transaction_found)
                                        @php

                                            $vendor_name = $transaction_found->vendor->business_name == "No Vendor" ? "NO VENDOR | Maybe: " . $transaction_found->plaid_merchant_name : $transaction_found->vendor->business_name;
                                            $line_details = [
                                                1 => [
                                                    'text' => $transaction_found->transaction_date->format('m/d/Y'),
                                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                            
                                                    ],
                                                2 => [

                                                // . $transaction_found->plaid_merchant_name
                                                //
                                                    'text' => $vendor_name,
                                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                            
                                                    ],
                                                3 => [
                                                    'text' => $transaction_found->bank_account->bank->name,
                                                    'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z'
                            
                                                    ],
                                                ];
                                        @endphp

                                        <x-lists.search_li
                                            href="#"
                                            wire:click="$emit('createExpenseFromTransaction', {{$transaction_found->id}})"
                                            :line_details="$line_details"
                                            :line_title="money($transaction_found->amount)"
                                            :bubble_message="'Transaction'"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            <x-misc.hr>
                                Or Create New Expense
                            </x-misc.hr>
                        @endif
                    @endif
                </div>

                {{-- DATE --}}
                <div 
                    x-data="{open: @entangle('expense.amount')}" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >
                    <x-forms.row 
                        wire:model.debounce.500ms="expense.date" 
                        errorName="expense.date" 
                        name="date" 
                        text="Date" 
                        type="date"
                    >
                    </x-forms.row>
                </div>

                {{-- VENDOR --}}
                <div 
                    x-data="{open: @entangle('expense.date')}" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >
                    <x-forms.row 
                        wire:model.debounce.250ms="expense.vendor_id" 
                        errorName="expense.vendor_id" 
                        name="vendor_id" 
                        text="Vendor"
                        type="dropdown"
                    >
                        <option value="" readonly>Select Vendor</option>
                        @foreach ($vendors as $index => $vendor)
                            <option value="{{$vendor->id}}">{{$vendor->business_name}}</option>
                        @endforeach
                    </x-forms.row>
                </div>
            
                {{-- PROJECT --}}
                <div 
                    x-data="{ open: @entangle('expense.vendor_id'), split: @entangle('split') }" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >
                    <x-forms.row 
                        wire:model="expense.project_id" 
                        x-bind:disabled="split"
                        errorName="expense.project_id" 
                        name="project_id" 
                        text="Project" 
                        type="dropdown" 
                        radioHint="Split"
                        >

                        {{-- default $slot x-slot --}}
                        <option 
                            value="" 
                            readonly 
                            x-text="split == true || split == 'true' ? 'Expense is Split' : 'Select Project'"
                            >
                        </option>

                        @foreach ($projects as $index => $project)
                            <option 
                                value="{{$project->id}}"
                                >
                                {{$project->name}}
                            </option>
                        @endforeach

                        <option disabled>----------</option>
                        
                        @foreach ($distributions as $index => $distribution)
                            <option 
                                value="D:{{$distribution->id}}"
                                >
                                {{$distribution->name}}
                            </option>
                        @endforeach

                        <x-slot name="radio">
                            <input 
                                wire:model="split" 
                                id="split" 
                                name="split" 
                                value="true" 
                                type="checkbox"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded ml-2"
                                >
                        </x-slot>
                    </x-forms.row>
                </div>

                {{-- SPLITS --}}
                <div 
                    x-data="{ open: @entangle('split'), splits: @entangle('splits') }" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >
        
                    <x-forms.row
                        wire:click="$emit('addSplits', {{$expense->amount}})"
                        errorName="" 
                        name=""
                        text="Splits"
                        type="button"
                        {{-- buttonText="Add Splits"  --}}
                        {{-- IF has splits VS no splits --}}
                        x-text="splits == true ? 'Edit Splits' : 'Add Splits'"
                        >    
                    </x-forms.row>
                </div>

                {{-- 04-09-2022 SHOW ALL SPLITS IN A UL/LI --}}

                {{-- PAID BY --}}
                <div 
                    x-data="{ open: @entangle('expense.project_id'), splits: @entangle('splits'), split: @entangle('split') }" 
                    x-show="splits && split || open" 
                    x-transition.duration.150ms
                    >
                    <x-forms.row 
                        wire:model="expense.paid_by" 
                        errorName="expense.paid_by" 
                        name="paid_by" 
                        text="Paid By"
                        type="dropdown"
                        >

                        <option value="" readonly>{{auth()->user()->vendor->business_name}}</option>
                            @foreach ($employees as $employee)
                                <option value="{{$employee->id}}">{{$employee->first_name}}</option>
                            @endforeach
                    </x-forms.row>
                </div>

                {{-- @include('livewire.checks._payment_form') --}}

                {{-- PAYMENT --}}
                {{-- <div 
                    x-data="{ open: @entangle('expense.project_id'), splits: @entangle('splits'), split: @entangle('split'), has_check_indication: @entangle('has_check_indication') }" 
                    x-show="splits && split || open"
                    x-transition.duration.150ms
                    >
                    <x-forms.row
                        wire:click="$emit('newCheck')"
                        errorName="" 
                        name=""
                        text="Check"
                        type="button"
                        x-text="has_check_indication == true ? 'Edit Payment Info' : 'Add Payment Info'"
                        >    
                    </x-forms.row>
                </div> --}}

                {{-- CHECKS --}}
                <div 
                    x-data="{ open: @entangle('expense.paid_by'), openproject: @entangle('expense.project_id'), splits: @entangle('splits') }" 
                    x-show="(openproject || splits) && !open" 
                    x-transition.duration.150ms
                    >

                    @include('livewire.checks._include_form')
                </div>

                {{-- RECEIPT --}}
                <div 
                    x-data="{ open: @entangle('expense.project_id'), splits: @entangle('splits'), split: @entangle('split') }" 
                    x-show="splits && split || open" 
                    x-transition.duration.150ms
                    >
                    <x-forms.row 
                        wire:model="receipt_file" 
                        errorName="receipt_file" 
                        name="receipt_file" 
                        text="Receipt" 
                        type="file"
                        >
                        
                        <x-slot name="titleslot">
                            @if($expense->receipts()->exists())
                                {{-- <input wire:model="existing_receipts" type="hidden" value="123"> --}}
                                <p class="mt-2 text-sm text-green-600" wire:loaded wire:target="receipt_file">Receipt Uploaded</p>                            
                            @endif
                            <p class="mt-2 text-sm text-green-600" wire:loading wire:target="receipt_file">Uploading...</p>
                        </x-slot>  
                    </x-forms.row>
                </div>

                {{-- REIMBURSPEMNT --}}
                <div 
                    x-data="{ open: @entangle('expense.project_id') }" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >
                    <x-forms.row wire:model.lazy="expense.reimbursment" errorName="expense.reimbursment" name="reimbursment"
                        text="Reimbursment" type="dropdown">
                        <option value="" x-bind:selected="split == true ? true : false">None</option>
                        <option value="Client">Client</option>
                    </x-forms.row>
                </div>

                {{-- PO/INVOICE --}}
                <div 
                    x-data="{ open: @entangle('expense.project_id'), splits: @entangle('splits'), split: @entangle('split') }" 
                    x-show="splits && split || open"
                    x-transition.duration.150ms
                    >
                    <x-forms.row 
                        wire:model.lazy="expense.invoice" 
                        errorName="expense.invoice" 
                        name="invoice" 
                        text="Invoice"
                        type="text" 
                        placeholder="Invoice/PO"
                        >
                    </x-forms.row>
                </div>

                {{-- NOTES --}}
                <div 
                    x-data="{ open: @entangle('expense.project_id'), splits: @entangle('splits'), split: @entangle('split') }" 
                    x-show="splits && split || open"
                    x-transition.duration.150ms
                    >
                    <x-forms.row 
                        wire:model.lazy="expense.note" 
                        errorName="expense.note" 
                        name="note" 
                        text="Note" 
                        type="textarea"
                        rows="1" 
                        placeholder="Notes about this expense.">
                    </x-forms.row>
                </div>
            </x-cards.body>
            
            {{-- FOOTER --}}
            <div 
                x-data="{ open: @entangle('expense.project_id'), split: @entangle('split') }" 
                x-show="split || open" 
                x-transition.duration.150ms
                >
                <x-cards.footer>
                    <button 
                        type="button"
                        {{-- class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" --}}
                        > 
                        {{-- Cancel --}}
                    </button>
                    <button 
                        type="submit"
                        {{-- x-bind:disabled="expense.project_id" --}}
                        class="ml-3 inline-flex justify-center disabled:opacity-50 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{$view_text['button_text']}}
                    </button>
                </x-cards.footer>
            </div>
        </x-cards.wrapper>
    </form>
</div>

{{-- SPLITS MODAL --}}
@livewire('expenses.expense-splits-form', ['expense_splits' => $expense_splits])
<div 
    x-data="{open: @entangle('new')}" 
    x-show="open" 
    x-transition.duration.250ms
    >

    <form wire:submit.prevent="{{$view_text['form_submit']}}">
        <x-cards.wrapper class="max-w-2xl mx-auto">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                <x-slot name="right">
                    @if(isset($expense->id))
                        <x-cards.button href="{{route('expenses.show', $expense->id)}}" target="_blank">
                            Show Expense
                        </x-cards.button>
                    @endif
                </x-slot>
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- AMOUNT --}}
                <x-forms.row 
                    wire:model="expense.amount" 
                    errorName="expense.amount" 
                    name="amount"
                    text="Amount"
                    type="number" 
                    hint="$" 
                    textSize="xl" 
                    disabled
                    > 
                </x-forms.row>

                {{-- DATE --}}
                <x-forms.row 
                    wire:model.debounce.500ms="expense.date" 
                    errorName="expense.date" 
                    name="date" 
                    text="Date" 
                    type="date"
                    autofocus
                    >
                </x-forms.row> 

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
                        @foreach ($vendors as $vendor)
                            <option value="{{$vendor->id}}">{{$vendor->name}}</option>
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
                        wire:model.debounce.250ms="expense.project_id" 
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

                {{-- SPLITS--}}
                <div 
                    x-data="{ open: @entangle('split'), splits: @entangle('splits') }" 
                    x-show="open" 
                    x-transition.duration.250ms
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
                    {{-- SPLITS MODAL --}}
                    @livewire('expenses.expense-splits-form', ['expense_splits' => $expense_splits])
                </div>

                {{-- 04-09-2022 SHOW ALL SPLITS IN A UL/LI --}}

                {{-- PAID BY --}}
                <div 
                    x-data="{ open: @entangle('expense.project_id'), splits: @entangle('splits'), split: @entangle('split') }" 
                    x-show="splits && split || open" 
                    x-transition.duration.250ms
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

                {{-- CHECKS --}}
                <div 
                    x-data="{ open: @entangle('expense.paid_by'), openproject: @entangle('expense.project_id'), splits: @entangle('splits') }" 
                    x-show="(openproject || splits) && !open" 
                    x-transition.duration.250ms
                    >

                    @include('livewire.checks._include_form')
                </div>

                {{-- RECEIPT --}}
                <div 
                    x-data="{ open: @entangle('expense.project_id'), splits: @entangle('splits'), split: @entangle('split') }" 
                    x-show="splits && split || open" 
                    x-transition.duration.250ms
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
                    x-transition.duration.250ms
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
                    x-transition.duration.250ms
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
                    x-transition.duration.250ms
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
                x-transition.duration.250ms
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
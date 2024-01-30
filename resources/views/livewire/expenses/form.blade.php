<x-modals.modal>
    <form wire:submit="{{$view_text['form_submit']}}">
        <x-cards.heading>
            <x-slot name="left">
                <h1>{{$view_text['card_title']}}</h1>
            </x-slot>
            <x-slot name="right">
                @if(isset($form->expense->id))
                    <x-cards.button href="{{route('expenses.show', $form->expense->id)}}" target="_blank">
                        Show Expense
                    </x-cards.button>
                @endif
            </x-slot>
        </x-cards.heading>

        {{-- ROWS --}}
        <x-cards.body :class="'space-y-4 my-4'">
            {{-- AMOUNT --}}
            <div
                x-data="{ amount: @entangle('form.amount'), save_form: @entangle('view_text.form_submit'), expense_transactions: @entangle('form.expense_transactions_sum') }"
                >
                <x-forms.row
                    wire:model.live.debounce.500ms="form.amount"
                    errorName="form.amount"
                    name="form.amount"
                    text="Amount"
                    type="number"
                    hint="$"
                    textSize="xl"
                    placeholder="00.00"
                    inputmode="decimal"
                    {{-- pattern="[-+,0-9.]*" --}}
                    step="0.01"
                    autofocus
                    {{-- disabled if $amount isset... AND not in Edit... or if expense transactions are complete/= expense.amount --}}
                    {{-- expense_transactions ||  --}}
                    x-bind:disabled="save_form == 'save'"
                    >
                </x-forms.row>
            </div>

            {{-- DATE --}}
            <x-forms.row
                wire:model.live.debounce.500ms="form.date"
                errorName="form.date"
                name="form.date"
                text="Date"
                type="date"
                autofocus
                >
            </x-forms.row>

            {{-- VENDOR --}}
            <x-forms.row
                wire:model.live="form.vendor_id"
                errorName="form.vendor_id"
                name="form.vendor_id"
                text="Vendor"
                type="dropdown"
                >
                <option value="" readonly>Select Vendor</option>
                @foreach ($vendors as $vendor)
                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                @endforeach

                <x-slot name="bottom">
                    @if(isset($form->merchant_name))
                        <span class="mt-2 text-sm text-black-600"><i>{{$form->merchant_name}}</i></span>
                    @endif
                </x-slot>
                {{-- @if((is_null($expense->vendor_id) AND isset($transaction->plaid_merchant_description)) OR isset($expense->note))
                    <x-slot name="bottom">
                        @if(isset($transaction->plaid_merchant_name))
                            <p class="mt-2 text-sm text-black-600">Name: {{$transaction->plaid_merchant_name}}</p>
                        @endif
                        @if(isset($transaction->plaid_merchant_description))
                            <p class="mt-2 text-sm text-black-600">Desc: {{$transaction->plaid_merchant_description}}</p>
                        @endif
                        @if(isset($expense->note))
                            <p class="mt-2 text-sm text-black-600">Maybe: {{$expense->note}}</p>
                        @endif
                    </x-slot>
                @endif --}}
            </x-forms.row>

            {{-- PROJECT --}}
            <div
                x-data="{ open: @entangle('form.vendor_id'), split: @entangle('split') }"
                x-show="open"
                x-transition
                >
                <x-forms.row
                    wire:model.live="form.project_id"
                    x-bind:disabled="split"
                    errorName="form.project_id"
                    name="form.project_id"
                    text="Project"
                    type="dropdown"
                    radioHint="Split"
                    >

                    {{-- default $slot x-slot --}}
                    <option
                        value=""
                        readonly
                        x-text="split ? 'Expense is Split' : 'Select Project'"
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
                            wire:model.live="split"
                            id="split"
                            name="split"
                            {{-- value="true" --}}
                            type="checkbox"
                            class="w-4 h-4 ml-2 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            >
                    </x-slot>

                    @if(isset($form->notes))
                        <x-slot name="bottom">
                            <span class="mt-2 text-sm text-black-600"><i>{{$form->notes}}</i></span>
                        </x-slot>
                    @endif
                </x-forms.row>
            </div>

            {{-- SPLITS --}}
            <div
                x-data="{ open: @entangle('split'), splits: @entangle('splits'), total: @entangle('form.amount')}"
                x-show="open"
                x-transition
                >

                <x-forms.row
                    wire:click="$dispatchTo('expenses.expense-splits-create', 'addSplits', { expense_total: total, expense: {{$expense}} })"
                    errorName=""
                    name=""
                    text="Splits"
                    type="button"
                    {{-- IF has splits VS no splits --}}
                    x-text="splits == true ? 'Edit Splits' : 'Add Splits'"
                    >
                </x-forms.row>

                {{-- SPLITS MODAL --}}
                <livewire:expenses.expense-splits-create :projects="$projects" :distributions="$distributions" />
            </div>

            {{-- PAID BY --}}
            <div
                x-data="{ open: @entangle('form.project_id'), splits: @entangle('splits'), split: @entangle('split') }"
                x-show="splits && split || open"
                x-transition
                >
                <x-forms.row
                    wire:model.live="form.paid_by"
                    errorName="form.paid_by"
                    name="form.paid_by"
                    text="Paid By"
                    type="dropdown"
                    >

                    <option value="" readonly>{{auth()->user()->vendor->business_name}}</option>
                    @foreach ($employees as $employee)
                        <option value="{{$employee->id}}">{{$employee->first_name}}</option>
                    @endforeach
                </x-forms.row>
            </div>

            {{-- CHECK --}}
            <div
                x-data="{ open: @entangle('form.paid_by'), openproject: @entangle('form.project_id'), splits: @entangle('splits') }"
                x-show="(openproject || splits) && !open"
                x-transition
                >

                @include('livewire.checks.form')
                {{-- :form="$checkform" --}}
                {{-- <livewire:checks.check-create /> --}}
            </div>

            {{-- RECEIPT --}}
            <div
                x-data="{ open: @entangle('form.project_id'), splits: @entangle('splits'), split: @entangle('split') }"
                x-show="splits && split || open"
                x-transition
                >
                <x-forms.row
                    wire:model="form.receipt_file"
                    errorName="form.receipt_file"
                    name="receipt_file"
                    text="Receipt"
                    type="file"
                    >

                    <x-slot name="titleslot">
                        <div x-data="{ receipts: @entangle('form.receipts')}" x-show="receipts">
                            <span class="mt-2 text-sm text-green-600">Receipt Existing.</span>
                        </div>
                        <div x-data="{ receipt: @entangle('form.receipt_file')}" x-show="receipt" wire:loading.remove wire:target="form.receipt_file">
                            <span class="mt-2 text-sm text-green-600" wire:loaded wire:target="form.receipt_file">Receipt Uploaded.</span>
                        </div>
                        <span class="mt-2 text-sm text-green-600" wire:loading wire:target="form.receipt_file">Uploading...</span>
                    </x-slot>
                </x-forms.row>
            </div>

            {{-- REIMBURSEMNT --}}
            <div
                x-data="{ open: @entangle('form.project_id'), project_completed: @entangle('form.project_completed') }"
                x-show="open"
                x-transition
                >
                <x-forms.row
                    wire:model.live="form.reimbursment"
                    errorName="form.reimbursment"
                    name="reimbursment"
                    text="Reimbursment"
                    type="dropdown"
                    >
                    <option value="" x-bind:selected="split == true ? true : false">None</option>
                    {{-- disabled if project is Complete --}}
                    <option value="Client" x-bind:disabled="project_completed">Client</option>
                    @foreach ($via_vendor_employees as $employee)
                        <option value="{{$employee->id}}">{{$employee->first_name}}</option>
                    @endforeach
                </x-forms.row>
            </div>
            {{-- <x-forms.error errorName="testtest" /> --}}

            {{-- PO/INVOICE --}}
            <div
                x-data="{ open: @entangle('form.project_id'), splits: @entangle('splits'), split: @entangle('split') }"
                x-show="splits && split || open"
                x-transition
                >
                <x-forms.row
                    wire:model.blur="form.invoice"
                    errorName="form.invoice"
                    name="form.invoice"
                    text="Invoice"
                    type="text"
                    placeholder="Invoice/PO"
                    >
                </x-forms.row>
            </div>

            {{-- NOTES --}}
            <div
                x-data="{ open: @entangle('form.project_id'), splits: @entangle('splits'), split: @entangle('split') }"
                x-show="splits && split || open"
                x-transition
                >
                <x-forms.row
                    wire:model.blur="form.note"
                    errorName="form.note"
                    name="form.note"
                    text="Note"
                    type="textarea"
                    rows="1"
                    placeholder="Notes about this expense.">
                </x-forms.row>
            </div>
        </x-cards.body>

        <x-cards.footer>
            <button
                {{-- wire:click="$emitTo('expenses.expenses-new-form', 'resetModal')" --}}
                type="button"
                x-on:click="open = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                Cancel
            </button>
            @if($form->amount == '0.00' || ($form->expense_transactions_sum == FALSE && $form->transaction == NULL))
                <button
                    type="button"
                    wire:click="remove"
                    {{-- wire:confirm.prompt="Are you sure you want to delete this line item?\n\nType DELETE to confirm|DELETE" --}}
                    x-on:click="open = false"
                    class="px-4 py-2 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                    >
                    Remove
                </button>
            @endif

            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                {{$view_text['button_text']}}
            </button>
        </x-cards.footer>
    </form>
</x-modals.modal>

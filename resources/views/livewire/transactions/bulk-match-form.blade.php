<x-modals.modal>
    <form wire:submit="store">
        {{-- HEADER --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Add New Automatic Vendor / Transaction</h1>
            </x-slot>
            <x-slot name="right">
            </x-slot>
        </x-cards.heading>

        {{-- ROWS --}}
        <x-cards.body :class="'space-y-4 my-4'">
            {{-- VENDOR --}}
            <x-forms.row
                wire:model.live.debounce.250ms="vendor_id"
                errorName="vendor_id"
                name="vendor_id"
                text="Vendor"
                type="dropdown"
                >
                <option value="" readonly>Select Vendor</option>
                @foreach ($vendors as $vendor)
                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                @endforeach
            </x-forms.row>

            {{-- checkbox to left ? --}}
            <div
                x-data="{ open: @entangle('vendor').live, any_amount: @entangle('any_amount').live, amount: @entangle('amount').live}"
                x-show="open"
                x-transition.duration.150ms
                >
                <x-forms.row
                    wire:model.live="amount"
                    errorName="amount"
                    name="amount"
                    text="Amount"
                    type="number"
                    hint="$"
                    textSize="xl"
                    placeholder="00.00"
                    inputmode="decimal"
                    x-bind:disabled="any_amount"
                    {{-- pattern="[-+,0-9.]*" --}}
                    step="0.01"
                    autofocus
                    radioHint="Any $"
                    {{-- x-bind:disabled="amount_disabled" --}}
                    >
                    <x-slot name="hint_dropdown">
                        <label for="amount_type" class="sr-only">Country</label>
                        <select
                            wire:model.live="amount_type"
                            errorName="amount_type"
                            x-bind:disabled="any_amount"
                            id="amount_type"
                            name="amount_type"
                            autocomplete="amount_type"
                            class="h-full py-0 pl-3 text-gray-500 bg-transparent border-0 rounded-md pr-7 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                            >
                            <option value="=">=</option>
                            <option value=">=">>=</option>
                            <option value="<="><=</option>
                            <option value=">">></option>
                            <option value="<"><</option>
                        </select>
                    </x-slot>
                    <x-slot name="radio">
                        <input
                            wire:model.live="any_amount"
                            x-bind:disabled="amount"
                            id="any_amount"
                            name="any_amount"
                            value="true"
                            type="checkbox"
                            class="w-4 h-4 ml-2 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            >
                    </x-slot>
                </x-forms.row>

                <br>

                <div
                    x-data="{ split: @entangle('split') }"
                    >
                    <x-forms.row
                        wire:model.live="distribution_id"
                        x-bind:disabled="split"
                        errorName="distribution_id"
                        name="distribution_id"
                        text="Distribution"
                        type="dropdown"
                        radioHint="Split"
                        >

                        <option
                            value=""
                            readonly
                            x-text="split ? 'Bulk Match is Split' : 'Select Distribution'"
                            >
                        </option>

                        @foreach ($distributions as $distribution)
                            <option
                                value="{{$distribution->id}}"
                                >
                                {{$distribution->name}}
                            </option>
                        @endforeach

                        <x-slot name="radio">
                            <input
                                wire:model.live="split"
                                id="split"
                                name="split"
                                type="checkbox"
                                class="w-4 h-4 ml-2 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                >
                        </x-slot>
                    </x-forms.row>
                </div>

                <br>

                <x-forms.row
                    wire:model.live="desc"
                    errorName="desc"
                    name="desc"
                    placeholder="LIKE Transaction Desc"
                    text="Description"
                    >
                </x-forms.row>

                {{-- SPLITS --}}
                <div
                    {{-- splits: @entangle('splits'),  --}}
                    x-data="{ open: @entangle('split'), total: @entangle('amount')}"
                    x-show="open"
                    x-transition
                    >
                    <br>
                    <x-forms.row
                        wire:click="bulkSplits"
                        errorName=""
                        name=""
                        text="Splits"
                        type="button"
                        {{-- IF has splits VS no splits --}}
                        {{-- x-text="splits == true ? 'Edit Splits' : 'Add Splits'" --}}
                        x-text="'Add Splits'"
                        >
                    </x-forms.row>
                </div>
                {{-- SPLIT FOREACH --}}
                <div
                    {{-- splits: @entangle('splits'),  --}}
                    x-data="{ split: @entangle('split'), bulk_splits: @entangle('bulk_splits')}"
                    x-show="split && bulk_splits"
                    x-transition
                    >
                    <x-cards.wrapper class="col-span-4 p-6 lg:col-span-2">
                        <x-cards.body>
                            @foreach ($bulk_splits as $index => $split)
                                <x-cards.heading>
                                    <x-slot name="left">
                                        <h1>Split {{$index + 1}}</h1>
                                    </x-slot>

                                    <x-slot name="right">
                                        {{-- cannot remove if splits is equal to 2 or less --}}
                                        @if($loop->count > 2)
                                            <button
                                                type="button"
                                                wire:click="removeSplit({{$index}})"
                                                x-transition
                                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                Remove Split
                                            </button>
                                        @endif
                                        @if($loop->last)
                                            <button
                                                wire:click="addSplit"
                                                type="button"
                                                class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                Add Another Split
                                            </button>
                                        @endif
                                    </x-slot>
                                </x-cards.heading>
                                <div
                                    wire:key="bulk-splits-{{ $index }}"
                                    class="mt-2 space-y-2"
                                    >
                                    {{-- ROWS --}}
                                    <x-forms.row
                                        wire:model.live.debounce.200ms="bulk_splits.{{ $index }}.amount"
                                        errorName="bulk_splits.{{ $index }}.amount"
                                        name="amount"
                                        text="Amount"
                                        type="number"
                                        hint=" "
                                        textSize="xl"
                                        placeholder="00.00"
                                        inputmode="decimal"
                                        pattern="[0-9]*"
                                        step="0.01"
                                        >
                                        <x-slot name="hint_dropdown">
                                            <label for="amount_type" class="sr-only">Country</label>
                                            <select
                                                wire:model.live="bulk_splits.{{ $index }}.amount_type"
                                                errorName="bulk_splits.{{ $index }}.amount_type"
                                                {{-- x-bind:disabled="any_amount" --}}
                                                id="amount_type"
                                                name="amount_type"
                                                autocomplete="amount_type"
                                                class="h-full py-0 pl-3 text-gray-500 bg-transparent border-0 rounded-md pr-7 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                                                >
                                                <option value="$">$</option>
                                                <option value="%">%</option>
                                            </select>
                                        </x-slot>
                                    </x-forms.row>

                                    <x-forms.row
                                        wire:model.live="bulk_splits.{{ $index }}.distribution_id"
                                        errorName="bulk_splits.{{ $index }}.distribution_id"
                                        name="distribution_id"
                                        text="Distribution"
                                        type="dropdown"
                                        >
                                        <option
                                            value=""
                                            readonly
                                            x-text="'Select Distribution'"
                                            >
                                        </option>

                                        @foreach ($distributions as $distribution)
                                            <option
                                                value="{{$distribution->id}}"
                                                >
                                                {{$distribution->name}}
                                            </option>
                                        @endforeach
                                    </x-forms.row>

                                    <hr>
                                </div>
                            @endforeach
                        </x-cards.body>
                    </x-cards.wrapper>
                </div>
                <br>
                {{-- FOOTER --}}
                <x-cards.footer>
                    <button
                        wire:click="$emitTo('transactions.bulk-match', 'resetModal')"
                        type="button"
                        x-on:click="open = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                        Cancel
                    </button>
                    {{--
                    <div
                        x-data="{ submit_disabled: @entangle('submit_disabled').live }"
                        > --}}
                    <button
                        type="submit"
                        {{-- x-on:click="submit_disabled = true" --}}
                        {{-- x-bind:disabled="store" --}}
                        {{-- {{$submit_disabled == true ? 'disabled' : ''}} --}}
                        {{-- type="submit" --}}
                        {{-- x-on:click="open = false" --}}

                        {{-- wire:click="{{$view_text['form_submit']}}" --}}
                        {{-- wire:loading.attr="disabled"
                        wire:target="{{$view_text['form_submit']}}, 'expense', 'createExpenseFromTransaction'" --}}
                        {{-- x-bind:disabled="expense.project_id" --}}
                        class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <p wire:loading.remove wire:target="store">
                                Create Bulk Match
                            </p>
                            <p wire:loading wire:target="store">
                                Saving...
                            </p>
                        </button>
                    {{-- </div> --}}
                </x-cards.footer>

                {{-- https://tailwindui.com/components/application-ui/data-display/description-lists --}}
                {{-- drop-shadow-none shadow-none --}}

                @if(!is_null($vendor_id))
                    @if(!$vendor_transactions->isEmpty())
                    <x-cards.wrapper class="col-span-4 p-6 lg:col-span-2">
                        <x-cards.heading class="bg-color-none">
                            <x-slot name="left">
                                <h1>Vendor <b>Transactions</b></h1>
                            </x-slot>
                            <x-slot name="right">
                                {{-- , ['vendor', '{{$vendor_add_type}}'] --}}
                                <x-cards.button wire:click="$dispatch('manualMatch')">
                                    Add Expenses For Selected
                                </x-cards.button>
                            </x-slot>
                        </x-cards.heading>
                        <x-lists.ul>
                            @foreach($vendor_transactions as $key => $transactions)
                                @php
                                    $checkbox = [
                                        // checked vs unchecked
                                        'wire_click' => "checkbox($key)",
                                        'id' => "$key",
                                        'name' => "vendor_amount_group",
                                    ];
                                @endphp
                                <x-lists.search_li
                                    {{-- tpggle checkbox value --}}
                                    {{-- :line_details="" --}}
                                    :line_title="money($transactions->first()->amount) . ' | ' . $transactions->first()->plaid_merchant_description"
                                    :bubble_message="$transactions->count() . ' Transaction/s'"

                                    {{-- :line_title="'TEST titlte'" --}}
                                    :checkbox="$checkbox"
                                    >
                                </x-lists.search_li>
                            @endforeach
                        </x-lists.ul>
                    </x-cards.wrapper>
                    @endif
                @endif

                @if(!is_null($vendor_id))
                    @if(!$vendor_expenses->isEmpty())
                    <x-cards.wrapper class="col-span-4 p-6 lg:col-span-2">
                        <x-cards.heading class="bg-color-none">
                            <x-slot name="left">
                                <h1>Vendor <b>Expenses</b></h1>
                            </x-slot>
                            <x-slot name="right">
                                {{-- , ['vendor', '{{$vendor_add_type}}'] --}}
                                {{-- <x-cards.button wire:click="$dispatch('manualMatch')">
                                    Add Expenses For Selected
                                </x-cards.button> --}}
                            </x-slot>
                        </x-cards.heading>
                        <x-lists.ul>
                            @foreach($vendor_expenses as $amount => $expenses)
                                {{-- @php
                                    $checkbox = [
                                        // checked vs unchecked
                                        'wire_click' => "checkbox($key)",
                                        'id' => "$key",
                                        'name' => "vendor_amount_group",
                                    ];
                                @endphp --}}
                                <x-lists.search_li
                                    {{-- tpggle checkbox value --}}
                                    {{-- :line_details="" --}}
                                    :line_title="money($amount)"
                                    :bubble_message="$expenses->count() . ' Expense/s'"

                                    {{-- :line_title="'TEST titlte'" --}}
                                    {{-- :checkbox="$checkbox" --}}
                                    >
                                </x-lists.search_li>
                            @endforeach
                        </x-lists.ul>
                    </x-cards.wrapper>
                    @endif
                @endif
            </div>
        </x-cards.body>
    </form>
</x-modals.modal>

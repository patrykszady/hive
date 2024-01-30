<x-modals.modal>
    {{-- @if(isset($this->expense)) --}}
        <form wire:submit="{{$view_text['form_submit']}}">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                {{-- <x-slot name="right">
                    @if(isset($expense->id))
                        <x-cards.button href="{{route('expenses.show', $expense->id)}}" target="_blank">
                            Show Expense
                        </x-cards.button>
                    @endif
                </x-slot> --}}
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- EXISTING LINE ITEMS --}}
                @if($section_id)
                    <x-forms.row
                        wire:model.live.debounce.500ms="line_item.id"
                        errorName="line_item.id"
                        name="line_item.id"
                        text="Item Name"
                        type="dropdown"
                        >
                        <option value="" readonly>Select Item</option>
                        @foreach ($line_items as $key => $line_item)
                            <option value="{{$line_item->id}}">{{$line_item->name}}</option>
                        @endforeach

                        {{-- @if(is_null($expense->vendor_id) AND isset($transaction->plaid_merchant_description))
                            <x-slot name="bottom">
                                @if(isset($transaction->plaid_merchant_name))
                                    <p class="mt-2 text-sm text-black-600">Name: {{$transaction->plaid_merchant_name}}</p>
                                @endif
                                @if(isset($transaction->plaid_merchant_description))
                                    <p class="mt-2 text-sm text-black-600">Desc: {{$transaction->plaid_merchant_description}}</p>
                                @endif
                            </x-slot>
                        @endif --}}
                    </x-forms.row>
                @else
                    <x-forms.row
                        wire:model.live.debounce.500ms="line_item.name"
                        errorName="line_item.name"
                        name="line_item.name"
                        text="Item Name"
                        type="text"
                        placeholder="Item Name"
                        >

                        {{-- @if(is_null($expense->vendor_id) AND isset($transaction->plaid_merchant_description))
                            <x-slot name="bottom">
                                @if(isset($transaction->plaid_merchant_name))
                                    <p class="mt-2 text-sm text-black-600">Name: {{$transaction->plaid_merchant_name}}</p>
                                @endif
                                @if(isset($transaction->plaid_merchant_description))
                                    <p class="mt-2 text-sm text-black-600">Desc: {{$transaction->plaid_merchant_description}}</p>
                                @endif
                            </x-slot>
                        @endif --}}
                    </x-forms.row>
                @endif

                <div
                    x-data="{ open: @entangle('line_item.name').live }"
                    x-show="open"
                    x-transition.duration.250ms
                    class="my-4 space-y-4"
                    >

                    {{-- DESC --}}
                    <x-forms.row
                        wire:model.blur="line_item.desc"
                        errorName="line_item.desc"
                        name="line_item.desc"
                        text="Description"
                        type="textarea"
                        rows="3"
                        placeholder="Description for this Line Item.">
                    </x-forms.row>

                    {{-- NOTES --}}
                    <x-forms.row
                        wire:model.blur="line_item.notes"
                        errorName="line_item.notes"
                        name="line_item.notes"
                        text="Notes"
                        type="textarea"
                        rows="3"
                        placeholder="Item Notes.">
                    </x-forms.row>

                    {{-- CATEGORY --}}
                    <x-forms.row
                        wire:model.blur="line_item.category"
                        errorName="line_item.category"
                        name="line_item.category"
                        text="Category"
                        type="text"
                        placeholder="Category">
                    </x-forms.row>

                    {{-- SUB CATEGORY --}}
                    <x-forms.row
                        wire:model.blur="line_item.sub_category"
                        errorName="line_item.sub_category"
                        name="line_item.sub_category"
                        text="Sub Category"
                        type="text"
                        placeholder="Sub Category">
                    </x-forms.row>

                    {{-- UNIT TYPE --}}
                    <x-forms.row
                        wire:model.live="line_item.unit_type"
                        errorName="line_item.unit_type"
                        name="line_item.unit_type"
                        text="Unit Type"
                        type="dropdown"
                        >
                        @include('livewire.line-items._unit_type_options')
                    </x-forms.row>

                    {{-- COST --}}
                    <x-forms.row
                        wire:model.live="line_item.cost"
                        errorName="line_item.cost"
                        name="line_item.cost"
                        text="Amount"
                        type="number"
                        hint="$"
                        {{-- textSize="xl" --}}
                        placeholder="00.00"
                        inputmode="decimal"
                        {{-- pattern="[-+,0-9.]*" --}}
                        step="0.01"
                        min="0.01"
                        autofocus
                        {{-- x-bind:disabled="amount_disabled" --}}
                        >
                    </x-forms.row>

                    <div
                        x-data="{ open: @entangle('new_line_item').live }"
                        x-show="!open"
                        x-transition.duration.250ms
                        class="my-4 space-y-4"
                        >
                        {{-- QUANTITY --}}
                        <x-forms.row
                            wire:model.live="line_item.quantity"
                            errorName="line_item.quantity"
                            name="line_item.quantity"
                            text="Quantity"
                            type="number"
                            {{-- textSize="xl" --}}
                            placeholder="1"
                            inputmode="numeric"
                            {{-- pattern="[-+,0-9.]*" --}}
                            step="1"
                            min="1"
                            autofocus
                            {{-- x-bind:disabled="amount_disabled" --}}
                            >
                        </x-forms.row>

                        {{-- TOTAL --}}
                        <x-forms.row
                            wire:model.live="line_item.total"
                            errorName="line_item.total"
                            name="line_item.total"
                            text="Total"
                            type="number"
                            hint="$"
                            disabled
                            textSize="xl"
                            inputmode="numeric"
                            min="1"
                            step="1"
                            autofocus
                            >
                        </x-forms.row>
                    </div>
                </div>
            </x-cards.body>

            {{-- @dd($line_item->id) --}}
            <x-cards.footer>
                {{-- <button
                    type="submit"
                    class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                    {{$view_text['button_text']}}
                </button> --}}
                {{-- <div

                    > --}}
                    <button
                        wire:click="$emitTo('line-items.line-items-add', 'resetModal')"
                        type="button"
                        {{-- x-bind:disabled="submit_disabled" --}}
                        x-on:click="open = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                        Cancel
                    </button>

                    {{-- only show if in Edit --}}
                    <div
                        x-data="{ open: @entangle('estimate_line_item.name').live }"
                        x-show="open"
                        x-transition.duration.250ms
                        class="my-4 space-y-4"
                        >
                        <button
                            wire:click="$emitTo('line-items.line-items-add', 'removeFromEstimate')"
                            type="button"
                            {{-- x-bind:disabled="submit_disabled" --}}
                            x-on:click="open = false"
                            class="px-4 py-2 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                            Remove
                        </button>
                    </div>

                    <button
                        type="button"
                        wire:click="{{$view_text['form_submit']}}"
                        class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                        {{$view_text['button_text']}}
                    </button>
                {{-- </div> --}}
            </x-cards.footer>
        </form>
    {{-- @endif --}}
</x-modals.modal>

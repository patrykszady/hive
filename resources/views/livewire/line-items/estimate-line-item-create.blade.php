<div>
    <x-modals.modal>
        <form wire:submit="{{$view_text['form_submit']}}">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                <div
                    x-data="{ openDropdown: false, search: @entangle('search'), edit_line_item: @entangle('edit_line_item') }"
                    >
                    {{-- SEARCH DROPDOWN --}}
                    <x-forms.row
                        x-on:click="openDropdown = true, search = ''"
                        x-bind:disabled="edit_line_item"
                        errorName="search"
                        name="search"
                        text="Select Line Item"
                        type="search_dropdown"
                        placeholder="Search Line Items"
                        >

                        <x-slot:rowslot>
                            <div
                                x-show="openDropdown && !edit_line_item"
                                x-transition
                                class="py-2 mt-1 overflow-y-auto bg-white border rounded-md shadow-lg max-h-64"
                                >
                                <x-lists.ul x-on:click="openDropdown = false">
                                    @foreach($line_items_test as $line_item)
                                        @php
                                            $line_details = [
                                                1 => [
                                                    'text' => $line_item->desc,
                                                    'icon' => 'M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z'
                                                    ],
                                                ];
                                        @endphp

                                        <x-lists.search_li
                                            wire:click="selected_line_item({{$line_item->id}})"
                                            {{-- wire:click="$dispatchTo('line-items.line-item-create', 'editItem', { lineItemId: {{$line_item->id}} })" --}}
                                            :line_details="$line_details"
                                            :line_title="$line_item->name"
                                            :bubble_message="$line_item->category . '/' . $line_item->sub_category"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            </div>
                        </x-slot>
                    </x-forms.row>
                </div>

                <div
                    x-data="{ open: @entangle('line_item_id') }"
                    x-show="open"
                    x-transition
                    class="my-4 space-y-4"
                    >

                    {{-- DESC --}}
                    <x-forms.row
                        wire:model.live.debounce.500ms="form.desc"
                        errorName="form.desc"
                        name="form.desc"
                        text="Description"
                        type="textarea"
                        rows="4"
                        placeholder="Description for this Line Item.">
                    </x-forms.row>

                    {{-- NOTES --}}
                    <x-forms.row
                        wire:model.live.debounce.500ms="form.notes"
                        errorName="form.notes"
                        name="form.notes"
                        text="Notes"
                        type="textarea"
                        rows="3"
                        placeholder="Item Notes">
                    </x-forms.row>

                    {{-- CATEGORY --}}
                    <x-forms.row
                        wire:model.live.debounce.500ms="form.category"
                        errorName="form.category"
                        name="form.category"
                        text="Category"
                        type="text"
                        placeholder="Category">
                    </x-forms.row>

                    {{-- SUB CATEGORY --}}
                    <x-forms.row
                        wire:model.live.debounce.500ms="form.sub_category"
                        errorName="form.sub_category"
                        name="sub_category"
                        text="Sub Category"
                        type="text"
                        placeholder="Sub Category">
                    </x-forms.row>

                    {{-- UNIT TYPE --}}
                    <x-forms.row
                        wire:model.live="form.unit_type"
                        errorName="form.unit_type"
                        name="unit_type"
                        text="Unit Type"
                        type="dropdown"
                        >
                        @include('livewire.line-items._unit_type_options')
                    </x-forms.row>

                    {{-- COST --}}
                    <x-forms.row
                        wire:model.live.debounce.500ms="form.cost"
                        errorName="form.cost"
                        name="form.cost"
                        text="Amount"
                        type="number"
                        hint="$"
                        placeholder="00.00"
                        inputmode="decimal"
                        {{-- pattern="[-+,0-9.]*" --}}
                        step="0.01"
                        autofocus
                        {{-- x-bind:disabled="amount_disabled" --}}
                        >

                    </x-forms.row>

                    {{-- QUANTITY --}}
                    <x-forms.row
                        wire:model.live="form.quantity"
                        errorName="form.quantity"
                        name="quantity"
                        text="Quantity"
                        type="number"
                        {{-- textSize="xl" --}}
                        placeholder="1"
                        inputmode="numeric"
                        {{-- pattern="[-+,0-9.]*" --}}
                        step=".1"
                        min=".1"
                        autofocus
                        {{-- x-bind:disabled="amount_disabled" --}}
                        >
                    </x-forms.row>

                    {{-- TOTAL --}}
                    <x-forms.row
                        wire:model.live="form.total"
                        errorName="form.total"
                        name="total"
                        text="Total"
                        type="number"
                        hint="$"
                        disabled
                        textSize="xl"
                        inputmode="numeric"
                        {{-- min="1"
                        step="1" --}}
                        autofocus
                        >
                    </x-forms.row>
                </div>
            </x-cards.body>

            <x-cards.footer>
                <button
                    type="button"
                    x-on:click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                    Cancel
                </button>
                <div
                    x-data="{ estimate_line_item: @entangle('estimate_line_item') }"
                    x-show="estimate_line_item"
                    >
                    <button
                        wire:click="removeFromEstimate"
                        type="button"
                        {{-- x-bind:disabled="submit_disabled" --}}
                        x-on:click="open = false"
                        class="px-4 py-2 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                        Remove
                    </button>
                </div>
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                    {{$view_text['button_text']}}
                </button>
            </x-cards.footer>
        </form>
    </x-modals.modal>
</div>

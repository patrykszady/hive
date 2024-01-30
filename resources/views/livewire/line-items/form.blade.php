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
                <x-forms.row
                    wire:model.live.debounce.500ms="form.name"
                    errorName="form.name"
                    name="form.name"
                    text="Item Name"
                    type="text"
                    placeholder="Item Name."
                    >

                    {{-- 08-26-2023 show potential duplicates --}}
                </x-forms.row>

                <div
                    x-data="{ open: @entangle('form.name') }"
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
                        placeholder="Item Notes.">
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
                        name="form.sub_category"
                        text="Sub Category"
                        type="text"
                        placeholder="Sub Category">
                    </x-forms.row>

                    {{-- UNIT TYPE --}}
                    <x-forms.row
                        wire:model.live="form.unit_type"
                        errorName="form.unit_type"
                        name="form.unit_type"
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
                        min="0.01"
                        autofocus
                        {{-- x-bind:disabled="amount_disabled" --}}
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
                        type="button"
                        wire:click="removeFromEstimate"
                        {{-- wire:confirm.prompt="Are you sure you want to delete this line item?\n\nType DELETE to confirm|DELETE" --}}
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

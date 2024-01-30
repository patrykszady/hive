<x-modals.modal>
    <form wire:submit="{{$view_text['form_submit']}}">
        <x-cards.heading>
            <x-slot name="left">
                <h1>Project Bid</h1>
            </x-slot>

            <x-slot name="right">
                <x-cards.button
                    wire:click="addChangeOrder"
                    >
                    Add Change Order
                </x-cards.button>
            </x-slot>
        </x-cards.heading>

        <x-cards.body :class="'space-y-2 my-2'">
            @foreach($form->bids as $bid_index => $bid)
                <div
                    class="mt-2 space-y-2"
                    >
                    {{-- ROWS --}}
                    <x-forms.row
                        wire:model.live="form.bids.{{$bid_index}}.amount"
                        errorName="form.bids.{{$bid_index}}.amount"
                        name="form.bids.{{$bid_index}}.amount"
                        text="{{$bid->name}}"
                        type="number"
                        hint="$"
                        textSize="xl"
                        placeholder="00.00"
                        inputmode="numeric"
                        step="0.01"
                        x-bind:disabled="{{!$bid->estimate_sections->isEmpty()}}"
                        radioHint="{{$loop->first ? '' : 'Remove'}}"
                        >
                        <x-slot name="radio">
                            <input
                                {{-- wire:click="$dispatch('removeChangeOrder', { index: {{$bid_index}} })" --}}
                                wire:click="removeChangeOrder({{$bid_index}})"
                                id="remove{{$bid_index}}"
                                name="remove"
                                value="true"
                                type="checkbox"
                                x-bind:disabled="{{!$bid->estimate_sections->isEmpty()}}"
                                class="w-4 h-4 ml-2 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                >
                        </x-slot>

                        {{-- if disabled, show a span: "" --}}
                    </x-forms.row>
                </div>
            @endforeach
        </x-cards.body>

        <x-cards.footer>
            <button
                type="button"
                x-on:click="open = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                Cancel
            </button>

            <x-forms.button
                type="submit"
                >
                {{$view_text['button_text']}}
            </x-forms.button>
        </x-cards.footer>
    </form>
</x-modals.modal>

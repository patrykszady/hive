<x-modals.modal>
    <form wire:submit.prevent="{{$view_text['form_submit']}}"> 
        <x-cards.heading>
            <x-slot name="left">
                <h1>Project Bid</h1>
            </x-slot>

            <x-slot name="right">
                <x-cards.button 
                    wire:click="$emit('addChangeOrder')"
                    >
                    Add Change Order
                </x-cards.button>
                {{-- <button
                    x-show="{{$splits_count == 2}}"
                    wire:click="$emit('addSplit')" 
                    type="button"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Another Split
                </button> --}}
            </x-slot>
        </x-cards.heading>

        <x-cards.body :class="'space-y-2 my-2'">
            @foreach ($bids as $index => $bid)
                {{-- <x-cards.heading>
                    <x-slot name="left">
                        9-20-22: x-text inline if statement
                        @if($loop->first)
                            <h1>Original Bid</h1>
                        @else
                            <h1>Change Order {{$index + 1}}</h1>
                        @endif
                    </x-slot>           

                </x-cards.heading> --}}
                <div
                    wire:key="bids-{{ $index }}" 
                    class="space-y-2 mt-2"
                    >
                    {{-- ROWS --}}
                    <x-forms.row 
                        wire:model.debounce.200ms="bids.{{ $index }}.amount" 
                        errorName="bids.{{ $index }}.amount"
                        name="bids.{{ $index }}.amount"
                        text="{{$loop->first ? 'Original Bid' : 'Change Order ' . $index}}" 
                        type="number" 
                        hint="$" 
                        textSize="xl"
                        placeholder="00.00" 
                        inputmode="numeric" 
                        step="0.01"
                        radioHint="{{$loop->first ? '' : 'Remove'}}"
                        >
                        <x-slot name="radio">
                            <input
                                id="remove{{$index}}"
                                name="remove" 
                                value="true"
                                type="checkbox"
                                wire:click="$emit('removeChangeOrder', {{$index}})"
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded ml-2"
                                >
                        </x-slot>
                    </x-forms.row>
                </div>
            @endforeach
        </x-cards.body>

        <x-cards.footer>
            <button 
                type="button"
                x-on:click="open = false"
                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                > 
                Cancel
            </button>

            {{-- <div>
                <h1>{{money($this->splits_sum)}}</h1>
                @if($errors->has('expense_splits_total_match'))         
                    <x-forms.error errorName="expense_splits_total_match" />              
                @endif
            </div>             --}}

            <button 
                type="submit"
                {{-- x-on:click="open = false" --}}
                {{-- x-bind:disabled="expense.project_id" --}}
                class="ml-3 inline-flex justify-center disabled:opacity-50 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                {{$view_text['button_text']}}
            </button>          
        </x-cards.footer> 
    </form>
</x-modals.modal>
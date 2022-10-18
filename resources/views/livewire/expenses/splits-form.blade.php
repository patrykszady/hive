<x-modals.modal>
    <form wire:submit.prevent="{{$view_text['form_submit']}}"> 
        <x-cards.heading>
            <x-slot name="left">
                <h1>Expense Project Splits</h1>
            </x-slot>

            <x-slot name="right">
                {{-- <x-cards.button href="#" wire:click="$emit('addSplit')">
                    Add Another Split
                </x-cards.button> --}}
                {{-- <button
                    x-show="{{$splits_count == 2}}"
                    wire:click="$emit('addSplit')" 
                    type="button"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Another Split
                </button> --}}
            </x-slot>
        </x-cards.heading>

        <x-cards.body>
            @foreach ($expense_splits as $index => $split)
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Split {{$index + 1}}</h1>
                    </x-slot>
        
                    <x-slot name="right">
                        {{-- <x-cards.button href="#" wire:click="$emit('addSplit')">
                            Add Another Split
                        </x-cards.button> --}}
                        @if($loop->count > 2)
                            <button 
                                {{-- cannot remove if splits is equal to 2 or less --}} 
                                type="button"
                                {{-- x-data="{ splits_count: @entangle('splits_count')}" 
                                x-show="{{count($expense_splits)}} > 2"  --}}
                                {{-- on click remove THIS splits_count --}} 
                                wire:click="$emit('removeSplit', {{$index}})"
                                x-transition.duration.150ms
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                Remove Split
                            </button>
                        @endif

                        @if($loop->last)
                            <button 
                                wire:click="$emit('addSplit')" 
                                type="button"
                                class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                Add Another Split
                            </button>
                        @endif
                    </x-slot>
                </x-cards.heading>
                <div 
                    wire:key="expense-splits-{{ $index }}" 
                    class="space-y-2 mt-2"
                    >
                    {{-- ROWS --}}
                    <x-forms.row 
                        wire:model.debounce.200ms="expense_splits.{{ $index }}.amount" 
                        errorName="expense_splits.{{ $index }}.amount"
                        name="expense_splits.{{ $index }}.amount"
                        text="Amount" 
                        type="number" 
                        hint="$" 
                        textSize="xl"
                        placeholder="00.00" 
                        inputmode="numeric" step="0.01"
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model="expense_splits.{{ $index }}.project_id"
                        errorName="expense_splits.{{ $index }}.project_id" 
                        name="expense_splits.{{ $index }}.project_id"
                        text="Project" 
                        type="dropdown"
                    >
                        <option value="" readonly x-text="'Select Project'">
                        </option>

                        @foreach ($projects as $project)
                            <option value="{{$project->id}}">{{$project->name}}</option>
                        @endforeach

                        <option disabled>----------</option>
                    
                        @foreach ($distributions as $distribution)
                            <option 
                                value="D:{{$distribution->id}}"
                                >
                                {{$distribution->name}}
                            </option>
                        @endforeach
                    </x-forms.row>

                    <x-forms.row wire:model.lazy="expense_splits.{{ $index }}.reimbursment"
                        errorName="expense_splits.{{ $index }}.reimbursment" name="expense_splits.{{ $index }}.reimbursment"
                        text="Reimbursment" type="dropdown">
                        <option value="">None</option>
                        <option value="Client">Client</option>
                    </x-forms.row>

                    <x-forms.row wire:model.lazy="expense_splits.{{ $index }}.note" errorName="expense_splits.{{ $index }}.note" name="note" text="Note" type="textarea"
                        rows="1" placeholder="Notes about this expense split.">
                    </x-forms.row>
                    <hr>
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

            <div>
                <h1>{{money($this->splits_sum)}}</h1>
                @if($errors->has('expense_splits_total_match'))         
                    <x-forms.error errorName="expense_splits_total_match" />              
                @endif
            </div>            

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
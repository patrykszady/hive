<x-modals.modal>
    {{-- @if(isset($expense)) --}}
        <form wire:submit.prevent="{{$view_text['form_submit']}}">
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

                {{-- PROJECT --}}
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
            </x-cards.body>

            <x-cards.footer>
                <button 
                    {{-- wire:click="$emit('resetModal')" --}}
                    type="button"
                    x-on:click="open = false"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    > 
                    Cancel
                </button>

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
    {{-- @endif --}}
</x-modals.modal>

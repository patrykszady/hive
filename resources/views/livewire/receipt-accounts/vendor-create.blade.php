<x-modals.modal>
    <form wire:submit="store">
        <x-cards.heading>
            <x-slot name="left">
                <h1>{{$vendor ? $vendor->name : 'NO VENDOR'}}</h1>
                {{-- <h1>{{$vendor->business_name}}</h1> --}}
            </x-slot>
        </x-cards.heading>
        <x-cards.body :class="'space-y-4 my-4'">
            <x-forms.row
                wire:model.live="distribution_id"
                errorName="distribution_id"
                name="distribution_id"
                text="Distribution"
                type="dropdown"
                >

                <option
                    value=""
                    readonly
                    x-text="'CONNECT'"
                    >
                </option>
                <option
                    value="NO_PROJECT"
                    x-text="'NO PROJECT'"
                    >
                </option>

                @foreach($distributions as $distribution)
                    <option
                        value="{{$distribution->id}}"
                        >
                        {{$distribution->name}}
                    </option>
                @endforeach
            </x-forms.row>
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
            <div
                x-data="{ open: @entangle('distribution_id') }"
                x-show="open"
                x-transition
                >
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                    Save
                </button>
            </div>
        </x-cards.footer>
    </form>
</x-modals.modal>

<div>
    <form wire:submit="audit_submit">
        {{-- HEADER --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Audit Report</h1>
            </x-slot>
            <x-slot name="right">
                {{-- @if(isset($expense->id))
                    <x-cards.button href="{{route('expenses.show', $expense->id)}}" target="_blank">
                        Show Expense
                    </x-cards.button>
                @endif --}}
            </x-slot>
        </x-cards.heading>

        {{-- ROWS --}}
        <x-cards.body :class="'space-y-4 my-4'">
            {{-- DATE --}}
            <x-forms.row
                wire:model.live.debounce.500ms="end_date"
                errorName="end_date"
                name="end_date"
                text="Audit End Date"
                type="date"
                autofocus
                >
            </x-forms.row>

            {{-- BANKS CHECKBOX/ RADIO MULTI-SELECT --}}

            <x-forms.row
                wire:model.live="banks"
                errorName="banks"
                name="banks"
                text="Banks"
                type="checkbox_group"
                {{-- :data="[
                    'wire_model' => $banks,
                    // 'radio_details_left' => [
                    //     'title' => 'name',
                    //     'desc' => 'address',
                    //     ],
                    // 'radio_details_right' => [
                    //     'title' => '',
                    //     'desc' => '',
                    //     ]
                    ]" --}}
                >

                @foreach($banks as $bank_id => $bank)
                    <div class="space-y-5">
                        <div class="relative flex items-start">
                            <div class="flex h-6 items-center">
                                <input
                                    wire:model.live="banks.{{$bank_id}}.checked"
                                    value="true"
                                    id="banks.{{$bank_id}}.checked"
                                    name="banks.{{$bank_id}}.checked"
                                    type="checkbox"
                                    aria-describedby="banks-description"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                    >
                            </div>
                            <div class="ml-3 text-sm leading-6">
                                <label
                                    for="banks.{{$bank_id}}.checked"
                                    class="font-medium text-gray-900"
                                    >
                                    {{$bank->name}}
                                </label>
                                {{-- <p id="comments-description" class="text-gray-500">Get notified when someones posts a comment on a
                                    posting.</p> --}}
                            </div>
                        </div>
                    </div>
                @endforeach
            </x-forms.row>

            <x-forms.row
                wire:model.live="type"
                errorName="type"
                name="type"
                text="Audit Type"
                type="dropdown"
                >

                <option value="" readonly>Select Type</option>
                <option value="general" readonly>General</option>
                <option value="workers" readonly>Workers</option>
            </x-forms.row>
        </x-cards.body>

        <x-cards.footer>
            <button></button>
            <button
                type="submit"
                class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                Run Audit
            </button>
        </x-cards.footer>
    </form>
    {{-- <button
    wire:click="$emitTo('vendor-docs.audit-show', 'auditTest')"
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
        Cancel
    </button> --}}
    {{-- @livewire('vendor-docs.audit-show') --}}
</div>

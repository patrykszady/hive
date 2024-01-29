<x-modals.modal>
    <form wire:submit="{{$view_text['form_submit']}}">
        <x-cards.heading>
            <x-slot name="left">
                <h1>Expense Project Splits</h1>
            </x-slot>

            <x-slot name="right">
                {{-- <x-cards.button href="#" wire:click="$dispatch('addSplit')">
                    Add Another Split
                </x-cards.button> --}}
                {{-- <button
                    x-show="{{$splits_count == 2}}"
                    wire:click="$dispatch('addSplit')"
                    type="button"
                    class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Another Split
                </button> --}}
            </x-slot>
        </x-cards.heading>

        <x-cards.body>
            @if(!is_null($expense_splits))
                @foreach ($expense_splits as $index => $split)
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
                                    wire:click="$dispatch('addSplit')"
                                    type="button"
                                    class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                    Add Another Split
                                </button>
                            @endif
                        </x-slot>
                    </x-cards.heading>
                    <div
                        wire:key="expense-splits-{{ $index }}"
                        class="mt-2 space-y-2"
                        >
                        {{-- ROWS --}}
                        <x-forms.row
                            wire:model.live.debounce.200ms="form.expense_splits.{{ $index }}.amount"
                            errorName="form.expense_splits.{{ $index }}.amount"
                            name="amount"
                            text="Amount"
                            type="number"
                            hint="$"
                            textSize="xl"
                            placeholder="00.00"
                            inputmode="decimal"
                            pattern="[0-9]*"
                            step="0.01"
                            >
                        </x-forms.row>

                        <x-forms.row
                            wire:model.live="form.expense_splits.{{ $index }}.project_id"
                            errorName="form.expense_splits.{{ $index }}.project_id"
                            name="project_id"
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

                        <x-forms.row
                            wire:model.live="form.expense_splits.{{ $index }}.reimbursment"
                            errorName="form.expense_splits.{{ $index }}.reimbursment"
                            name="reimbursment"
                            text="Reimbursment"
                            type="dropdown"
                            >
                            <option value="">None</option>
                            <option value="Client">Client</option>
                        </x-forms.row>

                        <x-forms.row
                            wire:model.live="form.expense_splits.{{ $index }}.note"
                            errorName="form.expense_splits.{{ $index }}.note"
                            name="note"
                            text="Note"
                            type="textarea"
                            rows="1"
                            placeholder="Notes about this expense split."
                            >
                        </x-forms.row>
                        <hr>
                    </div>
                @endforeach
            @endif
        </x-cards.body>

        <x-cards.footer>
            <button
                type="button"
                wire:click="$dispatch('resetSplits')"
                x-on:click="open = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                Cancel
            </button>

            <button
                type="button"
                disabled
                class="px-4 py-2 text-lg font-medium text-center text-gray-900 border-2 border-indigo-600 rounded-md shadow-sm cursor-text focus:outline-none"
                >
                {{money($this->splits_sum)}}
            </button>

            <x-forms.button
                type="submit"
                >
                {{$view_text['button_text']}}
            </x-forms.button>

            {{-- if error bag has expense_splits_total_match --}}
            <x-slot name="bottom">
                <x-forms.error errorName="expense_splits_total_match" />
            </x-slot>
        </x-cards.footer>
    </form>
</x-modals.modal>

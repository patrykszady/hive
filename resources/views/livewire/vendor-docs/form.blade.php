<div>
    <x-modals.modal>
        <form wire:submit="store">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>Add Vendor Document</h1>
                </x-slot>
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- TYPE --}}
                {{-- <x-forms.row
                    wire:model.blur="type"
                    errorName="type"
                    name="type"
                    text="Document Type"
                    type="dropdown"
                    >
                    <option value="" readonly>Select Document</option>
                    <option value="insurance">Insurance</option>
                        <option value="license">License</option>
                        <option value="w9">W9</option>
                </x-forms.row> --}}

                {{-- DOCUMENT --}}
                <x-forms.row
                    wire:model.live="doc_file"
                    errorName="doc_file"
                    name="doc_file"
                    text="Document"
                    type="file"
                    >
                    {{-- <p class="mt-2 text-sm text-orange-600" wire:loading wire:target="doc_file">Uploading...</p>
                    <p class="mt-2 text-sm text-green-600" wire:loaded wire:target="doc_file">Document Uploaded</p> --}}

                    {{-- <x-slot name="titleslot">
                        @if($expense->receipts()->exists())
                            <p class="mt-2 text-sm text-green-600" wire:loaded wire:target="doc_file">File Uploaded</p>
                        @endif
                            <p class="mt-2 text-sm text-green-600" wire:loading wire:target="doc_file">Uploading...</p>
                    </x-slot> --}}
                    <x-slot name="titleslot">
                        {{-- @if($expense->receipts()->exists())
                            <p class="mt-2 text-sm text-green-600" wire:loaded wire:target="receipt_file">Receipt Uploaded</p>
                        @endif --}}
                        <p class="mt-2 text-sm text-green-600" wire:loading wire:target="doc_file">Uploading...</p>
                    </x-slot>
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

                <button
                    type="button"
                    wire:click="store"
                    {{-- x-data="{ submit_disabled: false }"
                    x-on:click="submit_disabled = true"
                    x-bind:disabled="submit_disabled" --}}
                    wire:loading.attr="disabled"
                    class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:cursor-progress"
                    >
                        Add Document
                </button>
            </x-cards.footer>
        </form>
    </x-modals.modal>
</div>

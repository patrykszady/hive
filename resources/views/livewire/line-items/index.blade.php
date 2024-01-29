<div>
    <x-cards.wrapper class="w-full px-4 pb-5 mb-1 sm:px-6 lg:max-w-3xl lg:px-8">
        {{-- HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Estimate Items</h1>
            </x-slot>

            <x-slot name="right">
                {{-- @can('create', App\Models\Project::class) --}}
                    <x-cards.button
                        type="button"
                        wire:click="$dispatchTo('line-items.line-item-create', 'addItem')"
                        >
                        Create New Item
                    </x-cards.button>
                    {{-- <button x-data x-on:click="$dispatch('open-modal')">Open Modal</button> --}}
                {{-- @endcan --}}
            </x-slot>
        </x-cards.heading>

        {{-- SUB-HEADING --}}
        <x-cards.heading>
            {{-- main $slot --}}
            {{-- class="mt-3 sm:mt-0 sm:ml-4 --}}
            <div class="mx-auto">
                {{-- <label for="mobile-search-candidate" class="sr-only">Search</label> --}}
                <label for="desktop-search-candidate-5" class="sr-only">Search</label>
                <div class="flex rounded-md shadow-sm">
                    <div class="relative flex-grow focus-within:z-10">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <!-- Heroicon name: solid/search -->
                            <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            wire:model.live="search"
                            type="text"
                            name="mobile-search-candidate"
                            id="mobile-search-candidate"
                            class="block w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 rounded-l-md sm:hidden"
                            placeholder="Search"
                            autocomplete="mobile-search-candidate"
                            >
                        <input
                            wire:model.live="search"
                            type="text"
                            name="desktop-search-candidate-5"
                            id="desktop-search-candidate-5"
                            class="hidden w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 rounded-l-md sm:block sm:text-sm"
                            placeholder="Search Line Items"
                            autocomplete="desktop-search-candidate-5"
                            >
                    </div>
                    <button type="button"
                        class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 border border-gray-300 rounded-r-md bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                        <!-- Heroicon name: solid/sort-ascending -->
                        <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path
                                d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z" />
                        </svg>
                        <span class="ml-2">Sort</span>
                        <!-- Heroicon name: solid/chevron-down -->
                        <svg class="ml-2.5 -mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </x-cards.heading>

        {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
        <x-lists.ul>
            @foreach($line_items as $line_item)
                @php
                    $line_details = [
                        1 => [
                            'text' => $line_item->desc,
                            'icon' => 'M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z'
                            ],
                        ];
                @endphp

                <x-lists.search_li
                    wire:click="$dispatchTo('line-items.line-item-create', 'editItem', { lineItemId: {{$line_item->id}} })"
                    :line_details="$line_details"
                    :line_title="$line_item->name"
                    :bubble_message="$line_item->category . '/' . $line_item->sub_category"
                    >

                </x-lists.search_li>
            @endforeach
        </x-lists.ul>

        {{-- FOOTER for forms for example --}}
        {{-- <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
            <button type="submit"
                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Save
            </button>
        </div> --}}

        {{-- FOOTER --}}
        <x-cards.footer>
            {{-- 10/14/21 change/customize links view in resources/views/vendor/pagination/tailwind.blade.php to match our
            theme --}}
            {{-- {{ $projects->links() }} --}}
        </x-cards.footer>
    </x-cards.wrapper>

    {{-- NEW LINE ITEM MODAL --}}
    <livewire:line-items.line-item-create />
</div>

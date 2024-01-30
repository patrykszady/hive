<div>
    <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-3xl lg:px-8 pb-5 mb-1' : ''}}">
        {{-- HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Clients</h1>
            </x-slot>

            <x-slot name="right">
                {{-- <x-cards.button
                    wire:click="$dispatch('clientModal')">
                    Add New Client
                </x-cards.button> --}}
                {{-- $dispatchTo('expenses.expense-create', 'newExpense', { amount: {{$amount}}}) --}}
                {{-- <x-cards.button wire:click="$dispatch('newMember', ['client', 'NEW'])"> --}}
                <x-cards.button
                    wire:click="$dispatchTo('users.user-create', 'newMember', { model: 'client', model_id: 'NEW' })"
                    >
                    Add New Client
                </x-cards.button>
            </x-slot>
        </x-cards.heading>

        {{-- SUB-HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <div class="mt-3 sm:mt-0 sm:ml-4">
                    {{-- <label for="mobile-search-candidate" class="sr-only">Search</label> --}}
                    <label for="desktop-search-candidate" class="sr-only">Search</label>
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
                                wire:model.live="client_name_search"
                                type="text"
                                name="mobile-search-candidate"
                                id="mobile-search-candidate"
                                class="block w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 rounded-l-md sm:hidden"
                                placeholder="Search"
                                autocomplete="mobile-search-candidate"
                                >
                            <input
                                wire:model.live="client_name_search"
                                type="text"
                                name="desktop-search-candidate"
                                id="desktop-search-candidate"
                                class="hidden w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 rounded-l-md sm:block sm:text-sm"
                                placeholder="Search clients"
                                autocomplete="desktop-search-candidate"
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
                    {{-- <div>
                        <select
                            wire:model.live="vendor_type"
                            id="vendor_type"
                            name="vendor_type"
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="" readonly>Vendor Type</option>
                            <option value="Sub">Subcontractor</option>

                        </select>
                    </div> --}}
                </div>
            </x-slot>
        </x-cards.heading>

        {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
        <x-lists.ul>
            @foreach($clients as $client)
                @php
                    $line_details = [
                        1 => [
                            'text' => $client->one_line_address,
                            'icon' => 'M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z'

                            ],
                        ];
                @endphp

                <x-lists.search_li
                    href="{{route('clients.show', $client->id)}}"
                    :line_details="$line_details"
                    :line_title="$client->name"
                    :bubble_message="'Client'"
                    >
                </x-lists.search_li>
            @endforeach
        </x-lists.ul>

        {{-- FOOTER --}}
        <x-cards.footer>
            {{-- 10/14/21 change/customize links view in resources/views/vendor/pagination/tailwind.blade.php to match our
            theme --}}
            {{ $clients->links() }}
        </x-cards.footer>
    </x-cards.wrapper>

    <livewire:users.user-create />
    <livewire:clients.client-create />
</div>

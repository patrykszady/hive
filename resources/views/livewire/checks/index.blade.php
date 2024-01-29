<x-cards.wrapper class="max-w-xl px-4 pb-5 mb-1 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-3xl lg:px-8">
    {{-- HEADING --}}
    <x-cards.heading>
        <x-slot name="left">
            <h1>Checks</h1>
        </x-slot>

        <x-slot name="right">
            {{-- <x-cards.button href="{{route('expenses.create')}}">
                Create expense
            </x-cards.button> --}}
        </x-slot>
    </x-cards.heading>

    {{-- SUB-HEADING --}}
    <x-cards.heading>
        <div class="mx-auto">
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
                        wire:model.live="check_number"
                        type="text"
                        name="mobile-search-candidate"
                        id="mobile-search-candidate"
                        class="block w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 rounded-l-md sm:hidden"
                        placeholder="Check #"
                        autocomplete="mobile-search-candidate"
                        >
                    <input
                        wire:model.live="check_number"
                        type="text"
                        name="desktop-search-candidate"
                        id="desktop-search-candidate"
                        class="hidden w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 rounded-l-md sm:block sm:text-sm lg:text-lg"
                        placeholder="Search Check #"
                        autocomplete="desktop-search-candidate"
                        >
                </div>
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
                        wire:model.live="amount"
                        type="text"
                        name="mobile-search-candidate"
                        id="mobile-search-candidate"
                        class="block w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 sm:hidden"
                        placeholder="Amount"
                        autocomplete="mobile-search-candidate"
                        >
                    <input
                        wire:model.live="amount"
                        type="text"
                        name="desktop-search-candidate"
                        id="desktop-search-candidate"
                        class="hidden w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 sm:block sm:text-s lg:text-lg"
                        placeholder="Amount"
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

            <div>
                <select
                    wire:model.live="bank"
                    id="bank"
                    name="bank"
                    class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" readonly>All Banks</option>

                    @foreach($banks->groupBy('plaid_ins_id') as $index => $bank)
                        <option value="{{$bank->first()->id}}">{{$bank->first()->name}}</option>
                    @endforeach

                    {{-- @foreach($banks as $index => $bank)
                        <option value="{{$bank->info->id}}">{{$bank->info->name}}</option>
                    @endforeach --}}
                    {{-- <option disabled>----------</option>
                    @foreach($distributions as $index => $distribution)
                        <option value="D-{{$distribution->id}}">{{$distribution->name}}</option>
                    @endforeach --}}
                </select>
            </div>
            <div>
                <select
                    wire:model.live="check_type"
                    id="check_type"
                    name="check_type"
                    class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="" readonly>All Check Types</option>
                    <option value="Check">Check</option>
                    <option value="Transfer">Transfer | Zelle</option>
                    <option value="Cash">Cash</option>
                </select>
            </div>
        </div>
    </x-cards.heading>

    {{-- BODY --}}
    <x-cards.body>
        <x-lists.ul>
            @foreach($checks as $check)
                @php
                    $line_details = [
                        1 => [
                            'text' => $check->check_type != 'Check' ? $check->check_type : $check->check_number,
                            'icon' => 'M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z'
                            ],
                        2 => [
                            'text' => $check->date->format('M j, Y'),
                            'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                            ],
                        3 => [
                            'text' => $check->bank_account->bank->name,
                            'icon' => 'M10.496 2.132a1 1 0 00-.992 0l-7 4A1 1 0 003 8v7a1 1 0 100 2h14a1 1 0 100-2V8a1 1 0 00.496-1.868l-7-4zM6 9a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1zm3 1a1 1 0 012 0v3a1 1 0 11-2 0v-3zm5-1a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1z'
                            ],
                        4 => [
                            'text' => $check->owner,
                            'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                            ],
                        ];
                @endphp

                <x-lists.search_li
                    href="{{route('checks.show', $check->id)}}"
                    :line_details="$line_details"
                    :line_title="money($check->amount)"
                    :bubble_message="'Success'"
                    >
                </x-lists.search_li>
            @endforeach
        </x-lists.ul>
    </x-cards.body>

    {{-- FOOTER --}}
    <x-cards.footer>
        {{-- 10/14/21 change/customize links view in resources/views/vendor/pagination/tailwind.blade.php to match our
        theme --}}
        {{ $checks->links() }}
    </x-cards.footer>
</x-cards.wrapper>

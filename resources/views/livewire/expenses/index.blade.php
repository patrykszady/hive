<div>
    {{-- EXPENSES --}}
    <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-4xl lg:px-8 pb-5 mb-1' : ''}}">
        {{-- HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Expenses</h1>
            </x-slot>

            {{-- NEW EXPENSE BOTTON --}}
            <x-slot name="right">
                @can('create', App\Models\Expense::class)
                    @if($amount && $view == NULL)
                        <x-cards.button
                            wire:click="$dispatchTo('expenses.expense-create', 'newExpense', { amount: {{$amount}}})"
                            >
                            New Expense
                        </x-cards.button>
                    @endif
                @endcan
            </x-slot>
        </x-cards.heading>

        {{-- SUB-HEADING --}}
        <x-cards.heading>
            {{-- class="mt-3 sm:mt-0 sm:ml-4 --}}
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
                        {{-- 08-30-2023 combine into 1 --}}
                        <input
                            wire:model.live.debounce.500ms="amount"
                            type="number"
                            inputmode="decimal"
                            {{-- pattern="[-+,0-9.]*" --}}
                            step="0.01"
                            name="mobile-search-candidate"
                            id="mobile-search-candidate"
                            class="block w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 rounded-l-md sm:hidden sm:text-lg"
                            placeholder="Search"
                            autocomplete="mobile-search-candidate"
                            >
                        <input
                            wire:model.live.debounce.500ms="amount"
                            type="number"
                            inputmode="numeric"
                            step="0.01"
                            name="desktop-search-candidate"
                            id="desktop-search-candidate"
                            class="hidden w-full pl-10 border-gray-300 rounded-none focus:ring-indigo-500 focus:border-indigo-500 rounded-l-md sm:block sm:text-lg"
                            placeholder="Search amount"
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

                @if($view == NULL)
                    <div>
                        <select
                            wire:model.live="project"
                            id="project"
                            name="project"
                            @disabled($view == 'projects.show')
                            class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="" readonly>All Projects</option>
                            <option value="SPLIT">Project Splits</option>
                            <option value="NO_PROJECT">No Project</option>
                            @foreach($projects as $index => $project)
                                <option value="{{$project->id}}">{{$project->name}}</option>
                            @endforeach
                            <option disabled>----------</option>
                            @foreach($distributions as $index => $distribution)
                                <option value="D-{{$distribution->id}}">{{$distribution->name}}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <select
                        wire:model.live="vendor"
                        id="vendor"
                        name="vendor"
                        class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="" readonly>All Vendors</option>
                        <option value="0" readonly>NO VENDOR</option>
                        @foreach($vendors as $index => $vendor)
                            <option value="{{$vendor->id}}">{{$vendor->business_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-cards.heading>

        {{-- BODY --}}
        <x-cards.body>
            <div>
                <x-lists.ul wire:loading.class="opacity-50 text-opacity-40">
                    @foreach($expenses as $expense)
                        @php
                            if($view == 'projects.show'){
                                $line_details = [
                                1 => [
                                    'text' => $expense->date->format('m/d/Y'),
                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                    ],
                                2 => [
                                    'text' => is_null($expense->vendor->business_name) ? 'NO VENDOR' : $expense->vendor->business_name,
                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                                    ],
                                ];
                            }else{
                                $line_details = [
                                1 => [
                                    'text' => $expense->date->format('m/d/Y'),
                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                    ],
                                2 => [
                                    'text' => is_null($expense->vendor->business_name) ? 'NO VENDOR' : $expense->vendor->business_name,
                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                                    ],
                                3 => [
                                    'text' => $expense->type == 'transaction' ? 'NO PROJECT' : $expense->project->name,
                                    'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                    ],
                                // 4 => [
                                //     'text' => $expense->receipts()->first()->receipt_items->purchase_order ? $expense->receipts()->first()->receipt_items->purchase_order : '',
                                //     'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                //     ],
                                ];

                                $receipt = $expense->receipts()->latest()->first();

                                if(isset($receipt)){
                                    if($receipt->notes){
                                        array_push($line_details, [
                                            'text' => $receipt->notes,
                                            'icon' => 'M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z'
                                        ]);
                                    }
                                }

                                if(isset($expense->reimbursment)){
                                    array_push($line_details, [
                                        'text' => $expense->reimbursment,
                                        'icon' => 'M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.202.592.037.051.08.102.128.152z M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-6a.75.75 0 01.75.75v.316a3.78 3.78 0 011.653.713c.426.33.744.74.925 1.2a.75.75 0 01-1.395.55 1.35 1.35 0 00-.447-.563 2.187 2.187 0 00-.736-.363V9.3c.698.093 1.383.32 1.959.696.787.514 1.29 1.27 1.29 2.13 0 .86-.504 1.616-1.29 2.13-.576.377-1.261.603-1.96.696v.299a.75.75 0 11-1.5 0v-.3c-.697-.092-1.382-.318-1.958-.695-.482-.315-.857-.717-1.078-1.188a.75.75 0 111.359-.636c.08.173.245.376.54.569.313.205.706.353 1.138.432v-2.748a3.782 3.782 0 01-1.653-.713C6.9 9.433 6.5 8.681 6.5 7.875c0-.805.4-1.558 1.097-2.096a3.78 3.78 0 011.653-.713V4.75A.75.75 0 0110 4z'
                                    ]);
                                }
                            }

                            $click_emit_destination = 'editExpense';
                        @endphp

                        @can('create', App\Models\Expense::class)
                            <x-lists.search_li
                                wire:click="$dispatchTo('expenses.expense-create', '{{$click_emit_destination}}', { expense: {{$expense->id}}})"
                                :line_details="$line_details"
                                {{-- :no_hover=true --}}
                                :line_title="money($expense->amount)"
                                :bubble_message="$expense->status"
                                :bubble_color="$expense->status == 'Complete' ? 'green' : 'red'"
                                >
                            </x-lists.search_li>
                        @else
                            <x-lists.search_li
                                href="{{route('expenses.show', $expense->id)}}"
                                href_target="_blank"
                                :line_details="$line_details"
                                {{-- :no_hover=true --}}
                                :line_title="money($expense->amount)"
                                :bubble_message="$expense->status"
                                :bubble_color="$expense->status == 'Complete' ? 'green' : 'red'"
                                >
                            </x-lists.search_li>
                        @endcan
                    @endforeach
                </x-lists.ul>
            </div>
        </x-cards.body>

        {{-- FOOTER --}}
        <x-cards.footer>
            {{-- 10/14/21 change/customize links view in resources/views/vendor/pagination/tailwind.blade.php to match our
            theme --}}
            {{ $expenses->links() }}
        </x-cards.footer>
    </x-cards.wrapper>

    @can('create', App\Models\Expense::class)
        {{-- TRANSACTIONS --}}
        @if(!$transactions->isEmpty())
            <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-4xl lg:px-8 pb-5 mb-1' : ''}}">
                {{-- HEADING --}}
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Transactions</h1>
                    </x-slot>
                </x-cards.heading>

                {{-- SUB-HEADING --}}
                <x-cards.heading>
                    {{-- class="mt-3 sm:mt-0 sm:ml-4 --}}
                    <div class="mx-auto">
                        <div>
                            <select
                                wire:model.live="bank"
                                id="bank"
                                name="bank"
                                class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="" readonly>All Banks</option>
                                @foreach($banks as $institution_id => $bank)
                                    <option value="{{$institution_id}}">{{$bank->first()->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(!empty($bank_owners))
                            <div>
                                <select
                                    wire:model.live="bank_owner"
                                    id="bank_owner"
                                    name="bank_owner"
                                    class="block w-full py-2 pl-3 pr-10 mt-1 text-base border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="" readonly>All Owners</option>
                                    @foreach($bank_owners as $owner)
                                        <option value="{{$owner}}">{{$owner}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </x-cards.heading>

                {{-- BODY --}}
                <x-cards.body>
                    <div>
                        <x-lists.ul wire:loading.class="opacity-50 text-opacity-40">
                            @foreach($transactions as $expense)
                                @php
                                    if($view == 'projects.show'){
                                        $line_details = [
                                        1 => [
                                            'text' => $expense->date->format('m/d/Y'),
                                            'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                            ],
                                        2 => [
                                            'text' => $expense->vendor->business_name,
                                            'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                                            ],
                                        ];
                                    }else{
                                        $line_details = [
                                        1 => [
                                            'text' => $expense->date->format('m/d/Y'),
                                            'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                            ],
                                        2 => [
                                            'text' => $expense->vendor->business_name,
                                            'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                                            ],
                                        3 => [
                                            'text' => $expense->bank_account->bank->name . ' | ' . $expense->bank_account->type,
                                            'icon' => 'M9.674 2.075a.75.75 0 01.652 0l7.25 3.5A.75.75 0 0117 6.957V16.5h.25a.75.75 0 010 1.5H2.75a.75.75 0 010-1.5H3V6.957a.75.75 0 01-.576-1.382l7.25-3.5zM11 6a1 1 0 11-2 0 1 1 0 012 0zM7.5 9.75a.75.75 0 00-1.5 0v5.5a.75.75 0 001.5 0v-5.5zm3.25 0a.75.75 0 00-1.5 0v5.5a.75.75 0 001.5 0v-5.5zm3.25 0a.75.75 0 00-1.5 0v5.5a.75.75 0 001.5 0v-5.5z'
                                            ]
                                        // 3 => [
                                        //     'text' => 'NO PROJECT',
                                        //     'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                        //     ],
                                        // 4 => [
                                        //     'text' => $expense->plaid_merchant_description,
                                        //     'icon' => 'M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z'
                                        //     ]

                                        ];

                                        if(!is_null($expense->owner)){
                                            array_push($line_details, [
                                                'text' => $expense->owner,
                                                'icon' => 'M2.5 4A1.5 1.5 0 001 5.5V6h18v-.5A1.5 1.5 0 0017.5 4h-15zM19 8.5H1v6A1.5 1.5 0 002.5 16h15a1.5 1.5 0 001.5-1.5v-6zM3 13.25a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zm4.75-.75a.75.75 0 000 1.5h3.5a.75.75 0 000-1.5h-3.5z'
                                            ]);
                                        }

                                        if(strtolower($expense->plaid_merchant_description) != strtolower($expense->vendor->business_name)){
                                            array_push($line_details, [
                                                'text' => $expense->plaid_merchant_description,
                                                'icon' => 'M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z'
                                            ]);
                                        }

                                        // if(strtolower($expense->plaid_merchant_description) != strtolower($expense->vendor->business_name)){
                                        //     array_push($line_details, [
                                        //         'text' => $expense->plaid_merchant_description,
                                        //         'icon' => 'M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z'
                                        //     ]);

                                        //     if(strtolower($expense->plaid_merchant_description) != strtolower($expense->plaid_merchant_name)){
                                        //         array_push($line_details, [
                                        //             'text' => $expense->plaid_merchant_name,
                                        //             'icon' => 'M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z'
                                        //         ]);
                                        //     }
                                        // }
                                    }

                                    $click_emit_destination = "createExpenseFromTransaction";
                                @endphp

                                <x-lists.search_li
                                {{-- $emitTo('expenses.expenses-new-form', 'editExpense', {{$expense->id}}) --}}
                                    wire:click="$dispatchTo('expenses.expense-create', '{{$click_emit_destination}}', { transaction: {{$expense->id}}})"
                                    :line_details="$line_details"
                                    {{-- :no_hover=true --}}
                                    :line_title="money($expense->amount)"
                                    {{-- :bubble_message="$expense->complete == true ? 'Complete' : 'Missing Info'"
                                    :bubble_color="$expense->complete == true ? 'green' : 'red'" --}}
                                    :bubble_message="'Transaction'"
                                    :bubble_color="'yellow'"
                                    >
                                </x-lists.search_li>
                            @endforeach
                        </x-lists.ul>
                    </div>
                </x-cards.body>

                {{-- FOOTER --}}
                <x-cards.footer>
                    {{-- 10/14/21 change/customize links view in resources/views/vendor/pagination/tailwind.blade.php to match our
                    theme --}}
                    {{ $transactions->links() }}
                </x-cards.footer>
            </x-cards.wrapper>
        @endif

        {{-- EXPENSE FORM MODAL --}}
        <livewire:expenses.expense-create />
    @endcan
</div>

{{-- form classes divide-y divide-gray-200 --}}
<div class="xl:relative max-w-xl lg:max-w-5xl sm:px-6 mx-auto">
    <form wire:submit.prevent="{{$view_text['form_submit']}}">
        <x-cards.wrapper class="max-w-3xl mx-auto">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                {{-- <x-slot name="right">
                    <x-cards.button href="{{route('expenses.index')}}">
                        All Expenses
                    </x-cards.button>

                    @if(request()->routeIs('expenses.edit'))
                        <x-cards.button href="{{route('expenses.show', $expense->id)}}">
                            Show Expense
                        </x-cards.button>
                    @endif
                </x-slot> --}}
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- AMOUNT --}}
                <x-forms.row 
                    wire:model.defer="amount" 
                    errorName="amount" 
                    name="amount"
                    text="Amount"
                    type="number" 
                    hint="$" 
                    textSize="xl" 
                    placeholder="00.00" 
                    inputmode="decimal" 
                    pattern="[0-9]*"
                    step="0.01"
                    autofocus
                    > 
                </x-forms.row>

                <x-forms.row
                    wire:click.prevent="find_amount"
                    errorName=""
                    name=""
                    text=""
                    type="button"
                    buttonText="{{$view_text['button_text']}}"
                    >    
                </x-forms.row>

                {{-- existing expenses/transactions match from expense.amount --}}
                <div 
                    x-data="{open: @entangle('found')}" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >

                    {{-- EXPENSES FOUND --}}
                    @if(!is_null($expenses_found) || !is_null($transactions_found))
                        @if(!is_null($expenses_found))
                            <x-misc.hr>
                                Choose Existing Expense
                            </x-misc.hr>
                                <x-lists.ul :class="'mt-4'">
                                    @foreach ($expenses_found as $expense_found)
                                        @php
                                            $line_details = [
                                                1 => [
                                                    'text' => $expense_found->date->format('m/d/Y'),
                                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                            
                                                    ],
                                                2 => [
                                                    'text' => $expense_found->vendor->business_name,
                                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                            
                                                    ],
                                                3 => [
                                                    'text' => $expense_found->distribution ? $expense_found->distribution->name : $expense_found->project->name,
                                                    'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                            
                                                    ],
                                                ];
                                        @endphp
                            
                                        <x-lists.search_li
                                            href="{{$expense_found->project->name == 'No Project' ? route('expenses.edit', $expense_found->id) : route('expenses.show', $expense_found->id)}}"
                                            hrefTarget="_blank"
                                            :line_details="$line_details"
                                            :line_title="money($expense_found->amount)"
                                            :bubble_message="'Expense'"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            @if(is_null($transactions_found))
                                <x-misc.hr>
                                    Or Create New Expense
                                </x-misc.hr>
                            @endif
                        @endif

                        @if(!is_null($transactions_found))
                            <x-misc.hr>
                                Choose Existing Transaction
                            </x-misc.hr>
                                <x-lists.ul :class="'mt-4'">
                                    @foreach ($transactions_found as $transaction_found)
                                        @php
                                            $vendor_name = $transaction_found->vendor->business_name == "No Vendor" ? 'NO VENDOR <br> Maybe: ' . $transaction_found->plaid_merchant_name : $transaction_found->vendor->business_name;
                                            $line_details = [
                                                1 => [
                                                    'text' => $transaction_found->transaction_date->format('m/d/Y'),
                                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                                    ],
                                                2 => [
                                                    'text' => $vendor_name,
                                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                            
                                                    ],
                                                3 => [
                                                    'text' => $transaction_found->bank_account->bank->name,
                                                    'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z'                            
                                                    ],
                                                ];
                                        @endphp

                                        <x-lists.search_li
                                            href="#"
                                            {{-- hrefTarget="_blank" --}}
                                            wire:click="$emit('createExpenseFromTransaction', {{$transaction_found->id}})"
                                            :line_details="$line_details"
                                            :line_title="money($transaction_found->amount)"
                                            :bubble_message="'Transaction'"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            <x-misc.hr>
                                Or Create New Expense
                            </x-misc.hr>
                                <ul>
                                    <li class="grid place-items-center p-4 m-4">
                                        <a 
                                            href="{{route('expenses.create')}}"
                                            target="_blank"
                                            type="button" 
                                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            >
                                            Create New Expense
                                        </a>
                                    </li>
                                </ul>
                        @endif
                    @endif
                </div>
            </x-cards.body>
        </x-cards.wrapper>
    </form>
</div>
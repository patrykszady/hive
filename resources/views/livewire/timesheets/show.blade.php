<div>
    <x-page.top
        {{-- h1="{!! $timesheet->user->first_name . 's Timesheet' . '<br>' . 'Week of ' . $timesheet->date->format('M dS Y') !!}" --}}
        h1="{!! $timesheet->user->first_name . '\'s Timesheet' !!}"
        p="Week of {{ $timesheet->date->format('M dS Y') }}"
        {{-- right_button_href="{{auth()->user()->can('update', $timesheet) ? route('timesheets.edit', $tinesheet->id) : ''}}" --}}

        {{-- {{ route('expenses.edit', $expense->id) }} --}}
        {{-- right_button_text="Edit Timesheet" --}}
        >
    </x-page.top>

    <div class="grid max-w-xl grid-cols-4 gap-4 mx-auto xl:relative lg:max-w-5xl sm:px-6">
        <div class="col-span-4 space-y-4 lg:col-span-2 lg:h-32 lg:sticky lg:top-5">
            {{-- TIMESHEET DETAILS --}}
            <x-cards.wrapper>
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Timesheet Week Details</h1>
                        {{-- <p>Expense and related details like Expense Splits and Expense Receipts.</p> --}}
                    </x-slot>

                    {{-- @can('update', $expense)
                        <x-slot name="right">
                            <x-cards.button href="{{ route('expenses.edit', $expense->id) }}">
                                Edit Expense
                            </x-cards.button>
                        </x-slot>
                    @endcan --}}
                </x-cards.heading>

                <x-cards.body>
                    <x-lists.ul>
                        <x-lists.search_li
                            :basic=true
                            :line_title="'Team Member'"
                            :line_data="$timesheet->user->full_name"
                            >
                        </x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :line_title="'Week Of'"
                            :line_data="$timesheet->date->format('m/d/Y')"
                            >
                        </x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :line_title="'Week Total'"
                            :line_data="money($weekly_hours->sum('amount'))"
                            >
                        </x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :line_title="'Week Hours'"
                            :line_data="$weekly_hours->sum('hours')"
                            >
                        </x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :line_title="'Hourly'"
                            :line_data="money($timesheet->hourly)"
                            >
                        </x-lists.search_li>
                    </x-lists.ul>
                </x-cards.body>
            </x-cards.wrapper>

            {{-- WEEKLY GROUPED --}}
            <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        {{-- by project, not daily --}}
                        <h1>Weekly Hours</h1>
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($weekly_hours as $timesheet)
                        {{-- @dd($transaction->bank_account->bank) --}}
                        @php
                            // $timesheet->check = $timesheet->check()->withoutGlobalScopes()->first();
                            // dd($timesheet->check);
                            $line_details = [
                                1 => [
                                    'text' => $timesheet->hours,
                                    'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                ],
                                2 => [
                                    'text' => money($timesheet->amount),
                                    'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z'
                                ],
                            ];

                            if($timesheet->check){
                                $line_details += [
                                    3 => [
                                        'text' => $timesheet->check && $timesheet->check_id ? $timesheet->check->check_type != 'Check' ? $timesheet->check->check_type : $timesheet->check->check_number : '',
                                        'icon' => 'M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z'
                                    ],
                                ];
                            }elseif(!$timesheet->check && $timesheet->check_id && !$timesheet->vendor_id){
                                $line_details += [
                                    3 => [
                                        'text' => 'Paid By',
                                        'icon' => 'M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z'
                                    ],
                                ];
                            }
                        @endphp

                        <x-lists.search_li
                            href="{{$timesheet->check ? route('checks.show', $timesheet->check->id) : (!$timesheet->check && $timesheet->check_id ? '' : (auth()->user()->vendor->user_role == 'Admin' ? route('timesheets.payment', $timesheet->user_id) : ''))}}"
                            :line_details="$line_details"
                            :line_title="$timesheet->project->name"
                            {{-- $timesheet->check ? 'Paid' : (!$timesheet->check && $timesheet->check_id ? 'Paid By' : (auth()->user()->vendor->user_role == 'Admin' ? 'Pay' : 'Not Paid')) --}}
                            :bubble_message="$timesheet->paid_by ? 'Paid By' : ($timesheet->check_id ? 'Paid' : (auth()->user()->vendor->user_role == 'Admin' ? 'Pay' : 'Not Paid'))"
                            :bubble_color="$timesheet->paid_by || $timesheet->check_id ? 'green' : 'yellow'"
                            >
                        </x-lists.search_li>
                    @endforeach
                </x-lists.ul>
            </x-cards.wrapper>
        </div>

        <div class="col-span-4 space-y-2 lg:col-span-2">
            {{-- DAILY PROJECT HOURS --}}
            <x-cards.wrapper>
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Daily Hours</h1>
                    </x-slot>
                </x-cards.heading>

                @foreach($daily_hours as $hours)
                    <x-lists.ul>
                        <x-lists.search_li
                            {{-- wire:click="$dispatch('timesheetWeek')" --}}
                            :no_hover=true
                            :line_title="$hours->first()->date->format('l, M d, Y')"
                            :bubble_message="'Day'"
                            {{-- :class="'pointer-events-none'" --}}
                            >
                        </x-lists.search_li>
                    </x-lists.ul>
                    <x-lists.ul>
                        @foreach($hours as $hour)
                            @php
                                $line_details = [
                                    1 => [
                                        'text' => $hour->hours,
                                        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                        ],
                                    2 => [
                                        'text' => $hour->project->name,
                                        'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                        ],
                                    // 3 => [
                                    //     'text' => money($timesheet->amount),
                                    //     'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z'
                                    //     ],
                                    ];
                            @endphp

                            <x-lists.search_li
                                {{-- href="{{ route('checks.show', $expense->check->id) }}" --}}
                                :line_details="$line_details"
                                {{-- :line_title="'Hours | ' . $hour->hours" --}}
                                :bubble_message="'Hours'"
                                >
                            </x-lists.search_li>
                        @endforeach
                    </x-lists.ul>
                @endforeach
            </x-cards.wrapper>
        </div>
    </div>

</div>

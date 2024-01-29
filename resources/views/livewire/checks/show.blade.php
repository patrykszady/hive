<div>
    <x-page.top
        h1="Check for {{$check->owner}}"
        p="Check for {{$check->owner}}"
        {{-- right_button_href="{{route('checks.index')}}"
        right_button_text="Edit Check" --}}
        >
    </x-page.top>

    <div class="grid max-w-xl grid-cols-4 gap-4 mx-auto xl:relative lg:max-w-5xl sm:px-6">
        <div class="col-span-4 space-y-4 lg:col-span-2 lg:h-32 lg:sticky lg:top-5">
            <x-cards.wrapper>
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Check Details</h1>
                    </x-slot>

                    <x-slot name="right">
                        <div class="space-x-2">
                            {{-- 05-31-2023: used for testing only | kept for reference --}}
                            {{-- @if($check->vendor_id)
                                <x-cards.button
                                    wire:click="email"
                                    button_color=white
                                    >
                                    Email Details
                                </x-cards.button>
                            @endif --}}
                            <x-cards.button href="{{route('checks.index')}}">
                                Edit Check
                            </x-cards.button>
                        </div>
                    </x-slot>
                </x-cards.heading>

                <x-cards.body>
                    <x-lists.ul>
                        <x-lists.search_li
                            :basic=true
                            :line_title="'Check Payee'"
                            :line_data="$check->owner"
                            href="{{$check->vendor_id ? route('vendors.show', $check->vendor->id) : '#'}}"
                            >
                        </x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :line_title="'Check Amount'"
                            :line_data="money($check->amount)"
                            >
                        </x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :line_title="'Check Date'"
                            :line_data="$check->date->format('m/d/Y')"
                            >
                        </x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :line_title="'Check Type'"
                            :line_data="$check->check_type"
                            >
                        </x-lists.search_li>

                        @if($check->check_number)
                            <x-lists.search_li
                                :basic=true
                                :line_title="'Check Number'"
                                :line_data="$check->check_number"
                                >
                            </x-lists.search_li>
                        @endif

                        @if($check->bank_account)
                            <x-lists.search_li
                                :basic=true
                                :line_title="'Check Bank'"
                                :line_data="$check->bank_account->getNameAndType()"
                                >
                            </x-lists.search_li>
                        @endif
                    </x-lists.ul>
                </x-cards.body>
            </x-cards.wrapper>

            {{-- CHECK TRANSACTIONS --}}
            @if(!$check->transactions->isEmpty())
                <x-cards.wrapper>
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1>Transactions</h1>
                        </x-slot>
                    </x-cards.heading>

                    <x-lists.ul>
                        @foreach($check->transactions as $transaction)
                            @php
                                $line_details = [
                                    1 => [
                                        'text' => $transaction->transaction_date->format('m/d/Y'),
                                        'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                        ],
                                    2 => [
                                        'text' => $transaction->bank_account->bank->name . ' | ' . $transaction->bank_account->type,
                                        'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z'
                                        ],
                                    3 => [
                                        'text' => $transaction->plaid_merchant_description,
                                        'icon' => 'M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z'
                                        ],
                                    ];
                            @endphp

                            <x-lists.search_li
                                href=""
                                :line_details="$line_details"
                                :line_title="money($transaction->amount)"
                                :bubble_message="'Transaction'"
                                >
                            </x-lists.search_li>
                        @endforeach
                    </x-lists.ul>
                </x-cards.wrapper>
            @endif
        </div>

        <div class="col-span-4 space-y-2 lg:col-span-2">
            {{-- THIS CHECK USER PAID TIMESHEETS --}}
            @if(!$weekly_timesheets->isEmpty())
            <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1><b>{{$check->user->first_name}}</b> Timesheets</h1>
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($weekly_timesheets->groupBy('date') as $weekly_project_timesheets)
                        <x-lists.search_li
                            {{-- wire:click="$dispatch('timesheetWeek')" --}}
                            :no_hover=true
                            href="{{route('timesheets.show', $weekly_project_timesheets->first()->id)}}"
                            :line_title="'Week of ' . $weekly_project_timesheets->first()->date->startOfWeek()->toFormattedDateString() . ' | ' . money($weekly_project_timesheets->sum('amount'))"
                            :bubble_message="'Timesheets'"
                            {{-- :class="'pointer-events-none'" --}}
                            >
                        </x-lists.search_li>

                        {{-- 7-15-2022 Each foreach li shoud be a checkbox wherever it is clicked like an href --}}
                        @foreach($weekly_project_timesheets as $key => $project_timesheet)
                            @php
                                $line_details = [
                                    1 => [
                                        'text' => $project_timesheet->hours,
                                        'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                        ],
                                    2 => [
                                        'text' => $project_timesheet->project->name,
                                        'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                        ],
                                    ];

                                // $checkbox = [
                                //     'wire_click' => "like($project_timesheet->id)",
                                //     'id' => "$key",
                                //     'name' => "project_timesheet",
                                // ]
                            @endphp

                            <x-lists.search_li
                                {{-- wire:click="$dispatch('timesheetWeek')" --}}
                                {{-- :line_details="$line_details" --}}
                                href="{{route('projects.show', $project_timesheet->project->id)}}"
                                :line_title="money($project_timesheet->amount) . ' | ' . $line_details[1]['text'] . ' Hours | ' . $line_details[2]['text']"
                                :bubble_message="'Project'"
                                {{-- :checkbox="$checkbox" --}}
                                >
                            </x-lists.search_li>
                        @endforeach
                    @endforeach
                </x-lists.ul>
            </x-cards.wrapper>
            @endif

            {{-- THIS CHECK USER PAID EMPLOYEE TIMESHEETS --}}
            @if(!$employee_weekly_timesheets->isEmpty())
            <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Employee Paid Timesheets</h1>
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($employee_weekly_timesheets as $user_id => $employee_timesheet_weeks)
                        {{--  . ' | ' . $employee_timesheets_total[3] --}}
                        <x-lists.search_li
                            :no_hover=true
                            :line_title="$employee_timesheet_weeks->first()->first()->user->full_name"
                            :bubble_message="'Team Member'"
                            {{-- :class="'pointer-events-none'" --}}
                            >
                        </x-lists.search_li>

                        @foreach($employee_timesheet_weeks as $week => $employee_timesheet_week)
                            <x-lists.search_li
                                href="{{route('timesheets.show', $employee_timesheet_week->first()->id)}}"
                                :no_hover=true
                                :line_title="'Week of ' . $employee_timesheet_week->first()->date->toFormattedDateString() . ' | ' . money($employee_timesheet_week->sum('amount'))"
                                :bubble_message="'Timesheet'"
                                {{-- :class="'pointer-events-none'" --}}
                                >
                            </x-lists.search_li>
                            @foreach($employee_timesheet_week as $key => $employee_timesheet_week_project)
                                @php
                                    $line_details = [
                                        1 => [
                                            'text' => $employee_timesheet_week_project->hours,
                                            'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                            ],
                                        2 => [
                                            'text' => $employee_timesheet_week_project->project->name,
                                            'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                            ],
                                        ];

                                    // $checkbox = [
                                    //     'wire_click' => "like($project_timesheet->id)",
                                    //     'id' => "$key",
                                    //     'name' => "project_timesheet",
                                    // ]
                                @endphp

                                <x-lists.search_li
                                    {{-- wire:click="$dispatch('timesheetWeek')" --}}
                                    {{-- :line_details="$line_details" --}}
                                    href="{{route('projects.show', $employee_timesheet_week_project->project->id)}}"
                                    :line_title="money($employee_timesheet_week_project->amount)  . ' | ' . $line_details[1]['text'] . ' Hours | ' . $line_details[2]['text']"
                                    :bubble_message="'Project'"
                                    >
                                </x-lists.search_li>
                            @endforeach
                        @endforeach
                    @endforeach
                </x-lists.ul>
            </x-cards.wrapper>
            @endif

            {{-- THIS CHECK VENDOR PAID EXPENSES --}}
            {{-- @if(!is_null($vendor_paid_expenses))
            <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Vendor Paid Expenses</h1>
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($vendor_paid_expenses as $paid_expense)
                        <x-lists.search_li
                            :line_title="money($paid_expense->amount) . ' | ' . $paid_expense->project->name"
                            href="{{route('expenses.show', $paid_expense->id)}}"
                            :bubble_message="'Expense'"
                            >
                        </x-lists.search_li>
                    @endforeach
                </x-lists.ul>
            </x-cards.wrapper>
            @endif --}}

            {{-- THIS CHECK USER PAID EXPENSES --}}
            @if(!$vendor_expenses->isEmpty())
            <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Expenses</h1>
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($vendor_expenses as $expense)
                        <x-lists.search_li
                            {{-- wire:click="$dispatch('timesheetWeek')" --}}
                            {{-- :line_details="$line_details" --}}
                            :line_title="money($expense->amount) . ' | ' . $expense->project->name"
                            href="{{route('expenses.show', $expense->id)}}"
                            href_target="blank"
                            :bubble_message="'Expense'"
                            >
                        </x-lists.search_li>
                    @endforeach
                </x-lists.ul>
            </x-cards.wrapper>
            @endif

            {{-- THIS CHECK USER PAID EXPENSES --}}
            @if(!$user_paid_expenses->isEmpty())
            <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Paid Expenses</h1>
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($user_paid_expenses as $paid_expense)
                        <x-lists.search_li
                            {{-- wire:click="$dispatch('timesheetWeek')" --}}
                            {{-- :line_details="$line_details" --}}
                            :line_title="money($paid_expense->amount) . ' | ' . $paid_expense->project->name"
                            href="{{route('expenses.show', $paid_expense->id)}}"
                            href_target="blank"
                            :bubble_message="'Expense'"
                            {{-- :checkbox="$checkbox" --}}
                            >
                        </x-lists.search_li>
                    @endforeach
                </x-lists.ul>
            </x-cards.wrapper>
            @endif

            {{-- THIS CHECK DISTRIBUTIONS --}}
            @if(!$user_distributions->isEmpty())
            <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Paid Distrbutions</h1>
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($user_distributions as $user_distribution_expense)
                        <x-lists.search_li
                            :href="route('expenses.show', $user_distribution_expense)"
                            :line_title="money($user_distribution_expense->amount) . ' | ' . $user_distribution_expense->distribution->name"
                            :bubble_message="'Distribution'"
                            >
                        </x-lists.search_li>
                    @endforeach
                </x-lists.ul>
            </x-cards.wrapper>
            @endif

            {{-- THIS CHECK USER PAID REIMBURESEMENT RECEIPTS FROM ANOTHER EMPLOYEE --}}
            @if(!$user_paid_reimburesements->isEmpty())
            <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Paid Employee Reimbursements</h1>
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($user_paid_reimburesements as $user_distribution_expense)
                        <x-lists.search_li
                            :href="route('expenses.show', $user_distribution_expense)"
                            href_target="blank"
                            {{-- . ' | ' . $user_distribution_expense->vendor->name  --}}
                            {{--  . ' | ' . $user_distribution_expense->distribution->name --}}
                            :line_title="money($user_distribution_expense->amount)"
                            :bubble_message="'Reimbursement'"
                            >
                        </x-lists.search_li>
                    @endforeach
                </x-lists.ul>
            </x-cards.wrapper>
            @endif
        </div>
    </div>
</div>




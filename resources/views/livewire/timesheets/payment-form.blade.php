<form wire:submit="{{$view_text['form_submit']}}">
    <x-page.top
		h1="Pay {{ $form->first_name }}"
		p="{{ $form->first_name }}'s outstanding payments from {!! $form->via_vendor_back->business_name !!}"
		right_button_href="{{route('timesheets.index')}}"
		right_button_text="View Timesheets"
		>
	</x-page.top>

    <div class="grid max-w-xl grid-cols-5 gap-4 mx-auto xl:relative lg:max-w-5xl sm:px-6">
        <div class="col-span-5 space-y-4 lg:col-span-2 lg:h-32 lg:sticky lg:top-5">
            <x-cards.wrapper>
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Payment</h1>
                        <p class="text-gray-500"><i>Create a Payment for {{$form->payee_name}}</i></p>
                    </x-slot>
                </x-cards.heading>

                <x-cards.body :class="'space-y-2 my-2'">
                    {{-- ROWS --}}
                    <x-forms.row
                        wire:model.live="form.payee_name"
                        errorName="form.payee_name"
                        name="payee_name"
                        text="Payee"
                        type="text"
                        disabled
                        >
                    </x-forms.row>

                    @include('livewire.checks._payment_form')

                </x-cards.body>

                <x-cards.footer>
                    <div class="w-full space-y-1 text-center">
                        <button
                            type="button"
                            class="w-full px-4 py-2 text-lg font-medium text-center text-gray-900 border-2 border-indigo-600 rounded-md shadow-sm focus:outline-none">
                            Check Total | <b>{{money($this->weekly_timesheets_total)}}</b>
                        </button>

                        <x-forms.error errorName="weekly_timesheets_total" />

                        <button
                            type="submit"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow focus:outline-none hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                            {{$view_text['button_text']}}
                        </button>
                    </div>
                </x-cards.footer>
            </x-cards.wrapper>
        </div>

        <div class="col-span-5 space-y-2 lg:col-span-3">
            {{-- USER UNPAID TIMESHEETS --}}
            @if(!$weekly_timesheets->isEmpty())
                <x-cards.wrapper class="col-span-4 lg:col-span-2">
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1><b>{{ $user->first_name }}</b>'s Timesheets</h1>
                        </x-slot>
                    </x-cards.heading>

                    <x-lists.ul>
                        @foreach($weekly_timesheets->groupBy(function ($each) {
                                return $each->date->startOfWeek()->toFormattedDateString();
                            }, true) as $week_date => $weekly_project_timesheets)

                            <x-lists.search_li
                                href="{{route('timesheets.show', $weekly_project_timesheets->first()->id)}}"
                                :href_target="'_blank'"
                                :no_hover=true
                                :line_title="'Week of ' . $week_date . ' | ' . money($weekly_project_timesheets->sum('amount'))"
                                :bubble_message="'Timesheets'"
                                {{-- :class="'pointer-events-none'" --}}
                                >
                            </x-lists.search_li>

                            @foreach($weekly_project_timesheets as $timesheet_id => $project_timesheet)
                                {{-- 7-15-2022 Each foreach li shoud be a checkbox wherever it is clicked like an href --}}
                                @php
                                    $line_details = [
                                        1 => [
                                            'text' => $project_timesheet->hours,
                                            'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                            ],
                                        2 => [
                                            'text' => $project_timesheet->project->project_name,
                                            'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                            ],
                                        ];
                                    //radio button
                                    $checkbox = [
                                        // checked vs unchecked
                                        'id' => "$timesheet_id",
                                        'name' => "weekly_timesheets",
                                    ];
                                @endphp

                                <x-lists.search_li
                                    :line_title="money($project_timesheet->amount) . ' | ' . $line_details[1]['text'] . ' Hours | ' . $line_details[2]['text']"
                                    :bubble_message="'Project'"
                                    :checkbox="$checkbox"
                                    >
                                </x-lists.search_li>
                            @endforeach
                        @endforeach
                    </x-lists.ul>
                </x-cards.wrapper>
            @endif

            {{-- USER PAID EMPLOYEE TIMESHEETS --}}
            @if(!$employee_weekly_timesheets->isEmpty())
                <x-cards.wrapper class="col-span-4 lg:col-span-2">
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1><b>{{ $user->first_name }}</b> Paid Timesheets</h1>
                        </x-slot>
                    </x-cards.heading>

                    <x-lists.ul>
                        @foreach($this->employee_weekly_timesheets->groupBy('date') as $week_key => $weekly_project_timesheets)

                        <x-lists.search_li
                            :no_hover=true
                            :line_title="'Week of ' . $weekly_project_timesheets->first()->date->startOfWeek()->toFormattedDateString() . ' | ' . money($weekly_project_timesheets->sum('amount'))"
                            :bubble_message="'Timesheets'"
                            {{-- :class="'pointer-events-none'" --}}
                            >
                        </x-lists.search_li>
                            {{-- 7-15-2022 Each foreach li shoud be a checkbox wherever it is clicked like an href --}}
                            @foreach($employee_weekly_timesheets->where('date', $week_key) as $key => $project_timesheet)
                                @php
                                    $line_details = [
                                        1 => [
                                            'text' => $project_timesheet->hours,
                                            'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                            ],
                                        2 => [
                                            'text' => $project_timesheet->project->project_name,
                                            'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                            ],
                                        ];
                                    //radio button
                                    $checkbox = [
                                        // checked vs unchecked
                                        'id' => "$key",
                                        'name' => "employee_weekly_timesheets",
                                    ];
                                @endphp

                                <x-lists.search_li
                                    {{-- :line_details="$line_details" --}}
                                    href="{{route('timesheets.show', $project_timesheet->id)}}"
                                    :href_target="'_blank'"
                                    :line_title="money($project_timesheet->amount) . ' | ' . $project_timesheet->user->first_name . ' | ' . $line_details[1]['text'] . ' Hours | ' . $line_details[2]['text']"
                                    :bubble_message="'Project'"
                                    :checkbox="$checkbox"
                                    >
                                </x-lists.search_li>
                            @endforeach
                        @endforeach
                    </x-lists.ul>
                </x-cards.wrapper>
            @endif

            {{-- USER PAID FOR EXPENSES --}}
            @if(!$user_paid_expenses->isEmpty())
                <x-cards.wrapper class="col-span-4 lg:col-span-2">
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1><b>{{ $user->first_name }}</b> Paid For Expenses</h1>
                        </x-slot>
                    </x-cards.heading>

                    <x-lists.ul>
                        @foreach($user_paid_expenses as $key => $expense)
                            @php
                                // $line_details = [
                                //     1 => [
                                //         'text' => $project_timesheet->hours,
                                //         'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                //         ],
                                //     2 => [
                                //         'text' => $project_timesheet->project->project_name,
                                //         'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                //         ],
                                //     ];
                                //radio button
                                $checkbox = [
                                    // checked vs unchecked
                                    'id' => "$key",
                                    'name' => "user_paid_expenses",
                                ];
                            @endphp

                            <x-lists.search_li
                                {{-- wire:click="$dispatch('timesheetWeek')" --}}
                                {{-- :line_details="$line_details" --}}
                                href="{{route('expenses.show', $expense->id)}}"
                                :href_target="'_blank'"
                                :line_title="money($expense->amount) . ' | ' . $expense->vendor->business_name"
                                :bubble_message="'Expense'"
                                :checkbox="$checkbox"
                                >
                            </x-lists.search_li>
                        @endforeach
                    </x-lists.ul>
                </x-cards.wrapper>
            @endif

            {{-- USER REIMBURESEMENT EXPENSES --}}
            @if(!$user_reimbursement_expenses->isEmpty())
                <x-cards.wrapper class="col-span-4 lg:col-span-2">
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1><b>{{ $user->first_name }}</b> Owns {!! $user->via_vendor_back->name !!}</h1>
                        </x-slot>
                    </x-cards.heading>

                    <x-lists.ul>
                        @foreach($user_reimbursement_expenses as $key => $expense)
                            @php
                                // $line_details = [
                                //     1 => [
                                //         'text' => $project_timesheet->hours,
                                //         'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                //         ],
                                //     2 => [
                                //         'text' => $project_timesheet->project->project_name,
                                //         'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                //         ],
                                //     ];
                                //radio button
                                $checkbox = [
                                    // checked vs unchecked
                                    'id' => "$expense->id",
                                    'name' => "user_reimbursement_expenses",
                                ];
                            @endphp

                            <x-lists.search_li
                                {{-- wire:click="$dispatch('timesheetWeek')" --}}
                                {{-- :line_details="$line_details" --}}
                                href="{{route('expenses.show', $expense->id)}}"
                                :href_target="'_blank'"
                                {{-- '-' .  --}}
                                :line_title="money($expense->amount) . ' | ' . $expense->vendor->name"
                                :bubble_message="'Expense'"
                                :checkbox="$checkbox"
                                >
                            </x-lists.search_li>
                        @endforeach
                    </x-lists.ul>
                </x-cards.wrapper>
            @endif

            {{-- USER PAID REIMBURESEMENT EXPENSES FROM ANOHTER USER --}}
            @if(!$this->user_paid_by_reimbursements->isEmpty())
                <x-cards.wrapper class="col-span-4 lg:col-span-2">
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1><b>{{ $user->first_name }}</b> Paid Reimbursement Expenses</h1>
                        </x-slot>
                    </x-cards.heading>

                    <x-lists.ul>
                        @foreach($this->user_paid_by_reimbursements as $key => $expense)
                            @php
                                // $line_details = [
                                //     1 => [
                                //         'text' => $project_timesheet->hours,
                                //         'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                //         ],
                                //     2 => [
                                //         'text' => $project_timesheet->project->project_name,
                                //         'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                //         ],
                                //     ];
                                //radio button
                                $checkbox = [
                                    // checked vs unchecked
                                    'id' => "$key",
                                    'name' => "user_paid_by_reimbursements",
                                ];
                            @endphp

                            <x-lists.search_li
                                {{-- wire:click="$dispatch('timesheetWeek')" --}}
                                {{-- :line_details="$line_details" --}}
                                href="{{route('expenses.show', $expense->id)}}"
                                :href_target="'_blank'"
                                :line_title="money($expense->amount) . ' | ' . $expense->vendor->business_name . ' | ' . $expense->reimbursment"
                                :bubble_message="'Expense'"
                                :checkbox="$checkbox"
                                >
                            </x-lists.search_li>
                        @endforeach
                    </x-lists.ul>
                </x-cards.wrapper>
            @endif
        </div>
    </div>
</form>



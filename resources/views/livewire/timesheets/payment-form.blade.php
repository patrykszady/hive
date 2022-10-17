<form wire:submit.prevent="{{$view_text['form_submit']}}">
    <x-page.top
		h1="Pay {{ $user->first_name }}"
		p="{{ $user->full_name }}'s outstanding payments from {!! $user->vendor->business_name !!} "
		right_button_href="{{route('timesheets.index')}}"
		right_button_text="View Timesheets"
		>
	</x-page.top>

    <div class="xl:relative max-w-xl lg:max-w-5xl grid grid-cols-5 gap-4 sm:px-6 mx-auto">
        <div class="col-span-5 lg:col-span-2 lg:h-32 lg:sticky lg:top-5 space-y-4">
            <x-cards.wrapper>
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Payment</h1>
                        <p class="text-gray-500"><i>Create a Payment for User {{$user->full_name}}</i></p>
                    </x-slot>
                </x-cards.heading>

                <x-cards.body :class="'space-y-2 my-2'">
                    {{-- FORM --}}
                    {{-- ROWS --}}    
                    <x-forms.row 
                        wire:model="user.full_name"
                        errorName="user.full_name"
                        name="user.full_name" 
                        text="Payee"
                        type="text"  
                        disabled
                        >
                    </x-forms.row>

                    @include('livewire.checks._payment_form')

                    {{-- , ['project' => $project->id] --}}
                    {{-- @livewire('checks.checks-form') --}}
                </x-cards.body>          
                        
                <x-cards.footer>
                    <div class="text-center space-y-1 w-full">
                        <a 
                            type="button"
                            class="text-center focus:outline-none w-full rounded-md border-2 border-indigo-600 py-2 px-4 text-lg font-medium text-gray-900 shadow-sm">
                            Check Total | <b>{{money($this->weekly_timesheets_total)}}</b>                          
                        </a>
                        <button 
                            type="submit"
                            class="focus:outline-none mt-8 w-full rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{$view_text['button_text']}}                      
                        </button>
                    </div>
                </x-cards.footer>
            </x-cards.wrapper>
        </div>

        <div class="col-span-5 lg:col-span-3 space-y-2">
            {{-- USER UNPAID TIMESHEETS --}}
            @if(!$this->weekly_timesheets->isEmpty())
                <x-cards.wrapper class="col-span-4 lg:col-span-2">
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1>Unpaid <b>{{ $user->first_name }}</b> Timesheets</h1>
                        </x-slot>
                    </x-cards.heading>
            
                    <x-lists.ul>                        
                        @foreach($this->weekly_timesheets
                            //8-24-2022 why the groupBy here?
                            // ->groupBy(function ($each) {
                            //     return $each->date->format('Y-m-d');
                            // }) as $week_key => $weekly_project_timesheets

                            //->values()
                            ->groupBy('date') as $week_key => $weekly_project_timesheets
                            )

                        <x-lists.search_li
                            {{-- wire:click="$emit('timesheetWeek')" --}}  
                            href="{{route('timesheets.show', $weekly_project_timesheets->first()->id)}}"                              
                            :no_hover=true
                            :line_title="'Week of ' . $weekly_project_timesheets->first()->date->startOfWeek()->toFormattedDateString() . ' | ' . money($weekly_project_timesheets->sum('amount'))"
                            :bubble_message="'Timesheets'"
                            {{-- :class="'pointer-events-none'" --}}
                            >
                        </x-lists.search_li>
                            {{-- 7-15-2022 Each foreach li shoud be a checkbox wherever it is clicked like an href --}}
                            @foreach($this->weekly_timesheets->where('date', $week_key) as $key => $project_timesheet)
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
                                        'wire_click' => "checkbox($project_timesheet->id)",
                                        'id' => "$key",
                                        'name' => "weekly_timesheets",
                                    ];
                                @endphp

                                <x-lists.search_li
                                    {{-- wire:click="$emit('timesheetWeek')" --}}
                                    {{-- :line_details="$line_details" --}}
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
            @if(!$this->employee_weekly_timesheets->isEmpty())
            <x-cards.wrapper class="col-span-4 lg:col-span-2">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1><b>{{ $user->first_name }}</b> Paid By Timesheets</h1>
                    </x-slot>
                </x-cards.heading>
            
                <x-lists.ul>                        
                    @foreach($this->employee_weekly_timesheets
                        //8-24-2022 why the groupBy here?
                        // ->groupBy(function ($each) {
                        //     return $each->date->format('Y-m-d');
                        // }) as $week_key => $weekly_project_timesheets
            
                        //->values()
                        ->groupBy('date') as $week_key => $weekly_project_timesheets
                    )
            
                    <x-lists.search_li
                        {{-- wire:click="$emit('timesheetWeek')" --}}                                
                        :no_hover=true
                        :line_title="'Week of ' . $weekly_project_timesheets->first()->date->startOfWeek()->toFormattedDateString() . ' | ' . money($weekly_project_timesheets->sum('amount'))"
                        :bubble_message="'Timesheets'"
                        {{-- :class="'pointer-events-none'" --}}
                        >
                    </x-lists.search_li>
                        {{-- 7-15-2022 Each foreach li shoud be a checkbox wherever it is clicked like an href --}}
                        @foreach($this->employee_weekly_timesheets->where('date', $week_key) as $key => $project_timesheet)
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
                                    'wire_click' => "checkbox($project_timesheet->id)",
                                    'id' => "$key",
                                    'name' => "employee_weekly_timesheets",
                                ];
                            @endphp
            
                            <x-lists.search_li
                                {{-- wire:click="$emit('timesheetWeek')" --}}
                                {{-- :line_details="$line_details" --}}
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

            {{-- USER PAID EXPENSES --}}
            @if(!$this->user_paid_expenses->isEmpty())
            <x-cards.wrapper class="col-span-4 lg:col-span-2">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1><b>{{ $user->first_name }}</b> Paid By Expenses</h1>
                    </x-slot>
                </x-cards.heading>
            
                <x-lists.ul>                        
                    @foreach($this->user_paid_expenses as $key => $expense)
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
                                'wire_click' => "checkbox($expense->id)",
                                'id' => "$key",
                                'name' => "user_paid_expenses",
                            ];
                        @endphp
        
                        <x-lists.search_li
                            {{-- wire:click="$emit('timesheetWeek')" --}}
                            {{-- :line_details="$line_details" --}}
                            :line_title="money($expense->amount)"
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



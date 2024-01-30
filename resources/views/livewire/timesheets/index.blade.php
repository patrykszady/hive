@section('title','Hive Timesheets')
<div class="max-w-xl mx-auto sm:px-6">
    {{-- 7-2-2022 dont show if no timesheets to confirm --}}
    <x-cards.wrapper>
        {{-- HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Confirm Weekly Timesheets</h1>
            </x-slot>

            <x-slot name="right">
                <x-cards.button href="{{route('hours.create')}}">
                    Add Hours
                </x-cards.button>
            </x-slot>
        </x-cards.heading>

        {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
        <x-lists.ul>
            @foreach($weekly_hours_to_confirm as $name => $user_weeks)
                <x-lists.search_li
                    {{-- wire:click="$dispatch('timesheetWeek')" --}}
                    :no_hover=true
                    {{-- href="{{route('timesheets.show', $weekly_project_timesheets->first()->id)}}" --}}
                    :line_title="$name"
                    :bubble_message="'Team Member'"
                    {{-- :class="'pointer-events-none'" --}}
                    >
                </x-lists.search_li>

                @foreach($user_weeks as $week => $user_week)
                    <x-lists.search_li
                        href="{{route('timesheets.create', $user_week->first()->id)}}"
                        :line_title="$week . ' | ' . $user_week->sum_hours . ' Hours'"
                        :bubble_message="'Confirm'"
                        :bubble_color="'yellow'"
                        >
                    </x-lists.search_li>
                @endforeach
            @endforeach
        </x-lists.ul>
    </x-cards.wrapper>

    @if(!$confirmed_weekly_hours->isEmpty())
        <br>
        <x-cards.wrapper>
            {{-- HEADING --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>Confirmed Weekly Timesheets</h1>
                </x-slot>

                <x-slot name="right">
                    {{-- <x-cards.button href="{{route('hours.create')}}">
                        Add Hours
                    </x-cards.button> --}}
                </x-slot>
            </x-cards.heading>

            {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
            <x-lists.ul>
                @foreach($confirmed_weekly_hours as $week => $week_users)
                    <x-lists.ul>
                        <x-lists.search_li
                            {{-- wire:click="$dispatch('timesheetWeek')" --}}
                            :no_hover=true
                            :line_title="$week"
                            :bubble_message="'Week'"
                            {{-- :class="'pointer-events-none'" --}}
                            >
                        </x-lists.search_li>
                    </x-lists.ul>
                    @foreach($week_users as $user_name => $week_user)
                        @php
                            $line_details = [
                                1 => [
                                    'text' => $week_user->sum_hours,
                                    'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                    ],
                                2 => [
                                    'text' => money($week_user->sum_amount),
                                    'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z'
                                    ],
                                ];
                        @endphp

                        <x-lists.search_li
                            {{-- href="{{route('timesheets.create', $week_hours->first())}}" --}}
                            :line_details="$line_details"
                            :line_title="$user_name"
                            :bubble_message="'Confirmed Week'"
                            :bubble_color="'green'"
                            :href_target="'_blank'"
                            href="{{route('timesheets.show', $week_user->first()->id)}}"
                            >
                        </x-lists.search_li>
                    @endforeach
                @endforeach
            </x-lists.ul>

            {{-- FOOTER --}}
            <x-cards.footer>
                {{-- 10/14/21 change/customize links view in resources/views/vendor/pagination/tailwind.blade.php to match our
                theme --}}
                {{ $confirmed_weekly_hours->links() }}
            </x-cards.footer>
        </x-cards.wrapper>
    @endif
</div>

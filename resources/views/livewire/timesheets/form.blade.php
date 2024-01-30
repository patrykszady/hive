<div>
    <x-cards.wrapper class="max-w-xl px-4 pb-5 mb-1 sm:px-6">
        {{-- HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Confirm week of <b>{{$week_date}}</b> for {{$user->first_name}}</h1>
            </x-slot>

            <x-slot name="right">
                <x-cards.button href="{{route('hours.create')}}">
                    Add Hours
                </x-cards.button>
            </x-slot>
        </x-cards.heading>
    </x-cards.wrapper>

    {{-- EACH PROJECT DURING WEEK & DAY --}}
    @foreach($weekly_days as $weekly_day => $daily_projects)
        <x-cards.wrapper class="max-w-xl px-4 pb-5 mb-1 sm:px-6">
            {{-- HEADING --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{ \Carbon\Carbon::parse($weekly_day)->format('l, F jS Y') }}</h1>
                </x-slot>

                <x-slot name="right">
                    {{-- 7-2-2022 SEND TO hours.create DATE = $this date --}}
                    {{-- <x-cards.button href="{{route('hours.create')}}">
                        Edit Hours
                    </x-cards.button> --}}
                </x-slot>
            </x-cards.heading>

            {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
            <x-lists.ul>
                @foreach($daily_projects as $project_name => $daily_project)
                    <x-lists.search_li
                        :line_title="'Hours: ' . $daily_project->sum('hours') . ' | ' .  $daily_project->first()->project->name"
                        :bubble_message="'Hours'"
                        >
                    </x-lists.search_li>
                @endforeach
            </x-lists.ul>
        </x-cards.wrapper>
    @endforeach

    {{-- user info/ confirm/ change hourly if you can update Hours/Timesheets...ONLY if you Admin --}}
    <form wire:submit="save">
        <x-cards.wrapper class="max-w-xl px-4 pb-5 mb-1 sm:px-6">
            <x-cards.heading>
                <x-slot name="left">
                    <h1>Timesheet User Details</h1>
                    <p class="text-gray-500"><i>Confirm Timesheet Info for {{$user->first_name}}</i></p>
                </x-slot>
            </x-cards.heading>

            <x-cards.body :class="'space-y-2 my-2'">
                {{-- ROWS --}}
                <x-forms.row
                    wire:model="form.full_name"
                    errorName="form.full_name"
                    name="full_name"
                    text="Payee"
                    type="text"
                    disabled
                    >
                </x-forms.row>

                <x-forms.row
                    wire:model="form.hours"
                    errorName="form.hours"
                    name="hours"
                    text="Hours"
                    type="text"
                    textSize="xl"
                    hint=" "
                    disabled
                    >
                </x-forms.row>

                {{-- is user admin and not Timesheet being confirmed owner? not disabled.
                    is Member or admin confirming own timesheets? disabled --}}
                <x-forms.row
                    wire:model.live.debounce.500ms="form.hourly"
                    errorName="form.hourly"
                    name="hourly"
                    text="Hourly"
                    type="number"
                    inputmode="numeric"
                    step="0.25"
                    hint="$"
                    :disabled="$user->user_role == 'Member' ? true : ($user->logged_in ? true : false)"
                    >
                </x-forms.row>

                <x-forms.row
                    wire:model="form.amount"
                    errorName="form.amount"
                    name="form.amount"
                    text="Amount"
                    type="text"
                    textSize="xl"
                    hint="$"
                    disabled
                    >
                </x-forms.row>
            </x-cards.body>

            <x-cards.footer>
                <div class="w-full space-y-1 text-center">
                    <button
                        type="button"
                        class="w-full px-4 py-2 text-lg font-medium text-center text-gray-900 border-2 border-indigo-600 rounded-md shadow-sm focus:outline-none">
                        Total Amount | <b>{{money($this->user_hours_amount)}}</b>
                    </button>
                    <button
                        type="submit"
                        class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow focus:outline-none hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Confirm Weekly Timesheet
                    </button>
                </div>
            </x-cards.footer>
        </x-cards.wrapper>
    </form>
</div>

<div>
    {{-- md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-3xl lg:px-8  --}}
    {{-- class="max-w-xl px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-3xl lg:px-8 pb-5 mb-1" --}}
    <x-cards.wrapper class="max-w-xl px-4 sm:px-6 pb-5 mb-1">
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
        <x-cards.wrapper class="max-w-xl px-4 sm:px-6 pb-5 mb-1">
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
    <form wire:submit.prevent="store">
    <x-cards.wrapper class="max-w-xl px-4 sm:px-6 pb-5 mb-1">
        <x-cards.heading>
            <x-slot name="left">
                <h1>Timesheet User Details</h1>
                <p class="text-gray-500"><i>Confirm Timesheet Info for {{$user->first_name}}</i></p>
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

                <x-forms.row 
                    wire:model="user.hours"
                    errorName="user.hours"
                    name="user.hours" 
                    text="Hours"
                    type="text"
                    textSize="xl"  
                    hint=" "
                    disabled
                    >
                </x-forms.row>

                {{-- is user admin and not Timesheet being confirmed owner? not disabled. 
                    is Member or admin confirming own timesheets? disabled --}}
                @if($user->id == auth()->user()->id || $user->vendor->user_role == 'Member')
                    <x-forms.row 
                        wire:model="user.hourly"
                        errorName="user.hourly"
                        name="user.hourly" 
                        text="Hourly"
                        type="number" 
                        inputmode="numeric" 
                        step="0.25"
                        hint="$" 
                        disabled
                        {{-- {{$user->vendor->user_role == 'Admin' ? true : false}} --}}
                        >
                    </x-forms.row>
                @else
                    <x-forms.row 
                        wire:model="user.hourly"
                        errorName="user.hourly"
                        name="user.hourly" 
                        text="Hourly"
                        type="number" 
                        inputmode="numeric" 
                        step="0.25"
                        hint="$" 
                        {{-- {{$user->vendor->user_role == 'Admin' ? true : false}} --}}
                        >
                    </x-forms.row>
                @endif

                    <x-forms.row 
                        wire:model="user.amount"
                        errorName="user.amount"
                        name="user.amount" 
                        text="Amount"
                        type="text"
                        textSize="xl" 
                        hint="$" 
                        disabled
                        >
                    </x-forms.row>
                {{-- , ['project' => $project->id] --}}
                {{-- @livewire('checks.checks-form') --}}
            </x-cards.body>          
                    
            <x-cards.footer>
                <div class="text-center space-y-1 w-full">
                    <a 
                        type="button"
                        class="text-center focus:outline-none w-full rounded-md border-2 border-indigo-600 py-2 px-4 text-lg font-medium text-gray-900 shadow-sm">
                        Total Amount | <b>{{money($this->user_hours_amount)}}</b>                          
                    </a>
                    <button 
                        type="submit"
                        class="focus:outline-none w-full rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Confirm Weekly Timesheet                    
                    </button>
                </div>
            </x-cards.footer>        
    </x-cards.wrapper>
</form>
</div>
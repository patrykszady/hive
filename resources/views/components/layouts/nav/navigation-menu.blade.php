{{-- TOP LOGO --}}
<div class="flex items-center h-16 mt-1 shrink-0">
    <a href="{{ route('dashboard') }}">
        <img class="w-auto h-12 mx-auto" src="{{ asset('favicon.png') }}" alt="{{ env('APP_NAME') }}">
    </a>
    <a href="{{ route('dashboard') }}">
        <h3 class="pl-3">Hive Contractors</h3>
    </a>
</div>

{{-- @persist('navigation') --}}
<nav class="flex flex-col flex-1">
	<ul role="list" class="flex flex-col flex-1 gap-y-7">
        {{-- GLOBAL NOTIFICATIONS --}}

        @if(!request()->routeIs(['vendor_registration', 'vendor_selection']))
            {{-- BANK ERRORS --}}
            @can('viewAny', App\Models\Bank::class)
                @if(!auth()->user()->vendor->banks()->whereNotNull('plaid_access_token')->get()->where('plaid_options.error', '!=', FALSE)->isEmpty())
                    <li>
                        <a href="{{route('banks.index')}}" class="flex p-2 text-sm leading-6 text-red-400 rounded-md hover:text-white hover:bg-red-700 group gap-x-3">
                            <svg class="w-6 h-6 text-red-400 shrink-0 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                            </svg>
                            Banks
                            <span class="ml-auto w-9 min-w-max whitespace-nowrap rounded-full bg-red-600 px-2.5 py-0.5 text-center text-xs font-medium leading-5 text-white ring-1 ring-inset ring-red-500" aria-hidden="true">Error</span>
                        </a>
                    </li>
                @endif
            @endcan

            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <!-- Current: "bg-gray-50", Default: "hover:bg-gray-50" -->
                        <a href="{{route('dashboard')}}"
                            @class(['flex p-2 text-sm leading-6 text-gray-700 rounded-md group gap-x-3 hover:bg-gray-50', 'bg-gray-50' => request()->routeIs('dashboard')])
                            >
                            <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            <b class="truncate">{{ auth()->user()->vendor->name }}</b>
                        </a>
                    </li>
                    <li>
                        <!-- Current: "bg-gray-50", Default: "hover:bg-gray-50" -->
                        <a href="{{route('projects.index')}}"
                            @class(['flex p-2 text-sm leading-6 text-gray-700 font-semibold rounded-md group gap-x-3 hover:bg-gray-50', 'bg-gray-50' => request()->routeIs('projects.*')])
                            >
                            <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                            </svg>
                            Projects
                        </a>
                    </li>

                    @canany(['viewAny', 'create'], App\Models\Expense::class)
                    <li x-data="{expanded_expenses: false}">
                        <button type="button"
                            @class(['flex items-center w-full p-2 text-sm font-semibold leading-6 text-left text-gray-700 rounded-md hover:bg-gray-50 gap-x-3', 'bg-gray-200' => request()->routeIs('expenses.*') || request()->routeIs('checks.*')])
                            aria-controls="sub-menu-1"
                            aria-expanded="false"
                            x-on:click="expanded_expenses = !expanded_expenses"
                            >
                            <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Expenses
                            <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true" x-show="expanded_expenses">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true" x-show="!expanded_expenses">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <!-- Expandable link section, show/hide based on state. -->
                        <ul class="px-2 mt-1" id="sub-menu-1" x-show="expanded_expenses" x-collapse>
                            <li>
                                <!-- 44px -->
                                <a href="{{route('expenses.index')}}"
                                    @class(['block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9', 'bg-gray-200 border-2 border-indigo-500' => request()->routeIs('expenses.index')])
                                    >
                                    All Expenses
                                </a>
                            </li>
                            <li>
                                <!-- 44px -->
                                <a href="{{route('checks.index')}}"
                                    @class(['block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9', 'bg-gray-200 border-2 border-indigo-400' => request()->routeIs('checks.index')])
                                    >
                                    Checks
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endcanany

                    <li x-data="{expanded_vendors: false}">
                        <button type="button"
                            @class(['flex items-center w-full p-2 text-sm font-semibold leading-6 text-left text-gray-700 rounded-md hover:bg-gray-50 gap-x-3', 'bg-gray-200' => request()->routeIs('vendors.*') || request()->routeIs('vendor_docs.index')])
                            aria-controls="sub-menu-2" aria-expanded="false"
                            x-on:click="expanded_vendors = !expanded_vendors">
                            <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true" >
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Vendors
                            <!-- Expanded: "rotate-90 text-gray-500", Collapsed: "text-gray-400" -->
                            <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true" x-show="expanded_vendors">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true" x-show="!expanded_vendors">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <!-- Expandable link section, show/hide based on state. -->
                        <ul class="px-2 mt-1" id="sub-menu-2" x-show="expanded_vendors" x-collapse>
                            <li>
                                <!-- 44px -->
                                <a href="{{route('vendors.index')}}"
                                    class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                    All Vendors
                                </a>
                            </li>
                            @can('create', App\Models\Vendor::class)
                            <li>
                                <!-- 44px -->
                                <a href="{{route('vendor_docs.index')}}"
                                    class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                    Insurance Certificates
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>

                    <li x-data="{expanded_timesheets: false}">
                        <button type="button"
                            @class(['flex items-center w-full p-2 text-sm font-semibold leading-6 text-left text-gray-700 rounded-md hover:bg-gray-50 gap-x-3', 'bg-gray-200' => request()->routeIs('timesheets.*') || request()->routeIs('hours.*')])
                            aria-controls="sub-menu-3" aria-expanded="false"
                            x-on:click="expanded_timesheets = !expanded_timesheets"
                            >
                            <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Timesheets
                            <!-- Expanded: "rotate-90 text-gray-500", Collapsed: "text-gray-400" -->
                            <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true" x-show="expanded_timesheets">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                            <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true" x-show="!expanded_timesheets">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <!-- Expandable link section, show/hide based on state. -->
                        <ul class="px-2 mt-1" id="sub-menu-3" x-show="expanded_timesheets" x-collapse>
                            <li>
                                <!-- 44px -->
                                <a href="{{route('hours.create')}}"
                                    class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                    Hours
                                </a>
                            </li>
                            <li>
                                <!-- 44px -->
                                <a href="{{route('timesheets.index')}}"
                                    class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                    Timesheets
                                </a>
                            </li>

                            @can('viewPayment', App\Models\Timesheet::class)
                            <li>
                                <!-- 44px -->
                                <a href="{{route('timesheets.payments')}}"
                                    class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                    Payments
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </li>

                    <li>
                        <!-- Current: "bg-gray-50", Default: "hover:bg-gray-50" -->
                        <a href="{{route('clients.index')}}"
                            @class(['flex p-2 text-sm font-semibold leading-6 text-gray-700 rounded-md group gap-x-3 hover:bg-gray-50', 'bg-gray-50' => request()->routeIs('clients.*')])
                            >
                            <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            Clients
                        </a>
                    </li>

                    @can('viewAny', App\Models\Bank::class)
                        <li x-data="{expanded_finances: false}">
                            <button type="button"
                                @class(['flex items-center w-full p-2 text-sm font-semibold leading-6 text-left text-gray-700 rounded-md hover:bg-gray-50 gap-x-3', 'bg-gray-200' => request()->routeIs('distributions.*') || request()->routeIs('banks.*') || request()->routeIs('company_emails.*')])
                                aria-controls="sub-menu-4"
                                aria-expanded="false"
                                x-on:click="expanded_finances = !expanded_finances"
                                >
                                <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                Finances
                                <!-- Expanded: "rotate-90 text-gray-500", Collapsed: "text-gray-400" -->
                                <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true" x-show="expanded_finances">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                                <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true" x-show="!expanded_finances">
                                    <path fill-rule="evenodd"
                                        d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <!-- Expandable link section, show/hide based on state. -->
                            <ul class="px-2 mt-1" id="sub-menu-4" x-show="expanded_finances" x-collapse>
                                <li>
                                    <!-- 44px -->
                                    <a href="{{route('distributions.index')}}"
                                        class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                        Distributions
                                    </a>
                                </li>
                                <li>
                                    <!-- 44px -->
                                    <a href="{{route('banks.index')}}"
                                        class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                        Banks
                                    </a>
                                </li>
                                <li>
                                    <!-- 44px -->
                                    <a href="{{route('company_emails.index')}}"
                                        class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                        Company Emails
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endcan

                    @if(auth()->user()->id == 1)
                        <li x-data="{expanded_global_actions: false}">
                            <button type="button"
                                @class(['flex items-center w-full p-2 text-sm font-semibold leading-6 text-left text-gray-700 rounded-md hover:bg-gray-50 gap-x-3', 'bg-gray-200' => request()->routeIs('transactions.*')])
                                aria-controls="sub-menu-5"
                                aria-expanded="false"
                                x-on:click="expanded_global_actions = !expanded_global_actions"
                                >
                                <svg class="w-6 h-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                                Global Actions
                                <!-- Expanded: "rotate-90 text-gray-500", Collapsed: "text-gray-400" -->
                                <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true" x-show="expanded_global_actions">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                                <svg class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                    aria-hidden="true" x-show="!expanded_global_actions">
                                    <path fill-rule="evenodd"
                                        d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <!-- Expandable link section, show/hide based on state. -->
                            <ul class="px-2 mt-1" id="sub-menu-5" x-show="expanded_global_actions" x-collapse>
                                <li>
                                    <!-- 44px -->
                                    <a href="{{route('transactions.match_vendor')}}"
                                        class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                        Match Transaction/Vendor
                                    </a>
                                </li>
                                @can('viewAny', App\Models\TransactionBulkMatch::class)
                                <li>
                                    <!-- 44px -->
                                    <a href="{{route('transactions.bulk_match')}}"
                                        class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                        Transaction Match
                                    </a>
                                </li>
                                @endcan
                                {{-- <li>
                                    <!-- 44px -->
                                    <a href="{{route('transactions.match_vendor')}}"
                                        class="block py-2 pr-2 text-sm leading-6 text-gray-700 rounded-md hover:bg-gray-50 pl-9">
                                        Logs
                                    </a>
                                </li> --}}
                            </ul>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        {{-- ABSOLUTE BOTTOM --}}
        <li class="mt-auto -mx-6">
			<a href="#"
				class="flex items-center px-6 py-3 text-sm font-semibold leading-6 text-gray-900 gap-x-4">
				{{-- <img class="w-8 h-8 rounded-full bg-gray-50"
					src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
					alt=""> --}}
				<span class="sr-only">Logged in user name</span>
				<span aria-hidden="true">{{auth()->user()->full_name}}</span>
			</a>
            <a href="{{route('vendor_selection')}}"
                class="flex items-center px-6 py-3 text-sm leading-6 text-gray-900 gap-x-4 hover:bg-gray-50">
                <span class="sr-only">Switch Log In Account</span>
                <span aria-hidden="true" class="pl-9">Switch Account</span>
            </a>

            @can('admin_login_as_user', App\Models\User::class)
                <a href="{{route('admin_login_as_user')}}"
                    class="flex items-center px-6 py-3 text-sm leading-6 text-gray-900 gap-x-4 hover:bg-gray-50">
                    <span class="sr-only">Login As Another User. Incognito Mode</span>
                    <span aria-hidden="true" class="pl-9">Incognito</span>
                </a>
            @endcan

            <a href="{{route('logout')}}"
                class="flex items-center px-6 py-3 text-sm leading-6 text-gray-900 gap-x-4 hover:bg-gray-50"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                >
                <span class="sr-only">Log Out</span>
                <span aria-hidden="true" class="pl-9">
                    Logout
                </span>
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>
		</li>
	</ul>
</nav>
{{-- @endpersist --}}

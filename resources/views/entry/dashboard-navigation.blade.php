<nav class="flex-1 px-2 space-y-1 bg-white" aria-label="Sidebar">
    <x-nav.section :active="request()->routeIs('dashboard')">
        <x-slot name="name">
            Dashboard
        </x-slot>
        {{-- 10/8/21 [need slot for icon] [ .. ] = done and working! and an option/if statements in x-view if theres none provided(think props) --}}
        <x-slot name="links">
            <x-nav.section-dropdown href="{{route('dashboard')}}" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-nav.section-dropdown>
        </x-slot>
        <x-slot name="icon">
            M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10
        </x-slot>
    </x-nav.section>

    <x-nav.section :active="request()->routeIs('projects.*')">
        <x-slot name="name">
            Projects
        </x-slot>
        <x-slot name="links">
            <x-nav.section-dropdown href="{{route('projects.index')}}" :active="request()->routeIs('projects.index')">
                All Projects
            </x-nav.new-section-dropdown>
            {{-- <x-nav.section-dropdown href="{{route('projects.create')}}" :active="request()->routeIs('projects.create')">
                Create Expense
            </x-nav.new-section-dropdown> --}}
        </x-slot>
        <x-slot name="icon">
            M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z
        </x-slot>
    </x-nav.section>

    @canany(['viewAny', 'create'], App\Models\Expense::class)
    <x-nav.section :active="request()->routeIs('expenses.*')">
        <x-slot name="name">
            Expenses
        </x-slot>
        <x-slot name="links">
            @can('viewAny', App\Models\Expense::class)
            <x-nav.section-dropdown href="{{route('expenses.index')}}" :active="request()->routeIs('expenses.index')">
                All Expenses
            </x-nav.new-section-dropdown>
            @endcan

            @can('create', App\Models\Expense::class)
            <x-nav.section-dropdown href="{{route('expenses.create')}}" :active="request()->routeIs('expenses.create')">
                Create Expense
            </x-nav.new-section-dropdown>
            @endcan

        </x-slot>
        <x-slot name="icon">
            M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z
        </x-slot>
    </x-nav.section>
    @endcanany

    <x-nav.section :active="request()->routeIs('vendors.*')">
        <x-slot name="name">
            Vendors
        </x-slot>
        <x-slot name="links">
            <x-nav.section-dropdown href="{{route('vendors.index')}}" :active="request()->routeIs('vendors.index')">
                All Vendors
            </x-nav.new-section-dropdown>

            @can('create', App\Models\Vendor::class)
            <x-nav.section-dropdown href="{{route('vendors.create')}}" :active="request()->routeIs('vendors.create')">
                Create Vendor
            </x-nav.new-section-dropdown>
            @endcan

        </x-slot>
        <x-slot name="icon">
            M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z
        </x-slot>
    </x-nav.section>

    <x-nav.section :active="request()->routeIs('timesheets.*') || request()->routeIs('hours.*')">
        <x-slot name="name">
            Timesheets
        </x-slot>
        <x-slot name="links">
            <x-nav.section-dropdown href="{{route('hours.create')}}" :active="request()->routeIs('hours.create')">
                Enter New Hours
            </x-nav.new-section-dropdown>
            <x-nav.section-dropdown href="{{route('timesheets.index')}}" :active="request()->routeIs('timesheets.index')">
                Timesheets
            </x-nav.new-section-dropdown>   

            @can('viewPayment', App\Models\Timesheet::class)
                <x-nav.section-dropdown href="{{route('timesheets.payments')}}" :active="request()->routeIs('timesheets.payments')">
                    Payments
                </x-nav.new-section-dropdown>    
            @endcan   
        </x-slot>
        <x-slot name="icon">
            M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z
        </x-slot>
    </x-nav.section>

    <x-nav.section :active="request()->routeIs('banks.*') || request()->routeIs('transactions.*') || request()->routeIs('checks.*')">
        <x-slot name="name">
            Finance
        </x-slot>
        <x-slot name="links">
            <x-nav.section-dropdown href="{{route('checks.index')}}" :active="request()->routeIs('checks.index')">
                Checks
            </x-nav.new-section-dropdown>
            <x-nav.section-dropdown href="{{route('banks.index')}}" :active="request()->routeIs('banks.index')">
                Banks
            </x-nav.new-section-dropdown>
            <x-nav.section-dropdown href="{{route('transactions.match_vendor')}}" :active="request()->routeIs('transactions.match_vendor')">
                Match Transaction/Vendor
            </x-nav.new-section-dropdown>
        </x-slot>
        <x-slot name="icon">
            M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z
        </x-slot>
    </x-nav.section>

    <x-nav.section :active="request()->routeIs('clients.*')">
        <x-slot name="name">
            Clients
        </x-slot>
        <x-slot name="links">
            <x-nav.section-dropdown href="{{route('clients.index')}}" :active="request()->routeIs('clients.index')">
                All Clients
            </x-nav.new-section-dropdown>
        </x-slot>
        <x-slot name="icon">
            M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z
        </x-slot>
    </x-nav.section>
</nav>
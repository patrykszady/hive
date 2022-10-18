{{-- TOP LOGO --}}
<div class="flex items-center flex-shrink-0 px-4 pt-2">
    <a href="{{route('dashboard')}}">
        {{-- <img class="h-8 w-auto" src="https://tailwindui.com/img/logos/workflow-logo-indigo-600-mark-gray-800-text.svg"
        alt="Contractor Hive"> --}}
        Hive
    </a>
</div>

{{-- 10/8/21 need to have Sections with no dropdown, 
        once Section is clicked it goest to that URL... 
        no right arrows or animation either 
    --}}

{{-- SECTIONS --}}
<div class="mt-5 flex-grow flex flex-col">
    {{-- 11/8/21 include ONLY IF auth and user.vendor middlewares pass --}}
    @if(!Route::is('vendor_selection') )
        @include('entry.dashboard-navigation')
    @endif
</div>

{{-- ABSOLUTE BOTTOM --}}
<div class="flex-shrink-0 flex border-t border-indigo-700 p-4">
    <div class="flex items-left">
    {{--  <div>
            <img class="inline-block h-10 w-10 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&amp;ixid=eyJhcHBfaWQiOjEyMDd9&amp;auto=format&amp;fit=facearea&amp;facepad=2&amp;w=256&amp;h=256&amp;q=80" alt="">
        </div> --}}
        <div class="ml-3">
            <a href="#" class="text-base leading-6 font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                {{auth()->user()->full_name}}
            </a>
            <br>
    {{--             <p class="text-sm leading-5 font-medium text-indigo-300 group-hover:text-indigo-100 group-focus:underline transition ease-in-out duration-150">
                Logout
            </p> --}}

            @if(!Route::is('vendor_selection'))
                <a class="text-sm leading-5 font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 group-focus:underline" href="{{route('dashboard')}}">
                    {{ auth()->user()->vendor->business_name }}
                </a>

                <br>

                <a class="text-sm leading-5 font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 group-focus:underline" href="{{route('vendor_selection')}}">
                    Change vendor
                </a>                

                {{-- PATRYK / USER_ID 1 ONLY --}}
                            {{-- PATRYK / USER_ID 1 ONLY --}}
            {{-- @can('admin_login_as_user', App\Models\User::class)
                <a class="text-sm leading-5 font-medium text-indigo-300 group-hover:text-indigo-100 group-focus:underline transition ease-in-out duration-150 hover:text-white" href="{{ route('users.admin_login_as_user') }}">
                    Change Login User
                </a>
                <br>
            @endcan --}}
                @if (auth()->user()->id == 1)
                    <br>

                    <a 
                        class="text-sm leading-5 font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 group-focus:underline" 
                        href="{{route('admin_login_as_user')}}"
                        >
                        Login As Another User
                    </a>
                @endif

                <br>
            @endif

            <a class="text-sm leading-5 font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 group-focus:underline" href="{{ route('logout') }}"
                onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                Logout
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                {{ csrf_field() }}
            </form>

            <br>
        </div>
    </div>
</div>
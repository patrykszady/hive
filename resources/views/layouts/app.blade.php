<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    @include('layouts.head')
    {{-- 10/14/2021 meant to go in head but it doesnt render there.. --}}
    @livewireStyles
    
    <body class="font-sans antialiased">
        <div
            x-cloak
            x-data="{ sidebarOpen: false }" 
            @keydown.window.escape="sidebarOpen = false"
            {{-- 7-9-2022 bg-gray-100/0 = Transparent background --}}
            class="h-screen flex overflow-hidden bg-gray-100"
            >

            @include('layouts.nav.navigation')

            <!-- PAGE CONTENT -->
            <div class="flex flex-col w-0 flex-1 overflow-hidden">
                <div class="md:hidden pl-1 pt-1 sm:pl-3 sm:pt-3">
                    <button 
                        @click.stop="sidebarOpen = true" 
                        class="-ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-white bg-gray-400 hover:bg-gray-600 hover:text-white focus:outline-none focus:bg-gray-800 transition ease-in-out duration-150" 
                        aria-label="Open sidebar"
                        >
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
                <main class="flex-1 relative z-0 overflow-y-auto focus:outline-none" tabindex="0">
                    <div class="w-full mx-auto py-6">                            
                            {{-- @include('partials.messages') --}}
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
        {{-- MOVED TO layouts.head --}}
        {{-- @livewireScripts --}}
    </body>
</html>

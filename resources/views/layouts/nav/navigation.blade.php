<!-- Off-canvas menu for mobile -->
<div x-cloak x-show="sidebarOpen" class="md:hidden" style="display: none;">
    <div class="fixed inset-0 flex z-40">
        <div @click="sidebarOpen = false" x-show="sidebarOpen"
            x-description="Off-canvas menu overlay, show/hide based on off-canvas menu state."
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0"
            style="display: none;"
            >

            <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
        </div>
        <div x-show="sidebarOpen" x-description="Off-canvas menu, show/hide based on off-canvas menu state."
            x-transition:enter="transition ease-in-out duration-300 transform"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="relative flex-1 flex flex-col max-w-xs w-full bg-white" style="display: none;">
            <div class="absolute top-0 right-0 -mr-14 p-1">
                <button x-show="sidebarOpen" @click="sidebarOpen = false"
                    class="flex items-center justify-center h-12 w-12 rounded-full focus:outline-none focus:bg-gray-600"
                    aria-label="Close sidebar" style="display: none;">
                    <svg class="h-6 w-6 text-white" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            {{-- NAV SECTIONS --}}
            @include('layouts.nav.navigation-menu')

        </div>
        <div class="flex-shrink-0 w-14">
            <!-- Force sidebar to shrink to fit close icon -->
        </div>
    </div>
</div>

<!-- Static sidebar for desktop -->
<div class="hidden md:flex md:flex-shrink-0">
    <div class="flex flex-col w-64 flex-grow border-r border-gray-200 pt-5 pb-4 bg-white overflow-y-auto">
        @include('layouts.nav.navigation-menu')
    </div>
</div>
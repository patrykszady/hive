<x-guest-layout>
    <body class="antialiased">
        <div
            class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center py-4 sm:pt-0">
            @if (Route::has('login'))
                <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                    @auth
                    <a href="{{ url('/dashboard') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Dashboard</a>
                    @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-500 underline">Log in</a>
    
                    @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                        class="ml-4 text-sm text-gray-700 dark:text-gray-500 underline">Register</a>
                    @endif
                    @endauth
                </div>
            @endif
    
            <div class="bg-white">
                <div class="max-w-7xl mx-auto text-center py-12 px-4 sm:px-6 lg:py-16 lg:px-8">
                    <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        <span class="block">Ready to dive in?</span>
                        <span class="block">Start for free, forever.</span>
                    </h2>
                    <div class="mt-8 flex justify-center">
                        <div class="inline-flex rounded-md shadow">
                            <a href="{{route('register')}}"
                                class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Get started
                            </a>
                        </div>
                        <div class="ml-3 inline-flex">
                            <a href="#"
                                class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200">
                                Learn more
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</x-guest-layout>
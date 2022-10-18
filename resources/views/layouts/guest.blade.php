<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    
    @include('layouts.head')
    {{-- 10/14/2021 meant to go in head but it doesnt render there.. --}}
    @livewireStyles
    
    <body>
        <div class="font-sans text-gray-900 antialiased">
            {{ $slot }}
        </div>
        @livewireScripts
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('components.layouts.head')
    {{-- 10/14/2021 meant to go in head but it doesnt render there.. --}}
    {{-- @livewireStyles --}}

    <body>
        <div class="h-screen font-sans antialiased text-gray-900">
            {{ $slot }}
        </div>
    </body>
</html>

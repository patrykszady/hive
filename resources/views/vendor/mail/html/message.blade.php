<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="'https://dashboard.hive.contractors'">
<img src="https://dashboard.hive.contractors/favicon.png" class="logo" alt="Hive Contractors Logo" height="144px">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{{ $slot }}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{{ $subcopy }}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>

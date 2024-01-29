@props([
    'left' => null,
    'right' => null
    ])

{{--  sm:px-6 --}}
{{-- bg-gray-50 --}}
<div class="px-6 py-4 border-b border-gray-200">
    <div class="flex flex-wrap items-center justify-between sm:flex-nowrap">
        {{-- {{$slot}} --}}

        <div>
            <h3 class="text-lg font-medium leading-6 text-gray-900">
                {{$left}}
            </h3>
        </div>
        {{--  10/14/21 only last inside x-card.heading = flex-shrink-0 .. how to do automatically? --}}
        {{-- mt-2 md:mt-0 --}}
        <div class="flex-shrink-0 md:ml-4">
            {{-- 10/14/21 button = new compnent in card or application? --}}
            {{$right}}
        </div>
    </div>
    {{$slot}}
</div>

@props([
    'left' => null,
    'right' => null
    ])

{{--  sm:px-6 --}}
<div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
    <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">
        {{$slot}}

        <div>
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                {{$left}}
            </h3>
        </div>
        {{--  10/14/21 only last inside x-card.heading = flex-shrink-0 .. how to do automatically? --}}
        {{-- mt-2  --}}
        <div class="ml-4 flex-shrink-0">
            {{-- 10/14/21 button = new compnent in card or application? --}}
            {{$right}}
        </div>
    </div>
</div>
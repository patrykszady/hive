@props([
    'bottom' => null
    ])

<div class="bg-gray-50 px-6 py-3 lg:flex items-center justify-between border-t border-gray-200"> 
    <div class="sm:flex-1 sm:flex sm:items-center sm:justify-between">
        {{$slot}}            
    </div>
</div>

{{-- bottom slot  --}}
@if($bottom)
    <div class="bg-gray-50 px-6 py-3 lg:flex items-center justify-between border-t border-gray-200 items-center">
        {{$bottom}}            
    </div>
@endif
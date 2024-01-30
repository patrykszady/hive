{{-- See search_li.blade.php for similar --}}
{{-- search_li.blade is similar with href and wire:click --}}

@props([
    'hrefTarget' => NULL,
    //4-26-2023 remove white_button everywhere
    'white_button' => NULL,
    'button_color' => 'indigo',
])

@php
    if($white_button == TRUE || $button_color == 'white'){
        $classes = "bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500";
    }else{
        $classes = "relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-$button_color-600 hover:bg-$button_color-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-$button_color-500";
    }
@endphp

<a
    @if(isset($attributes['wire:click']))
        href="#"
        wire:click="{{ $attributes['wire:click'] }}";
    @elseif($attributes['href'] == "")

    @else
        href="{{ $attributes['href'] }}"
    @endif

    @if($hrefTarget)
        target="{{$hrefTarget}}"
    @endif
    {{ $attributes() }}

    class="{{$classes}}"
    {{-- disabled when clicked/loading --}}
    {{-- wire:loading.attr="disabled" --}}
    >
    {{$slot}}
</a>

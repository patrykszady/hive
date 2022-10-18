@props([
    'href' => '#',
    'white_button' => NULL,
    'color_button' => NULL
])

@php
    if($white_button == TRUE){
        $classes = "bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500";
    }elseif($color_button == TRUE){
        $classes = "relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500";
    }else{
        $classes = "relative inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500";
    }
@endphp

{{--  {{$attributes}}" --}}
<a href="{{$href}}" {{ $attributes() }} class="{{$classes}}">
    {{$slot}}
</a>
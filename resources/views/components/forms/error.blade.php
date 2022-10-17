@props(['errorName'])

{{-- {{dd($name)}} --}}
{{-- {{dd($errors->has($name))}} --}}
@error($errorName)
    <p class="mt-2 text-sm text-red-600" id="{{$errorName}}-error">{{$message}}</p>
@enderror

{{-- x-transition.duration.1000ms --}}

{{-- <div x-data="{ open{{$name}}: {{$errors->has($name)}} ? true : false}" x-show="open{{$name}}">
    <p class="mt-2 text-sm text-red-600" id="{{$name}}-error">Test error livewire</p>
</div> --}}
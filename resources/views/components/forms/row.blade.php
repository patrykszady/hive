@props([
    'name' => null,
    'errorName' => null,
    'text' => null,
    'type' => 'text',
    'hint' => null,
    'radioHint' => null,
    'textSize' => 'sm',
    'titleslot' => null,
    'buttonText' => null,
])

@php
    $input_classes = 'flex-1 block w-full min-w-0 rounded-none sm:text-' . $textSize;

    if($attributes['disabled'] == true){
        $input_classes .= ' bg-gray-50';
    }else{
        $input_classes .= ' hover:bg-gray-50';
    }

    if($hint && $radioHint){
        $input_classes .= ' ';
    }elseif($hint){
        $input_classes .= ' rounded-r-md';
    }elseif($radioHint){
        $input_classes .= ' rounded-l-md';
    }else{
        $input_classes .= ' rounded-md';
    }

    // 10-27-2021 Why cant we use @error here?
    if($errors->has($errorName)){
        $input_classes .= ' focus:ring-red-500 focus:border-red-500 border-red-300 text-red-900 placeholder-red-200';
        $label_text_color = 'red';
    }else{
        $input_classes .= ' focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 placeholder-gray-200';
        $label_text_color  = 'gray';
    }

    // 11-9-2021 this is only for ExpenseForm..why is it here??
    if(isset($this->split)){
        if($this->split && $radioHint){
            $input_classes .= ' bg-gray-50';
        }
    }
@endphp

<div class="px-6">
    {{-- <div>
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Profile
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            This information will be displayed publicly so be careful what you share.
        </p>
    </div> --}}

    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
        <label 
            for="{{ $name }}" 
            class="block text-sm font-medium text-{{$label_text_color}}-700 sm:mt-px sm:pt-2"
            >
            {{ $text }}
        </label>
        <div class="mt-1 sm:mt-0 sm:col-span-2">
            @if($type === 'radio')
                <div>
            @else
                <div class="flex max-w-lg rounded-md shadow-sm">
            @endif

                @if($hint)
                    <span
                        class="cursor-default inline-flex items-center px-3 rounded-l-md border border-r-0 border-{{$label_text_color}}-300 bg-{{$label_text_color}}-50 text-{{$label_text_color}}-500 sm:text-sm">
                        {{$hint}}
                    </span>
                @endif
                
                @if($type === 'textarea')
                    <textarea
                        name="{{ $name }}"
                        id="{{ $name }}" 
                        autocomplete="{{ $name }}"
                        class="max-w-lg shadow-sm block w-full focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border border-gray-300 rounded-md hover:bg-gray-50 placeholder-gray-200"
                        {{ $attributes() }}
                        >
                    </textarea>
                @elseif($type === 'dropdown')
                    <select
                        name="{{ $name }}"
                        id="{{ $name }}"  
                        class="{{ $input_classes }}"
                        {{ $attributes() }}
                        >
                        {{ $slot }}
                    </select>
                @elseif($type === 'file')
                    <input 
                        type="{{ $type }}"
                        name="{{ $name }}"
                        id="{{ $name }}"
                        class="{{ $input_classes }} py-2 px-4 border border-gray-300"
                        {{ $attributes() }}
                    >
                
                @elseif($type === 'button')
                    <button 
                        type="{{ $type }}"
                        name="{{ $name }}"
                        id="{{ $name }}"
                        class="{{ $input_classes }} py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        {{ $attributes() }}
                        >
                        {{ $buttonText }}
                    </button>

                @elseif($type === 'radio')
                    <fieldset>
                        <legend class="sr-only">
                            {{ $text }}
                        </legend>
                        {{$slot}}
                    </fieldset>
                                    
                {{-- @elseif($type === 'hidden')
                    <input 
                        type="{{ $type }}"
                        name="{{ $name }}"
                        id="{{ $name }}" 
                        value="2"
                    > --}}
                @else
                    <input 
                        type="{{ $type }}"
                        name="{{ $name }}"
                        id="{{ $name }}" 
                        autocomplete="{{ $name }}"
                        class="{{ $input_classes }}"
                        {{ $attributes() }}
                    >
                @endif

                @if($radioHint)
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-{{$label_text_color}}-300 bg-{{$label_text_color}}-50 text-{{$label_text_color}}-500 sm:text-sm">
                        {{$radioHint}}
                        {{$radio}}
                    </span>
                @endif              
            </div>

                {{-- slot for span below file upload input --}}
                {{ $titleslot }}
           
            <x-forms.error errorName="{{$errorName}}" />
        </div>
    </div>
</div>
@props([
    'name' => null,
    'errorName' => null,
    'text' => null,
    'type' => 'text',
    'hint' => null,
    'radioHint' => null,
    'textSize' => 'sm',
    'titleslot' => null,
    'rowslot' => null,
    'buttonText' => null,
    'bottom' => null,
    'hint_dropdown' => null,
])

@php
    $input_classes = 'flex-1 block w-full min-w-0 rounded-none sm:text-' . $textSize;

    // On blade do this x-bind:disabled="{{$bid->estimate_sections->isEmpty() ? false : true}}"

    if($attributes['x-bind:disabled']){
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
    <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
        <label
            for="{{ $name }}"
            class="block text-sm font-medium text-{{$label_text_color}}-700 sm:mt-px sm:pt-2"
            >
            {{ $text }}
        </label>
        <div class="mt-1 sm:mt-0 sm:col-span-2">
            {{-- 08-26-2026 inline --}}
            @if($type === 'radio' || $type === 'radiogroup')
                <div>
            @else
                <div class="flex max-w-lg rounded-md shadow-sm">
            @endif
                @if($hint)
                    <span
                        class="cursor-default inline-flex items-center px-3 rounded-l-md border border-r-0 border-{{$label_text_color}}-300 bg-{{$label_text_color}}-50 text-{{$label_text_color}}-500 sm:text-sm"
                        >
                        {{$hint_dropdown}}
                        {{$hint}}
                    </span>
                @endif

                @if($type === 'textarea')
                    <textarea
                        type="text"
                        name="{{ $name }}"
                        id="{{ $name }}"
                        autocomplete="{{ $name }}"
                        class="{{ $input_classes }}"
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
                        class="{{ $input_classes }} py-2 px-4 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
                {{-- https://tailwindui.com/components/application-ui/forms/checkboxes --}}
                @elseif($type === 'checkbox_group')
                    <fieldset>
                        <legend class="sr-only">
                            {{ $text }}
                        </legend>
                        {{$slot}}
                        {{-- @if($attributes['data']['wire_model'])
                            <div class="space-y-5">
                                @foreach($attributes['data']['wire_model'] as $bank_id => $bank)
                                <div class="relative flex items-start">
                                    <div class="flex items-center h-6">
                                        <input id="{{$bank_id}}" aria-describedby="bank-{{$bank_id}}-description" name="{{$name}}" type="checkbox"
                                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-600">
                                    </div>
                                    <div class="ml-3 text-sm leading-6">
                                        <label for="{{$name}}" class="font-medium text-gray-900">{{$bank->name}}</label>
                                        <p id="comments-description" class="text-gray-500">Get notified when someones posts a comment on a
                                            posting.</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @endif --}}
                    </fieldset>
                @elseif($type === 'radiogroup')
                    <div
                        wire:model.live="{{$name}}"
                        {{-- wire:ignore --}}
                        >
                        <div
                            x-data="{
                                value: null,
                                select(option) { this.value = option },
                                isSelected(option) { return this.value === option},

                                hasRovingTabindex(option, el) {
                                    //If this is the frist option element and no option has been selected, make it focusable.
                                    if (this.value === null && Array.from(el.parentElement.children).indexOf(el) === 0) return true
                                    return this.isSelected(option)
                                },
                                selectNext(e){
                                    let el = e.target
                                    let siblings = Array.from(el.parentElement.children)
                                    let index = siblings.indexOf(el)
                                    let next = siblings[index === siblings.length - 1 ? 0 : index + 1]

                                    next.click(); next.focus();
                                },
                                selectPrevious(e){
                                    let el = e.target
                                    let siblings = Array.from(el.parentElement.children)
                                    let index = siblings.indexOf(el)
                                    let previous = siblings[index === 0 ?  siblings.length - 1 : index - 1]

                                    previous.click(); previous.focus();
                                }
                            }"

                            @keydown.down.stop.prevent="selectNext"
                            @keydown.right.stop.prevent="selectNext"
                            @keydown.up.stop.prevent="selectPrevious"
                            @keydown.left.stop.prevent="selectPrevious"
                            role="radiogroup"
                            :aria-labelledby="$id('radio-group-label')"
                            x-id="['radio-group-label']"
                            >

                            <input type="hidden" role="none" :value="value" >
                            {{-- <label :id="$id('radio-group-label')" role="none">Backend Framework: <span x-text="value"></span></label> --}}

                            <div class="space-y-2">
                                {{-- options --}}
                                @if($attributes['data']['wire_model'])
                                    @foreach($attributes['data']['wire_model']->keyBy('id') as $key => $data_data)
                                        <div
                                            x-data="{ option: '{{$key}}' }"
                                            :class="isSelected(option) ? 'border-transparent border-indigo-600 ring-2 ring-indigo-600 bg-gray-50 hover:bg-gray-100' : ''"
                                            class="relative block px-6 py-4 bg-white border rounded-lg shadow-sm cursor-pointer hover:bg-gray-50 focus:outline-none sm:flex sm:justify-between"

                                            @click="select(option), $dispatch('input', option)"
                                            @keydown.enter.stop.prevent="select(option)"
                                            @keydown.space.stop.prevent="select(option)"
                                            :aria-checked="isSelected(option)"
                                            :tabindex="hasRovingTabindex(option, $el) ? 0 : -1"
                                            :aria-labelledby="$id('radio-option-left-label')"
                                            :aria-describedby="$id('radio-option-left-desc')"
                                            :aria-labelledby="$id('radio-option-right-label')"
                                            :aria-describedby="$id('radio-option-right-desc')"
                                            x-id="['radio-option-left-label', 'radio-option-left-desc', 'radio-option-right-label', 'radio-option-right-desc']"
                                            role="radio"
                                            >
                                            <input type="radio" class="sr-only" :value="value" wire:model.live="{{$name}}">

                                            {{-- LEFT DETAILS --}}
                                            <span class="flex items-center">
                                                <span class="flex flex-col text-sm">
                                                    <span :id="$id('radio-option-left-label')" class="font-medium text-gray-900">{{$data_data[$attributes['data']['radio_details_left']['title']]}}</span>
                                                    <span :id="$id('radio-option-left-desc')" class="text-gray-500">
                                                        <span class="block sm:inline">{{$data_data[$attributes['data']['radio_details_left']['desc']]}}</span>
                                                        {{-- center dot / seperator like | --}}
                                                        <span class="hidden sm:mx-1 sm:inline" aria-hidden="true">&middot;</span>
                                                        {{-- <span class="block sm:inline">160 GB SSD disk</span> --}}
                                                    </span>
                                                </span>
                                            </span>

                                            {{-- RIGHT DETAILS --}}
                                            {{-- <span class="flex mt-2 text-sm sm:ml-4 sm:mt-0 sm:flex-col sm:text-right">
                                                <span :id="$id('radio-option-right-label')" class="font-medium text-gray-900">{{$data_data[$attributes['data']['radio_details_right']['title']]}}</span>
                                                <span :id="$id('radio-option-right-desc')" class="ml-1 text-gray-500 sm:ml-0">{{$data_data[$attributes['data']['radio_details_right']['desc']]}}</span>
                                            </span> --}}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif($type === 'search_dropdown')
                    <input
                        type="text"
                        wire:model.live="{{ $name }}"
                        name="{{ $name }}"
                        id="{{ $name }}"
                        autocomplete="{{ $name }}"
                        class="{{ $input_classes }}"
                        {{ $attributes() }}
                    >
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

            <div>
                {{$rowslot}}
            </div>

            {{-- slot for span below file upload input --}}
            <div>
                {{ $titleslot }}
            </div>

            <div>
                {{ $bottom }}
            </div>

            <x-forms.error errorName="{{$errorName}}" />
        </div>
    </div>
</div>

{{-- button.blade is similar with href and wire:click --}}
@props([
    'noHover' => false,
    'checkbox' => false,
    'basic' => false,
    'form' => false,
    'lineTitle' => NULL,
    'hrefTarget' => NULL,
    'lineData' => NULL,
    'bubbleMessage' => NULL,
    'bubbleColor' => 'indigo',
    'bold' => FALSE,
    'span' => NULL
    ])

{{-- {{ $attributes->merge(['class' => 'divide-y divide-gray-200']) }}    (it works on lists.ul.blade) --}}
<li @class([
    'bg-gray-100' => $noHover == true,
    'hover:bg-gray-50 cursor-none' => $noHover == false
    ])
    >
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

        class="block"
        >

        <div class="px-4 py-4 sm:px-6">
            <div @class(['items-center', 'flex' => !$basic])>
                @if($checkbox)
                    <input
                        wire:model.live="{{$checkbox['name']}}.{{$checkbox['id']}}.checkbox"
                        class="w-4 h-4 mr-2 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        name="{{$checkbox['name']}}"
                        id="{{$checkbox['name'] .  $checkbox['id']}}"
                        aria-describedby="{{$checkbox['name']}}-description"
                        type="checkbox"
                        >
                @endif

                @if($basic)
                    <div class="sm:divide-y sm:divide-gray-200">
                        <div class="items-center sm:grid sm:grid-cols-3 sm:gap-1">
                            <p class="text-sm {{$bold ? 'font-bold' : 'font-medium'}} text-gray-500 font-col">{{ $lineTitle }}</p>

                            @if($lineData)
                                <p @class(['text-md text-gray-900 sm:col-span-2', 'hover:text-indigo-600' => $attributes['href'], 'font-bold' => $bold])>
                                    {!! $lineData !!}

                                    <br>
                                    <span class="text-sm italic text-gray-500 sm:mt-0">
                                        {{$span}}
                                    </span>
                                </p>
                            @else
                                @if($form)
                                    {{$select_form}}
                                @endif
                            @endif
                        </div>
                    </div>

                    @if($bubbleMessage)
                        <div class="ml-auto">
                            <p
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{$bubbleColor}}-100 text-{{$bubbleColor}}-800">
                                {{ $bubbleMessage }}
                            </p>
                        </div>
                    @endif
                @else
                    <p class="font-medium text-gray-900 text-md font-col">
                        {{ $lineTitle }}
                    </p>
                    <div class="ml-auto">
                        <p
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{$bubbleColor}}-100 text-{{$bubbleColor}}-800">
                            {{ $bubbleMessage }}
                        </p>
                    </div>
                @endif
            </div>

            @if(isset($lineDetails) AND $lineDetails != '')
                <div class="mt-2 sm:flex sm:justify-between">
                    <div class="sm:flex">
                        @foreach($lineDetails as $line_detail)
                            <p class="flex items-center mt-2 text-sm text-gray-500 sm:mt-0 sm:ml-6">
                                <svg
                                    class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                    aria-hidden="true">
                                    <path
                                        fill-rule="evenodd"
                                        d="{{ $line_detail['icon'] }}"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                {!! $line_detail['text'] !!}
                            </p>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{$slot}}
    </a>
</li>

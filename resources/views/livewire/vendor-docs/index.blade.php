<div>
    <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-2xl lg:px-8 pb-5 mb-1' : ''}}">
        {{-- HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Vendor Insurance Certificates</h1>
            </x-slot>
        </x-cards.heading>
    </x-cards.wrapper>

    <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-2xl lg:px-8 pb-5 mb-1' : ''}}">
        @livewire('vendor-docs.audit-index')
    </x-cards.wrapper>
    {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
    @foreach($vendors as $vendor)
        {{-- @dd($vendor_docs->first()->vendor->business_name) --}}
        {{-- $vendor->vendor_docs()->orderBy('expiration_date', 'DESC')->get() --}}
        {{-- @dd($vendor_docs->where('expiration_date', '>=', today())->isEmpty()) --}}
        <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-2xl lg:px-8 mb-2' : ''}}">
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$vendor->business_name}}</h1>
                </x-slot>
                <x-slot name="right">
                    <div class="space-x-2">
                        <x-cards.button
                            wire:click="$dispatchTo('vendor-docs.vendor-doc-create', 'addDocument', { vendor: {{$vendor->id}} })"
                            button_color=white
                            >
                            Add
                        </x-cards.button>
                        @if(isset($vendor->expired_docs))
                            <x-cards.button
                                wire:click="$dispatchTo('vendor-docs.vendor-doc-create', 'requestDocument', { vendor: {{$vendor->id}} })"
                                button_color=red
                                >
                                Request
                            </x-cards.button>
                        @endif
                    </div>
                </x-slot>
            </x-cards.heading>

            <x-cards.body>
                <x-lists.ul>
                    @foreach($vendor->vendor_docs()->orderBy('expiration_date', 'DESC')->get() as $vendor_doc)
                        @php
                            $line_details = [
                                1 => [
                                    'text' => $vendor_doc->expiration_date->format('m/d/Y'),
                                    'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z'
                                    ],
                                2 => [
                                    'text' => $vendor_doc->number,
                                    'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                    ],
                                ];
                        @endphp

                        <x-lists.search_li
                            {{-- href="{{ route('checks.show', $expense->check->id) }}" --}}
                            :line_details="$line_details"
                            :line_title="$vendor_doc->type"
                            :bubble_message="$vendor_doc->expiration_date <= today() ? 'Expired' : 'Active'"
                            :bubble_color="$vendor_doc->expiration_date <= today() ? 'red' : 'green'"
                            >
                        </x-lists.search_li>
                    @endforeach
                </x-lists.ul>
            </x-cards.body>
        </x-cards.wrapper>
    @endforeach
    <livewire:vendor-docs.vendor-doc-create />
</div>

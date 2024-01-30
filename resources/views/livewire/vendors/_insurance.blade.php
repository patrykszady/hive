<div>
    <x-cards.wrapper>
        <x-cards.heading>
            <x-slot name="left">
                {{-- attribute --}}
                <h1>Insurance</h1>
            </x-slot>

            @can('create', App\Models\User::class)
                <x-slot name="right">
                    <div class="space-x-2">
                        {{-- if any docs are expired.. policy? --}}
                        @if(isset($vendor->expired_docs))
                            <x-cards.button
                                wire:click="$dispatchTo('vendor-docs.vendor-doc-create', 'requestDocument', { vendor: {{$vendor->id}} })"
                                button_color=red
                                >
                                Request
                            </x-cards.button>
                        @endif

                        <x-cards.button
                            wire:click="$dispatchTo('vendor-docs.vendor-doc-create', 'addDocument', { vendor: {{$vendor->id}} })"
                            button_color=white
                            >
                            Add
                        </x-cards.button>
                    </div>
                </x-slot>
            @endcan
        </x-cards.heading>
        @if(!$vendor_docs->isEmpty())
            <x-lists.ul>
                @foreach($vendor_docs as $doc)
                    @php
                        $line_details = [
                            1 => [
                                'text' => $doc->first()->expiration_date->format('m/d/Y'),
                                'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                ],
                            2 => [
                                'text' => $doc->first()->number,
                                'icon' => 'M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z'
                                ],
                        ];
                    @endphp

                    <x-lists.search_li
                        {{-- wire:click="$dispatch('showMember', {{$user->id}})" --}}
                        {{-- href="#" --}}
                        :line_details="$line_details"
                        :line_title="$doc->first()->type"
                        :bubble_message="$doc->first()->expiration_date > today() ? 'Active' : 'Expired'"
                        :bubble_color="$doc->first()->expiration_date > today() ? 'green' : 'red'"
                        >
                    </x-lists.search_li>
                @endforeach
            </x-lists.ul>
        @else
            <p class="text-red-800">NO INSURANCE</p>
        @endif
    </x-cards.wrapper>
</div>

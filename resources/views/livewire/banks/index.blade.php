<x-cards.wrapper class="max-w-lg mx-auto">
    {{-- HEADING --}}
    <x-cards.heading>
        <x-slot name="left">
            <h1>Banks</h1>
        </x-slot>

        <x-slot name="right">
            <x-cards.button wire:click="plaid_link_token">
                New Bank
            </x-cards.button>
        </x-slot>
    </x-cards.heading>

    {{-- SUB-HEADING --}}
    {{-- <x-cards.heading>
        <x-slot name="left">

        </x-slot>
    </x-cards.heading> --}}

    {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
    <x-lists.ul>
        @foreach($banks as $bank)
            @php
                $line_details = [
                    1 => [
                        'text' => $bank->name,
                        'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'

                        ],
                    ];
            @endphp

            <x-lists.search_li
                href="{{route('banks.show', $bank->id)}}"
                :line_details="$line_details"
                :line_title="$bank->name"
                :bubble_message="'Success'"
                >
            </x-lists.search_li>
        @endforeach
    </x-lists.ul>

    {{-- FOOTER for forms for example --}}
    {{-- <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <button type="submit"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Save
        </button>
    </div> --}}

    {{-- FOOTER --}}

    {{-- PLAID LINK --}}
    {{-- <meta name="_token" content="{{csrf_token()}}" /> --}}
    <script> 
        Livewire.on('linkToken', exchangeToken => {            
            var handler = Plaid.create({                   
                token: exchangeToken,
                
                onLoad: function() {
                    handler.open();
                },

                onSuccess: function(token, metadata) {
                    // console.log(metadata);
                    // Send the public_token to your app server.
                    // The metadata object contains info about the institution the
                    // user selected and the account ID or IDs, if the
                    // Select Account view is enabled.
                    
                    Livewire.emit('plaidLinkItem', metadata);               
                },
            
                onExit: function(err, metadata) {
                    // The user exited the Link flow or error above.
                    if (err != null) {
                        // The user encountered a Plaid API error prior to exiting.
                    }
                        // metadata contains information about the institution
                        // that the user selected and the most recent API request IDs.
                        // Storing this information can be helpful for support.
                },
            
                onEvent: function(eventName, metadata) {
                    // Optionally capture Link flow events, streamed through
                    // this callback as your users connect an Item to Plaid.
                    // For example:
                    // eventName = "TRANSITION_VIEW"
                    // metadata  = {
                    //   link_session_id: "123-abc",
                    //   mfa_type:        "questions",
                    //   timestamp:       "2017-09-14T14:42:19.350Z",
                    //   view_name:       "MFA",
                    // }
                }
            });
        }) 
    </script>
</x-cards.wrapper>


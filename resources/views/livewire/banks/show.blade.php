<x-cards.wrapper class="max-w-lg mx-auto">
    {{-- HEADING --}}
    <x-cards.heading>
        <x-slot name="left">
            <h1>{{$bank->name}}</h1>
        </x-slot>

        <x-slot name="right">
            <x-cards.button wire:click="plaid_link_token_update">
                Update Bank
            </x-cards.button>
        </x-slot>
        @if($bank->plaid_options->error)
            <b><span class="text-red-900"><i>{{$bank->plaid_options->error->error_code}}</i></span></b>
        @endif
    </x-cards.heading>

    {{-- SUB-HEADING --}}
    {{-- <x-cards.heading>
        <x-slot name="left">

        </x-slot>
    </x-cards.heading> --}}

    {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
    <x-lists.ul>
        @foreach($bank->accounts as $account)
            @php
                $line_details = [
                    1 => [
                        'text' => $account->account_number,
                        'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'

                        ],
                    ];
            @endphp

            <x-lists.search_li
                {{-- href="{{route('banks.show', $bank->id)}}" --}}
                :line_details="$line_details"
                :line_title="$bank->name . ' ' . $account->type"
                :bubble_message="$account->type"
                >
            </x-lists.search_li>
        @endforeach
    </x-lists.ul>

    {{-- FOOTER for forms for example --}}
    {{-- <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
        <button type="submit"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Save
        </button>
    </div> --}}

    {{-- FOOTER --}}

    {{-- PLAID LINK --}}
    {{-- <meta name="_token" content="{{csrf_token()}}" /> --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('linkTokenUpdate', exchangeToken => {
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

                        // Livewire.emit('plaidLinkItemUpdate', metadata);
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

                    // onEvent: function(eventName, metadata) {
                    //     // Optionally capture Link flow events, streamed through
                    //     // this callback as your users connect an Item to Plaid.
                    //     // For example:
                    //     // eventName = "TRANSITION_VIEW"
                    //     // metadata  = {
                    //     //   link_session_id: "123-abc",
                    //     //   mfa_type:        "questions",
                    //     //   timestamp:       "2017-09-14T14:42:19.350Z",
                    //     //   view_name:       "MFA",
                    //     // }
                    // }
                });
            });
        });
    </script>
    <script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
</x-cards.wrapper>


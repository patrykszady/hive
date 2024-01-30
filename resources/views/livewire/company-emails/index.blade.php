<div>
    {{-- key="{{ Str::random() }}" --}}
    <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-xl lg:px-8 pb-5 mb-1' : ''}}">
        {{-- HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Company Emails</h1>
                <p class="text-sm text-gray-500">Email accounts you use to recieve digital receipts from merchants.</p>
            </x-slot>
            @if(session('error'))
                <div class="p-4 text-red-700 bg-red-100 border-l-4 border-red-500" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            {{-- <x-slot name="right">
                <x-cards.button
                    wire:click="$emitTo('company-emails.company-emails-form', 'addEmail')"
                    >
                    Add Email
                </x-cards.button>
            </x-slot> --}}
        </x-cards.heading>
        {{-- @livewire('company-emails.company-emails-form') --}}

        {{-- SUB-HEADING --}}
        {{-- <x-cards.heading>
            <x-slot name="left">

            </x-slot>
        </x-cards.heading> --}}

        {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
        <x-lists.ul>
            @foreach($emails as $email)
                {{-- @php
                    $line_details = [
                        1 => [
                            'text' => $email->email,
                            'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'

                            ],
                        ];
                @endphp --}}

                <x-lists.search_li
                    {{-- href="{{route('banks.show', $bank->id)}}" --}}
                    {{-- :line_details="$line_details" --}}
                    :line_title="$email->email"
                    :bubble_message="'Connected'"
                    :bubble_color="'green'"
                    >
                </x-lists.search_li>
            @endforeach
        </x-lists.ul>

        {{-- FOOTER for forms for example --}}
        <div class="px-4 py-3 text-right bg-gray-50 sm:px-6">
            <a href="{{route('ms_graph_login')}}" type="button"
                class="inline-flex justify-center px-4 py-2 text-sm text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Microsoft Email
            </a>
            <a href="{{route('google_cloud_login')}}" type="button"
                class="inline-flex justify-center px-4 py-2 text-sm text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Google Email
            </a>
        </div>
    </x-cards.wrapper>

    @if(request()->routeIs('company_emails.index'))
        <livewire:receipt-accounts.receipt-accounts-index />
    @endif
</div>


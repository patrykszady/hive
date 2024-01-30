<x-cards.wrapper>
    <x-cards.heading>
        <x-slot name="left">
            <h1 class="text-lg">Vendor Details</h1>
            @if($registration)
                <p class="max-w-2xl mt-1 text-sm text-gray-500">Confirm {{$vendor->business_name}} information.</p>
            @endif
        </x-slot>

        @can('update', $vendor)
            <x-slot name="right">
                <x-cards.button
                    wire:click="$dispatchTo('vendors.vendor-create', 'editVendor', { vendor: {{$vendor->id}} })"
                    >
                    Edit Vendor
                </x-cards.button>
            </x-slot>
        @endcan
    </x-cards.heading>

    <x-cards.body>
        <x-lists.ul>
            <x-lists.search_li
                :basic=true
                :line_title="'Business Name'"
                :line_data="$vendor->business_name"
                {{-- :bubble_message="'Success'" --}}
                >
            </x-lists.search_li>

            <x-lists.search_li
                :basic=true
                :line_title="'Vendor Type'"
                :line_data="$vendor->business_type"
                >
            </x-lists.search_li>

            {{-- Retail --}}
            @if($vendor->business_type != 'Retail')
                <x-lists.search_li
                    :basic=true
                    :line_title="'Vendor Address'"
                    href="{{$vendor->getAddressMapURI()}}"
                    :href_target="'blank'"
                    :line_data="$vendor->full_address"
                    >
                </x-lists.search_li>
            @endif

            @if(in_array($vendor->business_type, ["Sub", "DBA", "1099"]))
                <x-lists.search_li
                    :basic=true
                    :line_title="'Business Phone'"
                    :line_data="$vendor->business_phone"
                    >
                </x-lists.search_li>

                <x-lists.search_li
                    :basic=true
                    :line_title="'Business Email'"
                    :line_data="$vendor->business_email"
                    >
                </x-lists.search_li>
            @endif
        </x-lists.ul>
    </x-cards.body>

    @can('update', $vendor)
        @if(in_array($vendor->business_type, ["Sub", "DBA", "1099"]))
            @if($vendor->id != auth()->user()->vendor->id)
                <x-cards.footer>
                    <x-cards.button
                        href="{{route('vendors.payment', $vendor->id)}}"
                        >
                        Vendor Payment
                    </x-cards.button>
                    @if($vendor->vendor_docs->isEmpty())
                        <x-cards.button
                            {{-- wire:click="$dispatchTo('expenses.expense-create', 'newExpense', { amount: {{$amount}}})" --}}
                            {{-- {{$vendor->id}} --}}
                            wire:click="$dispatchTo('vendor-docs.vendor-doc-create', 'addDocument', { vendor: {{$vendor->id}} })"
                            button_color=white
                            >
                            Add Insurance
                        </x-cards.button>
                    @endif
                </x-cards.footer>
            @endif
        @endif
    @endcan

    <div
        x-data="{ vendor_info: @entangle('registration') }"
        x-show="vendor_info"
        x-transition.duration.250ms
        >
        <x-cards.footer>
            <button></button>
            <x-cards.button
                wire:click="$dispatchTo('entry.vendor-registration', 'confirmProcessStep', { process_step: 'vendor_info' })"
                button_color=green
                >
                Confirm Details
            </x-cards.button>
        </x-cards.footer>
    </div>
</x-cards.wrapper>

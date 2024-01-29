<div>
	<x-page.top
        h1="{!! $user->full_name !!}"
        p="{{$user->this_vendor ? 'Team Member for ' . $user->this_vendor->name : ''}}"
        {{-- right_button_href="{{auth()->user()->can('update', $vendor) ? route('vendors.show', $vendor->id) : ''}}"
        right_button_text="Edit Vendor" --}}
        >
    </x-page.top>

	<div class="grid max-w-xl grid-cols-4 gap-4 mx-auto lg:max-w-5xl sm:px-6">
        <div class="col-span-4 space-y-4 lg:col-span-2 lg:h-32 lg:sticky lg:top-5">
            {{-- USER DETAILS --}}
            <div class="col-span-4 lg:col-span-2">
                <x-cards.wrapper>
                        <x-cards.heading>
                            <x-slot name="left">
                                <h1 class="text-lg">User Details</h1>
                                {{-- @if($registration)
                                    <p class="max-w-2xl mt-1 text-sm text-gray-500">Confirm {{$vendor->business_name}} information.</p>
                                @endif --}}
                            </x-slot>

                            @can('update', $user)
                                <x-slot name="right">
                                    <x-cards.button
                                        {{-- wire:click="$dispatchTo('vendors.vendor-create', 'editVendor', { vendor: {{$vendor->id}} })" --}}
                                        >
                                        Edit User
                                    </x-cards.button>
                                    {{-- <livewire:vendor-docs.vendor-doc-create /> --}}
                                </x-slot>
                            @endcan
                        </x-cards.heading>

                        <x-cards.body>
                            <x-lists.ul>
                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Name'"
                                    :line_data="$user->full_name"
                                    {{-- :bubble_message="'Success'" --}}
                                    >
                                </x-lists.search_li>

                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Email'"
                                    :line_data="$user->email"
                                    >
                                </x-lists.search_li>

                                {{-- Retail --}}
                                {{-- @if($vendor->business_type != 'Retail')
                                    <x-lists.search_li
                                        :basic=true
                                        :line_title="'Vendor Address'"
                                        href="{{$vendor->getAddressMapURI()}}"
                                        :href_target="'blank'"
                                        :line_data="$vendor->full_address"
                                        >
                                    </x-lists.search_li>
                                @endif
                                --}}
                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Cell Phone'"
                                    :line_data="$user->cell_phone"
                                    >
                                </x-lists.search_li>

                                @if($user->this_vendor)
                                    @can('update', $user)
                                        <x-lists.search_li
                                            :basic=true
                                            :line_title="'Start Date'"
                                            :line_data="$user->this_vendor->pivot->start_date->format('m/d/Y')"
                                            >
                                        </x-lists.search_li>

                                        <x-lists.search_li
                                            :basic=true
                                            :line_title="'Hourly Rate'"
                                            :line_data="money($user->this_vendor->pivot->hourly_rate)"
                                            >
                                        </x-lists.search_li>
                                    @endcan

                                    <x-lists.search_li
                                        :basic=true
                                        :line_title="'Vendor Role'"
                                        :line_data="$user->getVendorRole($user->this_vendor->id)"
                                        >
                                    </x-lists.search_li>
                                @endif
                            </x-lists.ul>
                        </x-cards.body>
                </x-cards.wrapper>
            </div>
        </div>

        {{-- VENDOR DETAILS --}}
        @if($user->this_vendor)
            <div class="col-span-4 lg:col-span-2">
                <livewire:vendors.vendor-details :vendor="$user->vendor">
            </div>
        @endif


        {{-- VENDOR TEAM MEMBERS --}}
        {{-- @if($vendor->business_type != 'Retail')
            <div class="col-span-4 lg:col-span-2">
                <livewire:users.team-members :vendor="$vendor">
            </div>
        @endif --}}
	</div>
    <livewire:vendors.vendor-create />
</div>

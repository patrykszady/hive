<div>
	<x-page.top
        h1="{!! $vendor->business_name !!}"
        p=""
        right_button_href="{{auth()->user()->can('update', $vendor) ? route('vendors.show', $vendor->id) : ''}}"
        right_button_text="Edit Vendor"
        >
    </x-page.top>

	<div class="max-w-xl lg:max-w-5xl grid grid-cols-4 gap-4 sm:px-6 mx-auto">
		{{--  lg:h-32 lg:sticky lg:top-5 --}}
		<div class="col-span-4 lg:col-span-2">
			{{-- PROJECT DETAILS --}}
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1 class="text-lg">Vendor Details</b></h1>
					</x-slot>
		
                    @can('update', $vendor)
                        <x-slot name="right">
                            <x-cards.button href="{{route('vendors.show', $vendor->id)}}">
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
					</x-lists.ul>
				</x-cards.body>

                @can('update', $vendor)
                    @if($vendor->business_type == 'Sub')
                        <x-cards.footer>
                            <x-cards.button 
                                href="{{route('vendors.payment', $vendor->id)}}" 
                                white_button=true
                                {{-- class="w-full" --}}
                                >
                                Vendor Payment
                            </x-cards.button>
                        </x-cards.footer>
                    @endif
                @endcan
			</x-cards.wrapper>
		</div>

		<div class="col-span-4 lg:col-span-2">
            {{-- VENDOR TEAM MEMBERS --}}
            @if($vendor->business_type == "Sub")
                <x-cards.wrapper class="max-w-2xl">
                    <x-cards.heading>
                        <x-slot name="left">
                            {{-- attribute --}}
                            <h1>Team Members</h1>
                        </x-slot>
                    
                        @can('create', App\Models\User::class)
                            <x-slot name="right">                            
                                    <x-cards.button wire:click="$emit('newMember', 'vendor', {{$vendor->id}})">
                                        Add team member
                                    </x-cards.button>      
                        
                                @livewire('users.users-form')                
                            </x-slot>
                        @endcan
                    </x-cards.heading>
                    
                    <x-lists.ul>
                        @foreach($users as $user)
                            @php
                                $line_details = [
                                    // 1 => [
                                    //     'text' => 'Vendor role',
                                    //     'icon' => 'M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z'
                                    //     ],                
                                ];
                            @endphp
                    
                            <x-lists.search_li
                                wire:click="$emit('showMember', {{$user->id}})"
                                {{-- href="#" --}}
                                :line_details="$line_details"
                                :line_title="$user->full_name"
                                :bubble_message="$user->vendor_role"
                                {{-- :bubble_message="$user->vendor_role" --}}
                                >
                            </x-lists.search_li>
                    
                            {{-- 2-7-2022 ..only render when clicked above... --}}
                            @livewire('users.users-show', ['user' => $user])
                        @endforeach
                    </x-lists.ul>
                </x-cards.wrapper>
            @endif
		</div>
	</div>
</div>
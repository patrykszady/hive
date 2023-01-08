<div class="xl:relative max-w-xl lg:max-w-5xl sm:px-6 mx-auto">
    <form wire:submit.prevent="{{$view_text['form_submit']}}">
        <x-cards.wrapper class="max-w-2xl mx-auto">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                <x-slot name="right">
                    <x-cards.button href="{{route('vendors.index')}}">
                        All vendors
                    </x-cards.button>

                    @if(request()->routeIs('vendors.edit'))
                        <x-cards.button href="{{route('vendors.show', $vendor->id)}}">
                            Show Vendor
                        </x-cards.button>
                    @endif
                </x-slot>
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- BIZ NAME --}}
                <x-forms.row 
                    wire:model.debounce.500ms="vendor.business_name" 
                    errorName="vendor.business_name" 
                    name="vendor.business_name"
                    text="Business Name"
                    type="text" 
                    textSize="xl" 
                    placeholder="Business Name" 
                    autofocus
                    > 
                </x-forms.row>

                {{-- existing vendors from vendors.business_name --}}
                @if(!$errors->has('vendor.business_name'))
                    <div 
                        x-data="{open: @entangle('vendor.business_name')}" 
                        x-show="open" 
                        x-transition.duration.150ms
                        >

                        @if(!is_null($vendors_found))
                            <x-misc.hr>
                                Choose Existing Vendor
                            </x-misc.hr>
                                <x-lists.ul :class="'mt-4'">
                                    @foreach ($vendors_found as $vendor_found)
                                        @php
                                            $line_details = [
                                                1 => [
                                                    'text' => $vendor_found->business_name,
                                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                            
                                                    ],
                                                2 => [
                                                    'text' => $vendor_found->one_line_address,
                                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
                            
                                                    ],
                                                ];
                                        @endphp
                            
                                        <x-lists.search_li
                                            href="{{route('vendors.show', $vendor_found->id)}}"
                                            :line_details="$line_details"
                                            :line_title="$vendor_found->business_name"
                                            :bubble_message="$vendor_found->business_type"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            <x-misc.hr>
                                Or Create New Vendor
                            </x-misc.hr>
                        @else
                            @if(!$errors->has('vendor.business_name'))
                                <x-misc.hr :class="'mt-4'">
                                    Create Vendor
                                </x-misc.hr>
                            @endif
                        @endif
                    </div>
                
                    {{-- BUSINESS TYPE --}}
                    <div 
                        x-data="{open: @entangle('vendor.business_name')}" 
                        x-show="open" 
                        x-transition.duration.150ms
                        >
                        <x-forms.row 
                            wire:model="vendor.business_type" 
                            errorName="vendor.business_type" 
                            name="vendor_id" 
                            text="Busienss Type"
                            type="dropdown"
                            >
                            <option value="" readonly>Select Type</option>
                            <option value="Sub">Subcontractor</option>
                            <option value="Retail">Retail</option>
                            <option value="W9">W9</option>
                            <option value="DBA">DBA</option>                            
                        </x-forms.row>
                    </div>

                    {{-- USER --}}
                    <div 
                        {{-- x-text="console.log($wire.get('vendor.business_type') == 'Sub')" --}}
                        {{-- x-data="{ open: $wire.get('vendor.business_type') == 'Sub' ? 'true' : 'false' }"  --}}
                        x-data="{ open: @entangle('user') }" 
                        x-show="open" 
                        x-transition.duration.150ms
                        >

                        {{-- @dd($vendor) --}}
                        {{-- USER MODAL --}}
                        {{-- {{$vendor->add_type}} --}}
                        {{-- @dd($vendor_add_type[1]) --}}
                        <x-forms.row
                            wire:click="$emit('newMember', ['vendor', '{{$vendor_add_type}}'])"
                            errorName=""
                            name=""
                            text="Owner"
                            type="button"
                            buttonText="{{isset($user->first_name) ? $user->full_name : 'Add Owner'}}"
                            {{--  x-text="splits == true ? 'Edit Splits' : 'Add Splits'" --}}
                            >    
                        </x-forms.row>
                        {{-- existing found user vendors --}}
                        <div
                            x-data="{open: @entangle('user_vendors')}" 
                            x-show="open" 
                            x-transition.duration.150ms
                            >

                            @if(!is_null($user_vendors))
                                @if(!$user_vendors->isEmpty())
                                    <x-misc.hr :class="'mt-4'">
                                        Existing User Vendors
                                    </x-misc.hr>
                                        <x-lists.ul :class="'mt-4'">
                                            @foreach ($user_vendors as $user_vendor_found)
                                                @php
                                                    $line_details = [
                                                        1 => [
                                                            //'text' => $user_vendor_found->pivot->role_id == 1 ? 'Admin' : 'Member',
                                                            'text' => 'Vendor User Role HERE',
                                                            'icon' => 'M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 2.636.818.818 0 01-.36.98A7.465 7.465 0 0114.5 16z'
                                                            ],
                                                        2 => [
                                                            'text' => $user_vendor_found->address,
                                                            'icon' => 'M4 16.5v-13h-.25a.75.75 0 010-1.5h12.5a.75.75 0 010 1.5H16v13h.25a.75.75 0 010 1.5h-3.5a.75.75 0 01-.75-.75v-2.5a.75.75 0 00-.75-.75h-2.5a.75.75 0 00-.75.75v2.5a.75.75 0 01-.75.75h-3.5a.75.75 0 010-1.5H4zm3-11a.5.5 0 01.5-.5h1a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-1a.5.5 0 01-.5-.5v-1zM7.5 9a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1zM11 5.5a.5.5 0 01.5-.5h1a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-1a.5.5 0 01-.5-.5v-1zm.5 3.5a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1z'
                                                            ],
                                                        ];
                                                @endphp  

                                                <x-lists.search_li
                                                    {{-- href="{{route('vendors.show', $user_vendor_found->id)}}" --}}
                                                    :line_details="$line_details"
                                                    :line_title="$user_vendor_found->business_name"
                                                    :bubble_message="$user_vendor_found->business_type"
                                                    >
                                                </x-lists.search_li>
                                            @endforeach
                                        </x-lists.ul>
                                    <x-misc.hr :class="'mt-4'">
                                        Create New Vendor for User
                                    </x-misc.hr>
                                @else
                                    <x-misc.hr :class="'mt-4'">
                                        Create Vendor
                                    </x-misc.hr> 
                                @endif
                            @else
                                <x-misc.hr :class="'mt-4'">
                                    Create Vendor
                                </x-misc.hr>                 
                            @endif
                        </div>             
                    </div>

                    {{-- ADDRESS --}}
                    <div 
                        x-data="{ open: @entangle('address') }" 
                        x-show="open" 
                        x-transition.duration.150ms
                        class="space-y-4 my-4"
                        >
                        
                        <x-forms.row 
                            wire:model.debounce.500ms="vendor.address" 
                            errorName="vendor.address" 
                            name="vendor.address" 
                            text="Address"
                            type="text"
                            placeholder="Street Address | 123 Main St" 
                            >
                        </x-forms.row>

                        <x-forms.row 
                            wire:model.debounce.500ms="vendor.address_2" 
                            errorName="vendor.address_2" 
                            name="vendor.address_2" 
                            text=""
                            type="text"
                            placeholder="Unit Number | Suite 106" 
                            >
                        </x-forms.row>

                        <x-forms.row 
                            wire:model.debounce.500ms="vendor.city" 
                            errorName="vendor.city" 
                            name="vendor.city" 
                            text=""
                            type="text"
                            placeholder="City | Arlington Heights" 
                            >
                        </x-forms.row>

                        <x-forms.row 
                            wire:model.debounce.500ms="vendor.state" 
                            errorName="vendor.state" 
                            name="vendor.state" 
                            text=""
                            type="text"
                            placeholder="State | IL"
                            maxlength="2"
                            minlength="2"
                            >
                        </x-forms.row>

                        <x-forms.row 
                            wire:model.debounce.500ms="vendor.zip_code" 
                            errorName="vendor.zip_code" 
                            name="vendor.zip_code" 
                            text=""
                            type="number"
                            placeholder="Zipcode | 60070"
                            maxlength="5"
                            minlength="5"
                            inputmode="numeric"
                            >
                        </x-forms.row>
                    </div>
                @endif
            </x-cards.body>

            {{-- FOOTER --}}
            <div 
                x-data="{ open: @entangle('vendor.address'), retail: @entangle('retail') }" 
                x-show="retail || open" 
                x-transition.duration.250ms
                >
                <x-cards.footer>
                    <button 
                        type="button"
                        {{-- class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" --}}
                        > 
                        {{-- Cancel --}}
                    </button>
                    <button 
                        type="submit"
                        {{-- x-bind:disabled="expense.project_id" --}}
                        class="ml-3 inline-flex justify-center disabled:opacity-50 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{$view_text['button_text']}}
                    </button>
                </x-cards.footer>
            </div>
        </x-cards.wrapper>
        </div>
    </form>
</div>

{{-- USER MODAL --}}
@livewire('users.users-form')
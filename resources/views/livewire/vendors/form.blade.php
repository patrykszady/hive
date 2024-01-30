<x-modals.modal>
    <form wire:submit="{{$view_text['form_submit']}}">
        <x-cards.wrapper class="max-w-2xl mx-auto">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                {{-- @if(request()->routeIs('vendors.edit'))
                    <x-slot name="right">
                            <x-cards.button href="{{route('vendors.show', $vendor->id)}}">
                                Show Vendor
                            </x-cards.button>
                    </x-slot>
                @endif --}}
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- BIZ NAME TEXT--}}
                @if($view_text['card_title'] != 'Update Vendor')
                    <div
                        x-data="{via_vendor: @entangle('via_vendor')}"
                        x-transition
                        >
                        <x-forms.row
                            wire:model.live.debounce.1000ms="business_name_text"
                            x-bind:disabled="via_vendor"
                            errorName="business_name_text"
                            name="business_name_text"
                            text="Business Name"
                            type="text"
                            textSize="xl"
                            placeholder="Business Name"
                            autofocus
                            >
                        </x-forms.row>
                    </div>
                @endif
                {{-- <div
                    x-data="{business_name: @entangle('form.business_name')}"
                    x-show="!business_name"
                    >
                </div> --}}
                @if(!$errors->has('business_name_text'))
                    <div
                        x-data="{business_name_text: @entangle('business_name_text')}"
                        x-show="business_name_text"
                        x-transition
                        >

                        @if(!is_null($existing_vendors))
                            @if(!$existing_vendors->isEmpty())
                                <x-misc.hr :class="'mt-4'">
                                    Existing Vendors
                                </x-misc.hr>

                                <x-lists.ul :class="'mt-4'">
                                    @foreach ($existing_vendors as $vendor_found)
                                        @php
                                            $line_details = [
                                                1 => [
                                                    'text' => $vendor_found->business_name,
                                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'

                                                    ],
                                                2 => [
                                                    'text' => $vendor_found->business_type,
                                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'

                                                    ],
                                                ];
                                        @endphp

                                        <x-lists.search_li
                                            {{-- wire:click="$emitSelf('addVendorToVendor', {{$vendor_found->id}})" --}}
                                            href="{{route('vendors.show', $vendor_found->id)}}"
                                            hrefTarget="_blank"
                                            :line_details="$line_details"
                                            :line_title="$vendor_found->business_name"
                                            :bubble_message="$vendor_found->business_type"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            @endif
                        @endif

                        @if(!is_null($add_vendors_vendor))
                            @if(!$add_vendors_vendor->isEmpty())
                                <x-misc.hr :class="'mt-4'" :sectionclass="'bg-indigo-600'">
                                    Add Vendor
                                </x-misc.hr>

                                <x-lists.ul :class="'mt-4'">
                                    @foreach ($add_vendors_vendor as $vendor_found)
                                        @php
                                            $line_details = [
                                                1 => [
                                                    'text' => $vendor_found->business_name,
                                                    'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'

                                                    ],
                                                2 => [
                                                    'text' => $vendor_found->business_type,
                                                    'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'

                                                    ],
                                                ];
                                        @endphp

                                        <x-lists.search_li
                                            wire:click="$dispatchSelf('addVendorToVendor', { vendor_id: {{$vendor_found->id}} })"
                                            :line_details="$line_details"
                                            :line_title="$vendor_found->business_name"
                                            :bubble_message="$vendor_found->business_type"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            @endif
                        @endif

                        @if($view_text['card_title'] != 'Update Vendor')
                            <x-misc.hr>
                                Create New Vendor
                            </x-misc.hr>
                        @endif
                        {{-- <div class="container flex flex-col items-center mt-10">
                            <button
                                type="button"
                                wire:click="$emitSelf('newVendor')"
                                class="px-20 py-2 ml-3 text-sm font-medium text-gray-800 bg-white border border-indigo-600 border-solid rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-0 hover:text-white ring-indigo-500 ring-2 ring-offset-2"
                                >
                                    Create New Vendor
                            </button>
                        </div> --}}

                        {{-- BUSINESS NAME & TYPE --}}
                        <div
                            {{-- business_name = business_name_text --}}
                            x-data="{user: @entangle('user'), business_type: @entangle('form.business_type'), business_name: @entangle('form.business_name'), via_vendor: @entangle('via_vendor')}"
                            x-show="business_name"
                            class="my-4 space-y-4"
                            x-transition
                            >

                            <x-forms.row
                                wire:model.live="form.business_name"
                                errorName="form.business_name"
                                name="form.business_name"
                                text="Busienss Name"
                                x-bind:disabled="business_name"
                                {{-- autofocus --}}
                                {{-- 4-28-23 disabled only on new vendor, not on editVendor --}}
                                {{-- x-bind:disabled="!vendor_id_disabled || business_type_disabled == '1099'" --}}
                                >
                                {{--3-21-23 if you need to change business name, undo and reset component --}}
                                {{-- 3-21-23 (side button) radioHint="Change Name" --}}
                            </x-forms.row>

                            <x-forms.row
                                wire:model.live="form.business_type"
                                errorName="form.business_type"
                                name="form.business_type"
                                text="Busienss Type"
                                type="dropdown"
                                {{-- disabled only on editVendor, not on new vendor --}}
                                x-bind:disabled="via_vendor || user"
                                >
                                <option value="" readonly>Select Type</option>
                                <option value="Sub">Subcontractor</option>
                                {{--  x-bind:disabled="team_member != 'index'" --}}
                                <option value="Retail">Retail</option>
                                <option value="1099">1099</option>
                                <option value="DBA">DBA</option>
                            </x-forms.row>
                        </div>

                        {{-- USER --}}
                        <div
                            x-data="{ user: @entangle('user'), team_member: @entangle('team_member'), business_type: @entangle('form.business_type'), via_vendor: @entangle('via_vendor') }"
                            x-show="business_type == 'Sub' || business_type == '1099' || business_type == 'DBA'"
                            x-transition
                            >
                            {{-- USER MODAL --}}
                            <x-forms.row
                                wire:click="$dispatchTo('users.user-create', 'newMember', { model: 'vendor', model_id: '{{$vendor_add_type}}' })"
                                x-bind:disabled="team_member != 'index' || via_vendor"
                                {{-- disabled="{{!is_numeric($team_member) ? 'TRUE' : 'FALSE'}}" --}}
                                errorName=""
                                text="Owner"
                                type="button"
                                buttonText="{{isset($user->first_name) ? $user->full_name : 'Add Owner'}}"
                                >
                            </x-forms.row>

                            {{--  || $via_vendor --}}
                            @if($team_member == 'index')
                                <livewire:users.user-create />
                            @endif
                        </div>

                        {{-- existing Vendors found for User  --}}
                        <div
                            x-data="{team_member: @entangle('team_member'), business_type: @entangle('form.business_type')}"
                            x-show="team_member && (business_type == 'Sub' || business_type == '1099' || business_type == 'DBA')"
                            x-transition
                            >

                            @if(!is_null($user_vendors))
                                @if(!$user_vendors->isEmpty())
                                    <x-misc.hr :class="'mt-4'">
                                        {{$user->first_name}}'s Existing Vendors
                                    </x-misc.hr>
                                        <x-lists.ul :class="'mt-4'">
                                            @foreach ($user_vendors as $user_vendor_found)
                                                @php
                                                    $line_details = [
                                                        // 1 => [
                                                        //     'text' => $user_vendor_found->pivot->role_id == 1 ? 'Admin' : 'Member',
                                                        //     // 'text' => 'Vendor User Role HERE',
                                                        //     'icon' => 'M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 2.636.818.818 0 01-.36.98A7.465 7.465 0 0114.5 16z'
                                                        //     ],
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
                                        New Vendor Details
                                    </x-misc.hr>
                                @else
                                    <x-misc.hr :class="'mt-4'">
                                        Create Vendor
                                    </x-misc.hr>
                                @endif
                            @endif
                        </div>

                        {{-- ADDRESS --}}
                        <div
                            x-data="{business_type: @entangle('form.business_type'), address: @entangle('address') }"
                            x-show="(business_type == 'Sub' || business_type == '1099' || business_type == 'DBA') && address"
                            x-transition
                            class="my-4 space-y-4"
                            >

                            @include('components.forms._address_form', ['model' => 'vendor'])

                            <x-forms.row
                                wire:model.live.debounce.500ms="form.business_email"
                                errorName="form.business_email"
                                name="business_email"
                                text="Business Email"
                                type="text"
                                placeholder="office@gs.construction"
                                >
                            </x-forms.row>

                            <x-forms.row
                                wire:model.live.debounce.500ms="form.business_phone"
                                errorName="form.business_phone"
                                name="business_phone"
                                text="Business Phone"
                                type="number"
                                maxlength="10"
                                minlength="10"
                                inputmode="numeric"
                                placeholder="8474304439"
                                >
                            </x-forms.row>
                        </div>
                    </div>
                @endif
            </x-cards.body>

            {{-- FOOTER --}}
            <x-cards.footer>
                <button
                    wire:click="resetModal"
                    type="button"
                    x-on:click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                    Cancel
                </button>
                <div
                    {{-- x-data="{ address: @entangle('vendor.zip_code').live, biz_type: @entangle('vendor.business_type').live }"
                    x-show="address || biz_type == 'Retail'"
                    x-transition.duration.250ms --}}
                    >
                    <button
                        type="submit"
                        {{-- x-on:click="open = false" --}}
                        {{-- x-bind:disabled="expense.project_id" --}}
                        class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{$view_text['button_text']}}
                    </button>
                </div>
            </x-cards.footer>
        </x-cards.wrapper>
    </form>
</x-modals.modal>

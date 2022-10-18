<x-modals.modal :class="'max-w-lg'">
    <form wire:submit.prevent="{{$view_text['form_submit']}}"> 
        <x-cards.heading>
            <x-slot name="left">
                <h1>Add User to vendor.business_name or client.name</h1>
            </x-slot>
        
            <x-slot name="right">

            </x-slot>
        </x-cards.heading>
    
        <x-cards.body :class="'space-y-4 my-4'">
            <x-forms.row 
                wire:model.debounce.1000ms="user_cell" 
                errorName="user_cell" 
                name="user_cell" 
                text="User Cell Phone"
                type="number"
                maxlength="10"
                minlength="10"
                inputmode="numeric"
                placeholder="8474304439"
                autofocus
                >    
            </x-forms.row>

            <x-forms.row
                wire:click.prevent="user_cell"
                errorName=""
                name=""
                text=""
                type="button"
                buttonText="Search Users"
                >    
            </x-forms.row>

            {{-- NEW USER DETAILS --}}
            <div 
                x-data="{ open: @entangle('user_form') }" 
                x-show="open" 
                x-transition.duration.150ms
                class="space-y-4 my-4"
                >
                <hr>

                <x-forms.row 
                    wire:model.debounce.1000ms="user.first_name" 
                    errorName="user.first_name" 
                    name="user.first_name" 
                    text="First Name"
                    >
                </x-forms.row>

                <x-forms.row 
                    wire:model.debounce.1000ms="user.last_name" 
                    errorName="user.last_name" 
                    name="user.last_name" 
                    text="Last Name"
                    >
                </x-forms.row>

                <x-forms.row 
                    wire:model.debounce.1000ms="user.email" 
                    errorName="user.email" 
                    name="user.email"
                    text="User Email"
                    >
                </x-forms.row>
            </div>

            {{-- VENDOR USER FORM --}}
            <div 
                x-data="{ open: @entangle('vendor_user_form') }" 
                x-show="open" 
                x-transition.duration.150ms
                class="space-y-4 my-4"
                >

                <hr>
                {{-- USER NAME --}}
                <x-forms.row 
                    wire:model.debounce.1000ms="user.full_name" 
                    errorName="user.full_name" 
                    name="user.full_name" 
                    text="User Name"
                    {{-- disabled sometimes only --}}
                    disabled
                    >
                </x-forms.row>

                @if($user->vendors)
                    <hr>

                    <x-forms.row 
                        wire:model.debounce.1000ms="user_vendor_id" 
                        errorName="user_vendor_id"
                        name="user_vendor_id"
                        text="User Existing Vendors"
                        type="radio"
                        >

                        <div 
                            class="space-y-4" 
                            x-data="{user_vendor_id: @entangle('user_vendor_id')}"
                            >
                            <!--
                                        Checked: "border-transparent", Not Checked: "border-gray-300"
                                        Active: "ring-2 ring-indigo-500"
                                    -->
                            {{-- Vendors where User is Admin --}}
                            @foreach ($this->user->vendors()->wherePivot('role_id', 1)->get() as $vendor)
                            <label
                                class="{{ $user_vendor_id == $vendor->id ? 'border-transparent ring-2 ring-indigo-500 ' : 'border-gray-300' }}
                                        relative block bg-white border rounded-lg shadow-sm px-6 py-4 cursor-pointer sm:flex sm:justify-between focus:outline-none hover:bg-gray-50"
                                >
        
                                <input type="radio" name="server-size" class="sr-only" x-model="user_vendor_id"
                                    value="{{$vendor->id}}" aria-labelledby="{{$vendor->id}}"
                                    aria-describedby="server-size-{{$vendor->id}}-description-0 server-size-{{$vendor->id}}-description-1">
        
                                <div class="flex items-center">
                                    <div class="text-sm">
                                        <div id="server-size-{{$vendor->id}}-description-10" class="text-gray-500">
                                            <p id="{{$vendor->id}}" class="sm:inline font-medium text-gray-900">{{$vendor->business_name}}</p>
                                            <span class="hidden sm:inline sm:mx-1" aria-hidden="true">&middot;</span>
                                            <p class="sm:inline">{{$vendor->business_type}}</p>
                                        </div>
                                        <div id="server-size-{{$vendor->id}}-description-0" class="text-gray-500">
                                            <p class="sm:inline">{{$vendor->address}}</p>
                                            <span class="hidden sm:inline sm:mx-1" aria-hidden="true">&middot;</span>
                                            <p class="sm:inline">{{$vendor->city . ', ' . $vendor->state . ' ' . $vendor->zip_code}}</p>
                                        </div>
                                    </div>
                                </div>
        
                                <div id="server-size-{{$vendor->id}}-description-1"
                                    class="mt-2 flex text-sm sm:mt-0 sm:block sm:ml-4 sm:text-right">
                                    <div class="font-medium text-gray-900">{{ $vendor->user_role }}</div>
                                    <div class="ml-1 text-gray-500 sm:ml-0">Vendor role</div>
                                </div>
                                <!--
                                    Active: "border", Not Active: "border-2"
                                    Checked: "border-indigo-500", Not Checked: "border-transparent"
                                    -->
                                <div class="
                                            {{ $user_vendor_id == $vendor->id ? 'border-indigo-500 border' : 'border-transparent border-2' }}
                                            absolute -inset-px rounded-lg pointer-events-none" aria-hidden="true">
                                </div>
                            </label>
                            @endforeach
                            <label
                                class="{{ $user_vendor_id == "NEW" ? 'border-transparent ring-2 ring-indigo-500 ' : 'border-gray-300' }}
                                        relative block bg-white border rounded-lg shadow-sm px-6 py-4 cursor-pointer sm:flex sm:justify-between focus:outline-none hover:bg-gray-50"
                                >
        
                                <input type="radio" name="server-size" class="sr-only" x-model="user_vendor_id"
                                    value="NEW" aria-labelledby="NEW"
                                    aria-describedby="server-size-NEW-description-0 server-size-NEW-description-1">
        
                                <div class="flex items-center">
                                    <div class="text-sm">
                                        <div id="server-size-NEW-description-10" class="text-gray-500">
                                            <p id="NEW" class="sm:inline font-medium text-gray-900">New User Vendor</p>
                                            <span class="hidden sm:inline sm:mx-1" aria-hidden="true">&middot;</span>
                                            <p class="sm:inline">New User Vendor</p>
                                        </div>
                                        <div id="server-size-NEW-description-0" class="text-gray-500">
                                            <p class="sm:inline">New User Vendor</p>
                                            <span class="hidden sm:inline sm:mx-1" aria-hidden="true">&middot;</span>
                                            <p class="sm:inline">New User Vendor</p>
                                        </div>
                                    </div>
                                </div>
        
                                <div id="server-size-NEW-description-1"
                                    class="mt-2 flex text-sm sm:mt-0 sm:block sm:ml-4 sm:text-right">
                                    <div class="font-medium text-gray-900"></div>
                                    <div class="ml-1 text-gray-500 sm:ml-0"></div>
                                </div>
                                <!--
                                    Active: "border", Not Active: "border-2"
                                    Checked: "border-indigo-500", Not Checked: "border-transparent"
                                    -->
                                <div class="
                                            {{ $user_vendor_id == "NEW" ? 'border-indigo-500 border' : 'border-transparent border-2' }}
                                            absolute -inset-px rounded-lg pointer-events-none" aria-hidden="true">
                                </div>
                            </label>
                        </div>
                    </x-forms.row>
                @endif

                {{-- If User doesnt have any vendors or if New User Vendor is selected above W9 / DBA / Payroll ... --}}
                @if(isset($this->via_vendor->new))
                    <hr>
                    <x-forms.row 
                        wire:model="user.type" 
                        errorName="user.type" 
                        name="user.type" 
                        text="Employee Type"
                        type="dropdown"
                        autofocus
                        >

                        <option value="" readonly>Employee Type</option>
                        <option value="W9">W9</option>
                        <option value="DBA">DBA</option>
                        <option value="Sub">Subcontractor</option>
                    </x-forms.row>
                @endif

                {{-- VIA VENDOR --}}
                <div
                    x-data="{ open: @entangle('user.type') }" 
                    x-show="open" 
                    x-transition.duration.150ms
                    class="space-y-4 my-4"
                    >

                    <hr>
                    {{-- VENDOR NAME --}}
                    <x-forms.row
                        wire:model.defer="via_vendor.business_name" 
                        errorName="via_vendor.business_name" 
                        name="via_vendor.business_name" 
                        text="Business Name"
                        type="text"
                        placeholder="Business Name"
                        :disabled="$user->type == 'Sub' ? false : true"
                        {{-- :disabled="isset($via_vendor->business_name) ? true : false" --}}
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model.debounce.500ms="via_vendor.address" 
                        errorName="via_vendor.address" 
                        name="via_vendor.address" 
                        text="Address"
                        type="text"
                        placeholder="Street Address | 123 Main St" 
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model="via_vendor.address_2" 
                        errorName="via_vendor.address_2" 
                        name="via_vendor.address_2" 
                        text=""
                        type="text"
                        placeholder="Unit Number | Suite 106" 
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model.debounce.500ms="via_vendor.city" 
                        errorName="via_vendor.city" 
                        name="via_vendor.city" 
                        text=""
                        type="text"
                        placeholder="City | Arlington Heights" 
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model.debounce.500ms="via_vendor.state" 
                        errorName="via_vendor.state" 
                        name="via_vendor.state" 
                        text=""
                        type="text"
                        placeholder="State | IL"
                        maxlength="2"
                        minlength="2"
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model.debounce.500ms="via_vendor.zip_code" 
                        errorName="via_vendor.zip_code" 
                        name="via_vendor.zip_code" 
                        text=""
                        type="number"
                        placeholder="Zipcode | 60070"
                        maxlength="5"
                        minlength="5"
                        inputmode="numeric"
                        >
                    </x-forms.row>
                </div>
            </div>

            {{-- USER VENDOR ROLE AND HOURLY PAY --}}
            <div 
                x-data="{ open: @entangle('via_vendor.business_name') }" 
                x-show="open" 
                x-transition.duration.150ms
                class="space-y-4 my-4"
                >

                <hr>

                {{-- USER / VENDOR ROLE --}}
                <x-forms.row 
                    wire:model="user.role" 
                    errorName="user.role" 
                    name="user.role" 
                    text="User Role"
                    type="dropdown"
                    autofocus
                    >

                    <option value="" readonly>Select Role</option>
                    <option value="1">Admin</option>
                    <option value="2">Team Member</option>
                </x-forms.row>

                {{-- USER / VENDOR HOURLY PAY --}}
                <x-forms.row 
                    wire:model.debounce.1000ms="user.hourly_rate" 
                    errorName="user.hourly_rate" 
                    name="user.hourly_rate" 
                    text="User Hourly Pay"
                    type="number"
                    inputmode="numeric"
                    placeholder="28"
                    >    
                </x-forms.row>
            </div>

            <div 
                x-data="{ open: @entangle('client_user_form') }" 
                x-show="open" 
                x-transition.duration.150ms
                class="space-y-4 my-4"
                >

                <hr>

                {{-- USER DETAILS (5-27-22 this is double with VENDOR USER FORM above?--}}
                {{-- USER NAME --}}
                <x-forms.row 
                    wire:model.debounce.1000ms="user.full_name" 
                    errorName="user.full_name" 
                    name="user.full_name" 
                    text="User Name"
                    {{-- disabled sometimes only --}}
                    disabled
                    >
                </x-forms.row>
            </div>

            @if($errors->has('user_vendor_validate'))
                <div class="px-6">
                    <x-forms.error errorName="user_vendor_validate" />
                </div>
            @endif
        </x-cards.body>

        {{-- <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense"> --}}
        <x-cards.footer>
            <button
                {{-- emit = Cancel and remove all data to default... --}}
                wire:click="$emit('resetModal')"
                type="button"
                x-on:click="open = false"
                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </button>
            {{-- <x-cards.button 
                
                >
                Submit
            </x-cards.button> --}}
            <button 
                {{-- disabled="disabled" --}}
                {{-- x-on:click="open = false" --}}
                type="submit"
                class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{$view_text['button_text']}}
            </button>
        </x-cards.footer>
    </form>  
</x-modals.modal>
{{-- @dd($errors) --}}

<x-modals.modal :class="'max-w-lg'">
    <form wire:submit.prevent="{{$view_text['form_submit']}}"> 
        <x-cards.heading>
            <x-slot name="left">
                @if(isset($model))
                    @if($model['type'] == 'vendor')
                        <h1>Add User to Vendor</h1>
                    @else
                        <h1>Add User to Client</h1>
                    @endif
                @endif
            </x-slot>
        
            <x-slot name="right">

            </x-slot>
        </x-cards.heading>
    
        <x-cards.body :class="'space-y-4 my-4'">
            <x-forms.row 
                wire:model.debounce.3000ms="user_cell" 
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
                    wire:model.debounce.2000ms="user.first_name" 
                    errorName="user.first_name" 
                    name="user.first_name" 
                    text="First Name"
                    :disabled="isset($user) ? isset($user['id']) ? true : false : false"
                    >
                </x-forms.row>

                <x-forms.row 
                    wire:model.debounce.2000ms="user.last_name" 
                    errorName="user.last_name" 
                    name="user.last_name" 
                    text="Last Name"
                    :disabled="isset($user) ? isset($user['id']) ? true : false : false"
                    >
                </x-forms.row>

                <x-forms.row 
                    wire:model.debounce.2000ms="user.email" 
                    errorName="user.email" 
                    name="user.email"
                    text="User Email"
                    :disabled="isset($user) ? isset($user['id']) ? true : false : false"
                    >
                </x-forms.row>
            </div>

            {{-- USER VENDOR ROLE AND HOURLY PAY --}}
            <div 
                {{-- x-data="{ open: @entangle('via_vendor.business_name'), model: @entangle('model.type') }" --}}
                x-data="{ open: @entangle('vendor_user_form') }" 
                {{-- x-show="open && vendor_user_form"  --}}
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
                    :disabled="isset($model) ? $model['add_type'] == 'NEW' ? true : false : false"
                    autofocus
                    >

                    <option value="" readonly>Select Role</option>
                    <option value="1">Admin</option>
                    <option value="2">Team Member</option>
                </x-forms.row>

                {{-- USER / VENDOR HOURLY PAY --}}
                @if(isset($model))
                    @if($model['add_type'] != 'NEW')
                        <x-forms.row
                            {{-- x-data="{ open: false }"                 
                            x-show="open"  --}}
                            wire:model.debounce.1000ms="user.hourly_rate" 
                            errorName="user.hourly_rate" 
                            name="user.hourly_rate" 
                            text="User Hourly Pay"
                            type="number"
                            inputmode="numeric"
                            placeholder="28"
                            >    
                        </x-forms.row>
                    @endif
                @endif
            </div>

            {{-- <div 
                x-data="{ open: @entangle('client_user_form') }" 
                x-show="open" 
                x-transition.duration.150ms
                class="space-y-4 my-4"
                >

                <hr>

                USER DETAILS (5-27-22 this is double with VENDOR USER FORM above?
                USER NAME
                <x-forms.row 
                    wire:model.debounce.1000ms="user.full_name" 
                    errorName="user.full_name" 
                    name="user.full_name" 
                    text="User Name"
                    disabled sometimes only
                    disabled
                    >
                </x-forms.row>
            </div> --}}

            {{-- @if($errors->has('user_vendor_validate'))
                <div class="px-6">
                    <x-forms.error errorName="user_vendor_validate" />
                </div>
            @endif --}}
        </x-cards.body>

        <x-cards.footer>
            <button
                {{-- emit = Cancel and remove all data to default... --}}
                wire:click="$emit('resetModal')"
                type="button"
                x-on:click="open = false"
                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancel
            </button>

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
<div>
    <x-modals.modal>
        <form wire:submit="{{$view_text['form_submit']}}">
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
            </x-cards.heading>

            <x-cards.body :class="'space-y-4 my-4'">
                <x-forms.row
                    wire:model.live.debounce.500ms="user_cell"
                    errorName="user_cell"
                    name="user_cell"
                    text="User Cell Phone"
                    type="number"
                    {{-- <input type="text" name="username" maxlength="10"> --}}
                    maxlength="10"
                    minlength="10"
                    inputmode="numeric"
                    placeholder="8474304439"
                    autofocus
                    >
                </x-forms.row>

                {{-- 1/12/2023 if no user_cell or if updated ONLY --}}
                <div
                    x-data="{ user_cell: @entangle('user_cell'), user_form: @entangle('user_form') }"
                    x-show="user_cell.length == 10 && !user_form"
                    x-transition
                    {{-- class="my-4 space-y-4" --}}
                    >
                    <x-forms.row
                        wire:click="user_cell_find"
                        errorName=""
                        name=""
                        text=""
                        type="button"
                        buttonText="Search User"
                        >
                    </x-forms.row>
                </div>

                {{-- USER DETAILS --}}
                <div
                    x-data="{ open: @entangle('user_form'), user: @entangle('form.user') }"
                    x-show="open"
                    x-transition
                    class="my-4 space-y-4"
                    >
                    <x-forms.row
                        wire:model.live="form.first_name"
                        x-bind:disabled="user"
                        errorName="form.first_name"
                        placeholder="First Name"
                        name="first_name"
                        text="First Name"
                        >
                    </x-forms.row>

                    <x-forms.row
                        wire:model.live="form.last_name"
                        errorName="form.last_name"
                        x-bind:disabled="user"
                        name="last_name"
                        placeholder="Last Name"
                        text="Last Name"
                        >
                    </x-forms.row>

                    <x-forms.row
                        wire:model.live="form.email"
                        errorName="form.email"
                        x-bind:disabled="user"
                        placeholder="Email"
                        name="email"
                        text="User Email"
                        >
                    </x-forms.row>

                    {{-- save/create User here if not yet saved --}}
                    <div
                        x-data="{ user: @entangle('form.user') }"
                        x-show="!user"
                        x-transition
                        class="my-4 space-y-4"
                        >
                        <x-forms.row
                            wire:click="save_user_only"
                            errorName=""
                            name=""
                            text=""
                            type="button"
                            buttonText="Save User"
                            >
                        </x-forms.row>
                    </div>

                    {{-- CREATE/ATTACH 1099 / SUB Vendor / PAYROLL --}}
                    <div
                        x-data="{ via_vendor: @entangle('via_vendor'), model: @entangle('model.id') }"
                        x-show="via_vendor && model != 'NEW'"
                        x-transition.duration.900ms
                        class="my-4 space-y-4"
                        >
                        <hr>
                        {{-- USER / VENDOR ROLE --}}
                        <x-forms.row
                            wire:model="form.role"
                            errorName="form.role"
                            name="form.role"
                            text="User Role"
                            type="dropdown"
                            {{-- :disabled="isset($model) ? $model['id'] == 'NEW' ? true : false : false" --}}
                            autofocus
                            >
                            <option value="" readonly>Select Role</option>
                            <option value="1">Admin</option>
                            <option value="2">Team Member</option>
                        </x-forms.row>

                        <div
                            x-data="{ role: @entangle('form.role')}"
                            x-show="role == 2 ? true : false"
                            x-transition
                            class="my-4 space-y-4"
                            >
                            {{-- VIA VENDOR --}}
                            <x-forms.row
                                wire:model="form.via_vendor"
                                errorName="form.via_vendor"
                                name="form.via_vendor"
                                text="Via Vendor"
                                type="dropdown"
                                autofocus
                                >
                                <option value="" readonly>Select Vendor</option>
                                @foreach($via_vendors as $via_vendor)
                                    <option value="{{$via_vendor->id}}">{{$via_vendor->business_name}}, {{$via_vendor->business_type}}</option>
                                @endforeach
                                {{-- disabled if !$via_vendors->isEmpty --}}
                                <option value="NEW_VIA" readonly>New Vendor</option>
                            </x-forms.row>
                            <div
                                x-data="{ via_vendor: @entangle('form.via_vendor')}"
                                x-show="via_vendor == 'NEW_VIA' ? true : false"
                                x-transition
                                class="my-4 space-y-4"
                                >
                                {{-- create new vendor for user being added ... --}}
                                <x-forms.row
                                    wire:click="create_via_vendor"
                                    {{-- wire:click="$dispatchTo('vendors.vendor-create', 'viaVendor', { user: '{{$form->user}}', business_name: '{{$form->business_name}}' })" --}}
                                    {{-- wire:click="$dispatchTo('users.user-create', 'newMember', { model: 'vendor', model_id: '{{$vendor_add_type}}' })" --}}
                                    {{-- wire:click="$dispatchTo('vendors.vendor-create', 'via')" --}}
                                    errorName=""
                                    name=""
                                    text=""
                                    type="button"
                                    buttonText="Create Vendor"
                                    >
                                </x-forms.row>
                            </div>
                        </div>
                        {{-- USER / VENDOR HOURLY PAY --}}
                        {{-- <x-forms.row
                            wire:model="form.hourly_rate"
                            errorName="form.hourly_rate"
                            name="form.hourly_rate"
                            text="User Hourly Pay"
                            type="number"
                            inputmode="numeric"
                            placeholder="21"
                            >
                        </x-forms.row> --}}
                        <div
                            x-data="{ via_vendor: @entangle('form.via_vendor'), role: @entangle('form.role') }"
                            {{-- 2249993881 --}}
                            x-show="(via_vendor && via_vendor != 'NEW_VIA') || role == 1"
                            x-transition
                            class="my-4 space-y-4"
                            >
                            {{-- USER / VENDOR HOURLY PAY --}}
                            <x-forms.row
                                wire:model="form.hourly_rate"
                                errorName="form.hourly_rate"
                                name="hourly_rate"
                                text="User Hourly Pay"
                                type="number"
                                inputmode="numeric"
                                placeholder="21"
                                >
                            </x-forms.row>
                        </div>
                    </div>
                </div>
            </x-cards.body>

            <x-cards.footer>
                <button
                    {{-- wire:click="$emitTo('users.users-form', 'resetModal')" --}}
                    type="button"
                    x-on:click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>

                @if($errors->has('user_exists_on_model'))
                    <x-forms.error errorName="user_exists_on_model" />
                @endif

                <div
                    x-data="{open: @entangle('user_form'), user: @entangle('form.user')}"
                    x-show="open && user"
                    x-transition
                    >
                    <button
                        {{-- disabled="disabled" --}}
                        {{-- x-on:click="open = false" --}}
                        type="submit"
                        class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{$view_text['button_text']}}
                    </button>
                </div>
            </x-cards.footer>
        </form>
    </x-modals.modal>

    {{-- <livewire:clients.client-create /> --}}
    {{-- @if($model['id'] != 'NEW')
        <livewire:vendors.vendor-create />
    @endif --}}
</div>

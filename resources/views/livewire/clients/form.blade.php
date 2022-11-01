<div class="xl:relative max-w-xl lg:max-w-2xl sm:px-6 mx-auto">
    <form wire:submit.prevent="{{$view_text['form_submit']}}">
        <x-cards.wrapper class="max-w-3xl mx-auto">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                <x-slot name="right">
                    <x-cards.button href="{{route('clients.index')}}">
                        All Clients
                    </x-cards.button>
                        {{-- 
                    @if(request()->routeIs('expenses.edit'))
                        <x-cards.button href="{{route('expenses.show', $expense->id)}}">
                            Show Client
                        </x-cards.button>
                    @endif --}}
                </x-slot>
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- USER/Client NUMBER --}}
                <x-forms.row 
                    wire:model.debounce.500ms="user_cell" 
                    errorName="user_cell" 
                    name="user_cell"
                    text="Phone Number"
                    type="tel" 
                    hint="#" 
                    textSize="xl" 
                    placeholder="000-000-0000" 
                    inputmode="tel" 
                    {{-- lenght = 10 --}}
                    autofocus
                    > 
                </x-forms.row>

                {{-- disabled unless above is filled without error --}}
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

                {{-- ADDRESS --}}
                <div 
                    x-data="{ open: @entangle('client_user_form') }" 
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

                    {{-- BIZ NAME --}}
                    <x-forms.row 
                        wire:model.debounce.500ms="client.business_name" 
                        errorName="client.business_name" 
                        name="client.business_name"
                        text="Business Name"
                        type="text" 
                        placeholder="Business Name" 
                        autofocus
                        > 
                    </x-forms.row>

                    {{-- ADDRESS --}}
                    <x-forms.row 
                        wire:model.debounce.500ms="client.address" 
                        errorName="client.address" 
                        name="client.address" 
                        text="Address"
                        type="text"
                        placeholder="Street Address | 123 Main St" 
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model="client.address_2" 
                        errorName="client.address_2" 
                        name="client.address_2" 
                        text=""
                        type="text"
                        placeholder="Unit Number | Suite 106" 
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model.debounce.500ms="client.city" 
                        errorName="client.city" 
                        name="client.city" 
                        text=""
                        type="text"
                        placeholder="City | Arlington Heights" 
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model.debounce.500ms="client.state" 
                        errorName="client.state" 
                        name="client.state" 
                        text=""
                        type="text"
                        placeholder="State | IL"
                        maxlength="2"
                        minlength="2"
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model.debounce.500ms="client.zip_code" 
                        errorName="client.zip_code" 
                        name="client.zip_code" 
                        text=""
                        type="number"
                        placeholder="Zipcode | 60070"
                        maxlength="5"
                        minlength="5"
                        inputmode="numeric"
                        >
                    </x-forms.row>
                </div>
            </x-cards.body>
            <x-cards.footer>
                <button 
                    {{-- disabled="disabled" --}}
                    {{-- x-on:click="open = false" --}}
                    type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{$view_text['button_text']}}
                </button>
            </x-cards.footer>
        </x-cards.wrapper>
    </form>
</div>
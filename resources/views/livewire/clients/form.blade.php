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
                {{-- USER MODAL --}}
                <x-forms.row
                    {{--  --}}
                    {{--  {{$client}} --}}
                    wire:click="$emit('newMember', ['client', '{{$client->add_type}}'])"
                    errorName=""
                    name=""
                    text="User"
                    type="button"
                    buttonText="{{isset($user->first_name) ? $user->full_name : 'Add User'}}"
                    {{--  x-text="splits == true ? 'Edit Splits' : 'Add Splits'" --}}
                    >    
                </x-forms.row>

                {{-- existing found user clients --}}
                <div
                    x-data="{open: @entangle('user_clients')}" 
                    x-show="open" 
                    x-transition.duration.150ms
                    >

                    @if(!is_null($user_clients))
                        @if(!$user_clients->isEmpty())
                            <x-misc.hr :class="'mt-4'">
                                Existing User Clients
                            </x-misc.hr>
                                <x-lists.ul :class="'mt-4'">
                                    @foreach ($user_clients as $key => $user_vendor_found)
                                        @php
                                        $line_details = [
                                            // 1 => [
                                            //     'text' => $user_vendor_found->name,
                                            //     'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                        
                                            //     ],
                                            1 => [
                                                'text' => $user_vendor_found->one_line_address,
                                                'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                        
                                                ],
                                            // 3 => [
                                            //     'text' => $user_vendor_found->project->project_name,
                                            //     'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'
                                        
                                            //     ],
                                            ];
                                        @endphp
                                        
                                        <x-lists.search_li
                                            {{-- href="{{route('vendors.show', $user_vendor_found->id)}}" --}}
                                            :line_details="$line_details"
                                            :line_title="$user_vendor_found->name"
                                            :bubble_message="$user_vendor_found->business_name ? 'Business' : 'Household'"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            <x-misc.hr :class="'mt-4'">
                                Create New Client for User
                            </x-misc.hr>
                        @else
                            <x-misc.hr :class="'mt-4'">
                                Create Client
                            </x-misc.hr> 
                        @endif
                    @else
                        <x-misc.hr :class="'mt-4'">
                            Create Client
                        </x-misc.hr>                 
                    @endif
                </div>
                {{-- ADDRESS --}}
                <div 
                    x-data="{ open: @entangle('address') }" 
                    x-show="open" 
                    x-transition.duration.150ms
                    class="space-y-4 my-4"
                    >

                    {{-- BUSINESS NAME --}}
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

            {{-- FOOTER --}}
            <div 
                x-data="{ open: @entangle('user.id')}" 
                x-show="open" 
                x-transition.duration.150ms
                >
                <x-cards.footer>
                    <button 
                        {{-- disabled="disabled" --}}
                        {{-- x-on:click="open = false" --}}
                        type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{$view_text['button_text']}}
                    </button>
                </x-cards.footer>
            </div>
        </x-cards.wrapper>
    </form>
</div>

{{-- USER FORM MODAL --}}
@livewire('users.users-form')
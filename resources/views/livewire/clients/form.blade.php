<x-modals.modal>
    <form wire:submit="{{$view_text['form_submit']}}">
        <x-cards.wrapper class="max-w-2xl mx-auto">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                <x-slot name="right">
                    {{-- <x-cards.button href="{{route('clients.index')}}">
                        All Clients
                    </x-cards.button> --}}

                    {{-- @if(request()->routeIs('clients.edit'))
                        <x-cards.button href="{{route('clients.show', $client->id)}}">
                            Show Client
                        </x-cards.button>
                    @endif --}}
                </x-slot>
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                <x-forms.row
                    wire:model.live.debounce.500ms="form.client_name"
                    errorName="form.client_name"
                    name="client_name"
                    text="Client Name"
                    disabled
                    >
                </x-forms.row>

                <div
                    x-data="{ open: @entangle('client_name')}"
                    x-show="open"
                    x-transition
                    class="my-4 space-y-4"
                    >
                    <x-forms.row
                        wire:model="client_name"
                        errorName="client_name"
                        name="client_name"
                        text="Client Name"
                        disabled
                        >
                    </x-forms.row>
                </div>


                <div
                    x-data="{ open: @entangle('client_name'), address: @entangle('form.address')}"
                    x-show="!open && !address"
                    x-transition
                    class="my-4 space-y-4"
                    >
                    <x-forms.row
                        wire:model.live="user_client_id"
                        errorName="user_client_id"
                        name="user_client_id"
                        text="Existing Clients"
                        type="radiogroup"
                        :data="[
                            'wire_model' => $user_clients,
                            'radio_details_left' => [
                                'title' => 'name',
                                'desc' => 'address',
                                ],
                            'radio_details_right' => [
                                'title' => '',
                                'desc' => '',
                                ]
                            ]"
                        >
                    </x-forms.row>

                    <x-forms.row
                        wire:click="newClient"
                        errorName=""
                        name=""
                        {{-- //-:text="!is_null($user_clients) ? !$user_clients->isEmpty() ? '' : 'Existing Clients': ''" --}}
                        type="button"
                        buttonText="Create New Client"
                        >
                    </x-forms.row>
                </div>

                <div
                    {{-- open: @entangle('user_client_id'),  --}}
                    x-data="{open: @entangle('user_client_id'), address: @entangle('form.address')}"
                    {{-- open == 'NEW' ||  --}}
                    x-show="open == 'NEW' || address"
                    x-transition
                    class="my-4 space-y-4"
                    >

                    <x-forms.row
                        wire:model.live.debounce.500ms="form.business_name"
                        errorName="form.business_name"
                        placeholder="Business Name"
                        name="business_name"
                        text="Business Name"
                        >
                    </x-forms.row>

                    {{-- ADDRESS --}}
                    @include('components.forms._address_form')

                    <x-forms.row
                        wire:model.live.debounce.500ms="form.source"
                        errorName="form.source"
                        placeholder="Referral / Lead"
                        name="source"
                        text="Client Source"
                        >
                    </x-forms.row>
                </div>
            </x-cards.body>

            {{-- FOOTER --}}
            <x-cards.footer>
                <button
                    {{-- wire:click="$emitTo('clients.clients-form', 'resetModal')" --}}
                    type="button"
                    x-on:click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                {{-- SUBMIT BUTTON --}}
                <button
                    type="submit"
                    class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{$view_text['button_text']}}
                </button>
                {{-- <div
                    x-data="{ user_client_id: @entangle('user_client_id')}"
                    x-show="user_client_id"
                    x-transition
                    class="my-4 space-y-4"
                    >
                    <button
                        type="submit"
                        class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{$view_text['button_text']}}
                       // x-text="'open' = 'NEW' ? 'Create Client' : 'Show Client'"
                    </button>
                </div> --}}
                {{-- <div
                    x-data="{ open: @entangle('user_client_id')}"
                    x-show="open != 'NEW'"
                    x-transition
                    class="my-4 space-y-4"
                    >
                    <button
                        href="{{route('clients.show', open)}}"
                        target="_blank"
                        type="button"
                        class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Show Client
                    </button>
                </div> --}}
            </x-cards.footer>
        </x-cards.wrapper>
    </form>
</x-modals.modal>




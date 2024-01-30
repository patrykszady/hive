<div>
    <x-cards.wrapper class="w-full px-4 pb-5 mb-1 sm:px-6 lg:max-w-4xl lg:px-8">
        {{-- HEADER --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1 class="text-lg">Choose account to log into</h1>
                <p class="text-sm"><i>{{$user->first_name}}, select one of your accounts to see dashboard.</i></p>
            </x-slot>
        </x-cards.heading>

        {{-- BODY --}}
        <form wire:submit="save">
            <div class="justify-center p-6">
                {{-- 05-25-2022 this is a x-forms.row type="radio" ... change --}}
                <fieldset>
                    <legend class="sr-only">
                        User vendor selection
                    </legend>
                    <div
                        class="space-y-4"
                        x-data="{vendor_id: @entangle('vendor_id').live}"
                        >
                        <!--
                                    Checked: "border-transparent", Not Checked: "border-gray-300"
                                    Active: "ring-2 ring-indigo-500"
                                -->
                        @foreach ($vendors as $vendor)
                            <label
                                class="{{ $vendor_id == $vendor->id ? 'border-transparent ring-2 ring-indigo-500 ' : 'border-gray-300' }}
                                        relative block bg-white border rounded-lg shadow-sm px-6 py-4 cursor-pointer sm:flex sm:justify-between focus:outline-none hover:bg-gray-50"
                                >

                                <input type="radio" name="server-size" class="sr-only" x-model="vendor_id"
                                    value="{{$vendor->id}}" aria-labelledby="{{$vendor->id}}"
                                    aria-describedby="server-size-{{$vendor->id}}-description-0 server-size-{{$vendor->id}}-description-1">

                                <div class="flex items-center">
                                    <div class="text-sm">
                                        <div id="server-size-{{$vendor->id}}-description-10" class="text-gray-500">
                                            <p id="{{$vendor->id}}" class="font-medium text-gray-900 sm:inline">{{$vendor->business_name}}</p>
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
                                    class="flex mt-2 text-sm sm:mt-0 sm:block sm:ml-4 sm:text-right">
                                    <div class="font-medium text-gray-900">{{ $vendor->user_role }}</div>
                                    {{-- <div class="ml-1 text-gray-500 sm:ml-0">Vendor role</div> --}}
                                </div>
                                <!--
                                    Active: "border", Not Active: "border-2"
                                    Checked: "border-indigo-500", Not Checked: "border-transparent"
                                    -->
                                <div class="
                                            {{ $vendor_id == $vendor->id ? 'border-indigo-500 border' : 'border-transparent border-2' }}
                                            absolute -inset-px rounded-lg pointer-events-none" aria-hidden="true">
                                </div>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            {{-- FOOTER --}}
            <div x-data="{ open: @entangle('vendor_name') }" x-show="open" x-transition.duration.250ms>
                <x-cards.footer>
                    <button></button>
                    {{-- <button type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button> --}}
                    <button
                        x-transition.duration.250ms
                        {{-- x-text="$wire.vendor_name" --}}
                        type="submit"
                        class="inline-flex justify-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                        {{$vendor_name}}
                        {{-- {{ isset($vendor_id) ? 'Login to ' . $vendor->business_name : '' }} --}}
                    </button>
                </x-cards.footer>
            </div>
        </form>
    </x-cards.wrapper>

    <x-misc.hr :padding="''">
        <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path fill="#6B7280" fill-rule="evenodd"
                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                clip-rule="evenodd" />
        </svg>
    </x-misc.hr>
    <br>

    <x-cards.wrapper class="w-full px-4 pb-5 mb-1 sm:px-6 lg:max-w-4xl lg:px-8">
        <button
            x-data=""
            wire:click="$dispatchTo('vendors.vendor-create', 'vendorModal')"
            type="button"
            class="relative block w-full p-12 text-center border-2 border-indigo-400 rounded-lg hover:ring-3 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-800"
            >
            <svg class="w-12 h-12 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                fill="none" viewBox="0 0 48 48" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />
            </svg>
            <span class="block mt-2 text-sm font-medium text-gray-900">
                Create a Hive. <b>Contact Patryk to get started for free.</b> Cell: 224-999-3880 Email: patryk@hive.contractors
            </span>
        </button>
    </x-cards.wrapper>

    {{-- CREATE NEW VENDOR/BUSINESS --}}
    <livewire:vendors.vendor-create />
</div>

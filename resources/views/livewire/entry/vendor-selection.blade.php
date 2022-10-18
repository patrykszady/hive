<x-cards.wrapper class="max-w-4xl">
    {{-- HEADER --}}
    <x-cards.heading>
        <x-slot name="left">
            <h1 class="text-lg">Choose account to log into</h1>
            <p class="text-sm"><i>{{auth()->user()->first_name}}, select one of your accounts to see dashboard.</i></p>
        </x-slot>
        <x-slot name="right">
        </x-slot>
    </x-cards.heading>

    {{-- BODY --}}
    <form wire:submit.prevent="save">
        <div class="p-6 justify-center">
            {{-- 05-25-2022 this is a x-forms.row type="radio" ... change --}}
            <fieldset>
                <legend class="sr-only">
                    User vendor login
                </legend>
                <div 
                    class="space-y-4" 
                    x-data="{vendor_id: @entangle('vendor_id')}"
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
                                    {{ $vendor_id == $vendor->id ? 'border-indigo-500 border' : 'border-transparent border-2' }}
                                    absolute -inset-px rounded-lg pointer-events-none" aria-hidden="true">
                        </div>
                    </label>
                    @endforeach
                </div>
            </fieldset>
        </div>

        {{-- FOOTER --}}
        <div x-data="{ open: @entangle('vendor_id') }" x-show="open" x-transition.duration.150ms>
            <x-cards.footer>
                <button></button>
                {{-- <button type="button"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button> --}}
                <button 
                    x-transition
                    type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ isset($vendor_id) ? 'Login to ' . $vendor_name : '' }}
                </button>
            </x-cards.footer>
        </div>
    </form>
</x-cards.wrapper>

<br>
<x-misc.hr>
    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
        <path fill="#6B7280" fill-rule="evenodd"
            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
            clip-rule="evenodd" />
    </svg>
</x-misc.hr>
<br>

<x-cards.wrapper class="max-w-4xl">
    <button type="button"
        class="relative block w-full border-2 border-gray-300 border-dashed rounded-lg p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
            fill="none" viewBox="0 0 48 48" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />
        </svg>
        <span class="mt-2 block text-sm font-medium text-gray-900">
            Create a new account
        </span>
    </button>
</x-cards.wrapper>

{{-- CREATE NEW VENDOR/BUSINESS --}}
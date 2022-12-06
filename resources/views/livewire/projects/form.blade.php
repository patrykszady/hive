<x-modals.modal :class="'max-w-lg'">
    <form wire:submit.prevent="{{$view_text['form_submit']}}"> 
        <x-cards.heading>
            <x-slot name="left">
                    <h1>
                        {{$view_text['card_title']}}
                    </h1>
            </x-slot>
        
            <x-slot name="right">

            </x-slot>
        </x-cards.heading>
    
        <x-cards.body :class="'space-y-4 my-4'">
            {{-- CLIENT ID --}}
            <x-forms.row 
                wire:model.debounce.250ms="client.id" 
                errorName="client.id" 
                name="client.id" 
                text="Client"
                type="dropdown"
                :disabled="$client['type'] == 'Existing' ? true : false"
                >
                <option value="" readonly>Select Client</option>
                @foreach ($clients as $client)
                    <option value="{{$client->id}}">{{$client->name}}</option>
                @endforeach
            </x-forms.row>

            {{-- PROJECT NAME --}}
            <div 
                x-data="{ open: @entangle('client.id') }" 
                x-show="open" 
                x-transition.duration.150ms
                class="space-y-4 my-4"
                >
                <hr>

                <x-forms.row 
                    wire:model.debounce.500ms="project.project_name" 
                    errorName="project.project_name" 
                    name="project.project_name" 
                    text="Project Name"
                    >
                </x-forms.row>
            </div>

            {{-- client->projects->pluck_>address --}}
            {{-- ADDRESS --}}
            <div 
                x-data="{ open: @entangle('address') }" 
                x-show="open" 
                x-transition.duration.150ms
                class="space-y-4 my-4"
                >

                <x-forms.row 
                    wire:model.debounce.1000ms="client.id" 
                    errorName="client.id"
                    name="client.id"
                    text="Address"
                    type="radio"
                    >

                    <div 
                        class="space-y-4" 
                        {{-- x-data="{user_vendor_id: @entangle('client.address')}" --}}
                        >
                        <!--
                                    Checked: "border-transparent", Not Checked: "border-gray-300"
                                    Active: "ring-2 ring-indigo-500"
                                -->
                        {{-- Vendors where User is Admin --}}
                        <label
                            class="border-gray-300 relative block bg-white border rounded-lg shadow-sm px-6 py-4 cursor-pointer sm:flex sm:justify-between focus:outline-none hover:bg-gray-50"
                            >

                            <input 
                                type="radio" 
                                name="server-size" 
                                class="sr-only" 
                                x-model="client.address"
                                value="" 
                                aria-labelledby="0"
                                aria-describedby="server-size-0-description-0 server-size-0-description-1"
                                >

                            <div class="flex items-center">
                                <div class="text-sm">
                                    <div
                                        class="text-gray-500"
                                        id="server-size-0-description-10"
                                        >

                                        <p
                                            class="sm:inline font-medium text-gray-900"
                                            id="0"
                                            >
                                            {{-- {{$client->address}} --}}
                                        </p>
                                        <span 
                                            class="hidden sm:inline sm:mx-1" 
                                            aria-hidden="true"
                                            >
                                            &middot;                                        
                                        </span>
                                        <p class="sm:inline">
                                            {{-- {{$client->zip_code}} --}}
                                        </p>
                                    </div>
                                    <div
                                        class="text-gray-500" 
                                        id="server-size-1-description-0"
                                        >
                                        <p class="sm:inline">
                                            {{-- {{$client->address}} --}}
                                        </p>
                                        <span 
                                            class="hidden sm:inline sm:mx-1" 
                                            aria-hidden="true"
                                            >
                                            &middot;
                                        </span>
                                        <p class="sm:inline">
                                            {{-- {{$client->oneLineAddress}} --}}
                                        </p>
                                    </div>
                                </div>
                            </div>
{{-- 
                            <div 
                                class="mt-2 flex text-sm sm:mt-0 sm:block sm:ml-4 sm:text-right"
                                id="server-size-1-description-1"
                                >
                                <div class="font-medium text-gray-900">
                                    {{ $client->id }}
                                </div>
                                <div class="ml-1 text-gray-500 sm:ml-0">
                                    Vendor role
                                </div>
                            </div> --}}
                            <!--
                                Active: "border", Not Active: "border-2"
                                Checked: "border-indigo-500", Not Checked: "border-transparent"
                                -->
                            <div 
                                class="border-transparent border-2absolute -inset-px rounded-lg pointer-events-none"
                                aria-hidden="true"
                                >
                            </div>
                        </label>
                    </div>
                </x-forms.row>


  
                
                {{-- <x-forms.row 
                    wire:model.debounce.500ms="project.address" 
                    errorName="project.address" 
                    name="project.address" 
                    text="Address"
                    type="text"
                    placeholder="Street Address | 123 Main St" 
                    >
                </x-forms.row>

                <x-forms.row 
                    wire:model="project.address_2" 
                    errorName="project.address_2" 
                    name="project.address_2" 
                    text=""
                    type="text"
                    placeholder="Unit Number | Suite 106" 
                    >
                </x-forms.row>

                <x-forms.row 
                    wire:model.debounce.500ms="project.city" 
                    errorName="project.city" 
                    name="project.city" 
                    text=""
                    type="text"
                    placeholder="City | Arlington Heights" 
                    >
                </x-forms.row>

                <x-forms.row 
                    wire:model.debounce.500ms="project.state" 
                    errorName="project.state" 
                    name="project.state" 
                    text=""
                    type="text"
                    placeholder="State | IL"
                    maxlength="2"
                    minlength="2"
                    >
                </x-forms.row>

                <x-forms.row 
                    wire:model.debounce.500ms="project.zip_code" 
                    errorName="project.zip_code" 
                    name="project.zip_code" 
                    text=""
                    type="number"
                    placeholder="Zipcode | 60070"
                    maxlength="5"
                    minlength="5"
                    inputmode="numeric"
                    >
                </x-forms.row> --}}
            </div>
        </x-cards.body>

        <x-cards.footer>
            <button
                {{-- emit = Cancel and remove all data to default... --}}
                {{-- wire:click="$emit('resetModal')" --}}
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
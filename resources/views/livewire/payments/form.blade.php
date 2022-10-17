<form wire:submit.prevent="{{$view_text['form_submit']}}">
    <x-page.top
        h1="{!! $client->name !!} Payment"
        p="Add Payment for Client Projects"
        right_button_href="{{route('clients.show', $client->id)}}"
        right_button_text="Client"
        >
    </x-page.top>

    <div class="xl:relative max-w-xl lg:max-w-5xl grid grid-cols-5 gap-4 sm:px-6 mx-auto">
		<div class="col-span-5 lg:col-span-2 lg:h-32 lg:sticky lg:top-5 space-y-4">
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1>Client Payment</h1>
						<p class="text-gray-500"><i>Choose Projects to add in this Payment</i></p>
					</x-slot>
				</x-cards.heading>
		
				<x-cards.body :class="'space-y-2 my-2'">
                    {{-- FORM --}}
                    <x-forms.row 
                        wire:model.debounce.500ms="payment.date" 
                        errorName="payment.date" 
                        name="date" 
                        text="Date"
                        type="date"
                        >
                    </x-forms.row>

                    <x-forms.row 
                        wire:model.lazy="payment.invoice" 
                        name="invoice" 
                        errorName="payment.invoice"
                        text="Reference"
                        placeholder="Client Check #"
                        type="text"  
                        >
                    </x-forms.row>
                    <x-forms.row 
                        wire:model.lazy="payment.note" 
                        errorName="payment.note" 
                        name="note" 
                        text="Note" 
                        type="textarea"
                        rows="1" 
                        placeholder="Payment Notes.">
                    </x-forms.row>
				</x-cards.body>

                <x-cards.footer>
                    <div class="text-center space-y-1 w-full">
                        <a 
                            type="button"
                            class="text-center focus:outline-none w-full rounded-md border-2 border-indigo-600 py-2 px-4 text-lg font-medium text-gray-900 shadow-sm">
                           
                            Payment Total | <b> {{money($this->client_payment_sum)}}</b>                          
                        </a>
                        <button 
                            type="submit"
                            class="focus:outline-none mt-8 w-full rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{$view_text['button_text']}}                      
                        </button>
                    </div>
                </x-cards.footer>
			</x-cards.wrapper>
		</div>

		<div class="col-span-5 lg:col-span-3 space-y-2">
            {{-- CHOOSE PROJECT DIV --}}
			<x-cards.wrapper>
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Choose Payment Projects</h1>
                    </x-slot>
                </x-cards.heading>
            
                <x-cards.body :class="'space-y-2 my-2'">
                    <x-forms.row 
                        wire:model.debounce.150ms="project_id" 
                        errorName="project_id" 
                        name="project_id" 
                        text="Project"
                        type="dropdown"
                        >
        
                        <option value="" readonly>Select Project</option>
                        @foreach ($projects as $index => $project)
                            <option value="{{$project->id}}">{{$project->name}}</option>
                        @endforeach
                    </x-forms.row>

                    <x-forms.row
                        wire:click="$emit('addProject')"
                        type="button"
                        errorName="project_id_DONT_SHOW"                        
                        text=""
                        buttonText="Add Project"
                        >                       
                    </x-forms.row>                      
                </x-cards.body>
            </x-cards.wrapper>

            {{-- PAYMENT PROJECTS --}}
            @if($payment_projects)
            @foreach ($payment_projects as $index => $project)
                <x-cards.wrapper>       
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1>{{ $project['name'] }}</h1>
                        </x-slot>

                        <x-slot name="right">
                            {{-- 8/20/2022 x-cards.button --}}
                            <button 
                                type="button"
                                wire:click="$emit('removeProject', {{$project['id']}})"
                                x-transition.duration.150ms
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                Remove Project
                            </button>
                        </x-slot>
                    </x-cards.heading>

                    {{-- ROWS --}}
                    <x-cards.body :class="'space-y-2 my-2'">  
                        {{-- AMOUNT --}}
                        <x-forms.row 
                            wire:model.debounce.200ms="payment_projects.{{$index}}.amount" 
                            errorName="payment_projects.{{$index}}.amount" 
                            name="payment_projects.{{$index}}.amount"
                            text="Amount"
                            type="number"
                            hint="$" 
                            textSize="xl" 
                            placeholder="00.00"
                            inputmode="numeric" 
                            step="0.01"
                            autofocus
                            > 
                        </x-forms.row>
                    </x-cards.body>
                </x-cards.wrapper>
            @endforeach
            @endif

        </div>
    </div>
</form>



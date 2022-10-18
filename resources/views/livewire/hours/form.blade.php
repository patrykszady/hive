<form wire:submit.prevent="{{$view_text['form_submit']}}">
	<x-page.top
		h1="Daily Hours for {{auth()->user()->first_name}}"
		p="Daily Hours for {{auth()->user()->first_name}}"
		right_button_href="{{route('timesheets.index')}}"
		right_button_text="Show Timesheets"
		>
	</x-page.top>

	<div class="xl:relative max-w-xl lg:max-w-5xl grid grid-cols-4 gap-4 sm:px-6 mx-auto">
		{{-- FLOAT CALENDAR --}}
		<div class="col-span-4 lg:col-span-2 lg:h-32 lg:sticky lg:top-5 space-y-4">
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1>Daily Hours</h1>
						<p>Pick Date to add or edit Daily Hours for {{auth()->user()->first_name}}</p>
					</x-slot>
				</x-cards.heading>
		
				<x-cards.body>
					{{-- CALANDER --}}
					@include('livewire.hours._calander')
					
					<div class="m-4 text-center space-y-1">
						<a 
							type="button"
							class="text-center focus:outline-none mt-8 w-full rounded-md border-2 border-indigo-600 py-2 px-4 text-md font-medium text-gray-900   shadow-sm">
							{{$this->selected_date->format('D M jS, Y')}}
						</a>
						<a 
							type="button"
							class="text-center focus:outline-none mt-8 w-full rounded-md border-2 border-indigo-600 py-2 px-4 text-lg font-medium text-gray-900   shadow-sm">
							Hours | <b>{{$this->hours_count}}</b>
						</a>
						<button 
							type="submit"
							class="focus:outline-none mt-8 w-full rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
							{{$view_text['button_text']}}
						</button>
													{{-- <a 
							type="button"
							href="{{route('timesheets.index')}}"
							class="text-center focus:outline-none mt-8 w-full rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
							Confirm Weekly Timesheet
						</a> --}}
					</div>
				</x-cards.body>
			</x-cards.wrapper>
		</div>

		<div class="col-span-4 lg:col-span-2 space-y-2">
			@foreach ($projects as $index => $project)			
				<x-cards.wrapper>
					<x-cards.heading>
						<x-slot name="left">
							<h1>{{$project->name}}</h1>
						</x-slot>
			
						<x-slot name="right">
						</x-slot>
					</x-cards.heading>
					<x-cards.body :class="'space-y-2 my-2'">
						{{-- PROJECT HOUR AMOUNT --}}
						<x-forms.row 
							wire:model="hours.{{$index}}.amount" 
							errorName="hours.{{$index}}.amount" 
							name="hours.{{$index}}.amount"
							text="Hours"
							type="number"
							hint="Hours" 
							textSize="xl" 
							placeholder="1.00"
							inputmode="numeric" 
							step="0.25"
							> 
						</x-forms.row>						
					</x-cards.body>
				</x-cards.wrapper>
			@endforeach
		</div>
	</div>
</form>
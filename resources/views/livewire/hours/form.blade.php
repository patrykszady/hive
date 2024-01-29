<form wire:submit="{{$view_text['form_submit']}}">
	<x-page.top
		h1="Daily Hours for {{$first_name}}"
		p="Daily Hours for {{$first_name}}"
		right_button_href="{{route('timesheets.index')}}"
		right_button_text="Show Timesheets"
		>
	</x-page.top>

	<div class="grid max-w-xl grid-cols-4 gap-4 mx-auto xl:relative lg:max-w-5xl sm:px-6">
		{{-- FLOAT CALENDAR --}}
		<div class="col-span-4 space-y-4 lg:col-span-2 lg:h-32 lg:sticky lg:top-5">
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1>Daily Hours</h1>
						<p><i>Pick Date to add or edit Daily Hours for {{$first_name}}</i></p>
					</x-slot>
				</x-cards.heading>

				<x-cards.body>
					{{-- CALANDER --}}
					@include('livewire.hours._calander')

				</x-cards.body>

                <x-cards.footer>
					<div class="w-full space-y-1 text-center">
						<button
							type="button"
							class="w-full px-4 py-2 text-lg font-medium text-center text-gray-900 border-2 border-indigo-600 rounded-md shadow-sm focus:outline-none">
							{{$this->selected_date->format('D M jS, Y')}}
						</button>
						{{-- @if(!$days->where('format', $this->selected_date->format('Y-m-d'))->first()['confirmed_date']) --}}
                        <button
                            type="button"
                            class="w-full px-4 py-2 text-lg font-medium text-center text-gray-900 border-2 border-indigo-600 rounded-md shadow-sm focus:outline-none">
                            Hours | <b>{{$this->hours_count}}</b>
                        </button>

                        <x-forms.error errorName="hours_count"/>

                        <button
                            type="submit"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow focus:outline-none hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                            {{$view_text['button_text']}}
                        </button>
					</div>
                </x-cards.footer>
			</x-cards.wrapper>
		</div>

		<div class="col-span-4 space-y-2 lg:col-span-2">
			{{-- @if(!$days->where('format', $this->selected_date->format('Y-m-d'))->first()['confirmed_date']) --}}
				@foreach ($projects as $index => $project)
					<x-cards.wrapper>
						{{-- <x-cards.heading>
							<x-slot name="left">
								<h1>{{$project->name}}</h1>
							</x-slot>

							<x-slot name="right">
							</x-slot>
						</x-cards.heading> --}}
						<x-cards.body :class="'space-y-2 my-2'">
							{{-- PROJECT HOUR AMOUNT --}}
							<x-forms.row
								wire:model.live="form.projects.{{$index}}.hours"
								errorName="form.projects.{{$index}}.hours"
								name="form.projects.{{$index}}.hours"
								text="{{$project->name}}"
								type="number"
								hint="Hours"
								textSize="xl"
								placeholder="1.00"
								inputmode="decimal"
								step="0.25"
								>
							</x-forms.row>
						</x-cards.body>
					</x-cards.wrapper>
				@endforeach
			{{-- @else
				<x-cards.wrapper>
					<x-cards.heading>
						<x-slot name="left">
							<h1>Date Confirmed</h1>
						</x-slot>

						<x-slot name="right">
						</x-slot>
					</x-cards.heading>
					<x-cards.body :class="'space-y-2 my-2'">
						<div class="'space-y-2 my-2'">
							<p>This date has already been confirmed and converted into a Timesheet. See Timesheet for details.</p>
						</div>
					</x-cards.body>
				</x-cards.wrapper>
			@endif --}}
		</div>

        {{-- 3-25-2023 add another project --}}
	</div>
</form>

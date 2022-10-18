<div>
	<x-page.top
        h1="{!! $project->name !!}"
        p="{!! $project->client->name !!}"
		right_button_href="{{auth()->user()->can('update', $project) ? route('projects.show', $project->id) : ''}}"
        right_button_text="Edit Project"
        >
    </x-page.top>

	<div class="max-w-xl lg:max-w-5xl grid grid-cols-4 gap-4 sm:px-6 mx-auto">
		{{--  lg:h-32 lg:sticky lg:top-5 --}}
		<div class="col-span-4 lg:col-span-2">
			{{-- PROJECT DETAILS --}}
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1 class="text-lg">Project Details</b></h1>
					</x-slot>
					
					@can('update', $project)
						<x-slot name="right">
							<x-cards.button href="{{route('projects.show', $project->id)}}">
								Edit Project
							</x-cards.button>
						</x-slot>
					@endcan
				</x-cards.heading>
				<x-cards.body>
					<x-lists.ul>
						<x-lists.search_li
							:basic=true
							:line_title="'Project Client'"
							href="{{route('clients.show', $project->client)}}"
							:line_data="$project->client->name"
							{{-- :bubble_message="'Success'" --}}
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Project Name'"
							:line_data="$project->project_name"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Jobsite Address'"
							href="{{$project->getAddressMapURI()}}"
							:href_target="'blank'"							
							:line_data="$project->full_address"
							>
						</x-lists.search_li>
						
						@can('update', $project)
							<x-lists.search_li
								:basic=true
								:line_title="'Billing Address'"						
								:line_data="$project->client->full_address"
								>
							</x-lists.search_li>
						@endcan

							<x-lists.search_li
								:basic=true
								:line_title="'Project Status'"
								:form=true
								{{-- :line_data="$project->project_status" --}}
								>
								<x-slot name="select_form">
									<select
										wire:model="project_status"
										name="project_status"
										class="ml-auto rounded-md hover:bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 placeholder-gray-200"
										@disabled(auth()->user()->cannot('update', $project))
										>
										@include('livewire.projects._status_options')
									</select>

									@can('update', $project)
									{{-- component? --}}
										<button 
											type="button"
											wire:click="change_project_status"
											class="ml-3 justify-center text-lg py-1 px-1 border-2 border-indigo-600 shadow-sm font-medium rounded-md text-gray-900 hover:text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
											>
											Change
										</button>
									@endcan
								</x-slot>				
							</x-lists.search_li>
						
					</x-lists.ul>
				</x-cards.body>
			</x-cards.wrapper>

			<br>
			
			@livewire('expenses.expense-index', ['project' => $project->id, 'view' => 'projects.show'])
		</div>

		@can('update', $project)
		<div class="col-span-4 lg:col-span-2">
			{{-- PROJECT FINANCIALS --}}
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1 class="text-lg">Project Finances</b></h1>
					</x-slot>

					<x-slot name="right">
						<x-cards.button
							wire:click="$emitTo('bids.bids-form', 'addBids', {{$project}}, {{auth()->user()->vendor}})"
							>
							Edit Bid
						</x-cards.button>
					</x-slot>
				</x-cards.heading>
				<x-cards.body>
					<x-lists.ul>
						<x-lists.search_li
							:basic=true
							:line_title="'Estimate'"
							{{-- href="{{route('clients.show', $project->client)}}" --}}
							:line_data="money($project->bids()->where('vendor_id', auth()->user()->vendor->id)->where('type', 1)->sum('amount'))"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Change Order'"
							:line_data="money($project->bids()->where('vendor_id', auth()->user()->vendor->id)->where('type', 2)->sum('amount'))"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Reimbursements'"
							{{-- :href_target="'blank'"							 --}}
							:line_data="money($project->expenses()->where('reimbursment', 'Client')->sum('amount'))"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:bold=true
							{{-- make gray --}}
							:line_title="'TOTAL PROJECT'"						
							:line_data="money(
										$project->expenses()->where('reimbursment', 'Client')->sum('amount') +
										$project->bids()->where('type', 2)->sum('amount') +
										$project->bids()->where('type', 1)->sum('amount')
									)"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Expenses'"
							:line_data="money($project->expenses->sum('amount'))"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Timesheets'"
							:line_data="money($project->timesheets->sum('amount'))"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:bold=true
							{{-- make gray --}}
							:line_title="'TOTAL COST'"						
							:line_data="money(
										$project->timesheets->sum('amount') +
										$project->expenses->sum('amount')
									)"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Payments'"
							:line_data="money($project->payments->sum('amount'))"
							>
						</x-lists.search_li>
						
						@if($project->project_status->title == 'Complete')
							<x-lists.search_li
								:basic=true
								:bold=true
								{{-- make gray --}}
								:line_title="'PROFIT'"						
								:line_data="money(
											($project->expenses()->where('reimbursment', 'Client')->sum('amount') +
											$project->bids()->where('type', 2)->sum('amount') +
											$project->bids()->where('type', 1)->sum('amount')) - 

											($project->timesheets->sum('amount') +
											$project->expenses->sum('amount'))
										)"
								>
							</x-lists.search_li>
						@endif

						<x-lists.search_li
							:basic=true
							{{-- make gray --}}
							:line_title="'Balance'"						
							:line_data="money(											
											($project->expenses()->where('reimbursment', 'Client')->sum('amount') +
											$project->bids()->where('type', 2)->sum('amount') +
											$project->bids()->where('type', 1)->sum('amount')) -
											$project->payments->sum('amount')
										)"
							>
						</x-lists.search_li>
					</x-lists.ul>
				</x-cards.body>
			</x-cards.wrapper>

			<br>

			{{-- PROJECT PAYMENTS --}}
			@if(!$project->payments->isEmpty())
			<x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
				<x-cards.heading>
					<x-slot name="left">
						<h1 class="text-lg">Payments</b></h1>
					</x-slot>
				
					<x-slot name="right">
						<x-cards.button href="{{route('payments.create', $project->client->id)}}">
							Add Payment
						</x-cards.button>
					</x-slot>
				</x-cards.heading>
				<x-lists.ul>
					@foreach($project->payments()->orderBy('date', 'DESC')->get() as $payment)
						@php
							$line_details = [
								1 => [
									'text' => $payment->date->format('m/d/Y'),
									'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'					
									],
								// 2 => [
								// 	'text' => $transaction->bank_account->type,
								// 	'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z'
								// 	],
								3 => [
									'text' => $payment->reference,
									'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'					
									],
								];
						@endphp
			
						<x-lists.search_li
							href=""
							:line_details="$line_details"
							:line_title="money($payment->amount)"
							:bubble_message="'Payment'"
							>
						</x-lists.search_li>
					@endforeach
				</x-lists.ul>
			</x-cards.wrapper>
			@endif
		</div>
		@endcan
	</div>

	@livewire('bids.bids-form')
</div>


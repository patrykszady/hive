<div>
	<x-page.top
        h1="{!! $project->name !!}"
        p="{!! $project->client->name !!}"
		{{-- right_button_href="{{auth()->user()->can('update', $project) ? route('estimates.create', $project->id) : ''}}"
        right_button_text="Create Estimate" --}}
        >
    </x-page.top>

	<div class="grid max-w-xl grid-cols-4 gap-4 mx-auto lg:max-w-5xl sm:px-6">
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
										wire:model.live="project_status"
										name="project_status"
										class="ml-auto placeholder-gray-200 border-gray-300 rounded-md hover:bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500"
										@disabled(auth()->user()->cannot('update', $project))
										>
										@include('livewire.projects._status_options')
									</select>

									@can('update', $project)
									{{-- component? --}}
										<button
											type="button"
											wire:click="change_project_status"
											class="justify-center px-1 py-1 ml-3 text-lg font-medium text-gray-900 border-2 border-indigo-600 rounded-md shadow-sm hover:text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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

			@if(!$project->expenses->isEmpty())
				@livewire('expenses.expense-index', ['project' => $project->id, 'view' => 'projects.show'])
			@endif
		</div>

		@can('update', $project)
            <div class="col-span-4 lg:col-span-2">
                {{-- PROJECT ESTIMATES --}}
                <x-cards.wrapper>
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1 class="text-lg">Project Estimates</b></h1>
                        </x-slot>

                        <x-slot name="right">
                            <x-cards.button
                                href="{{route('estimates.create', $project->id)}}"
                                >
                                Create Estimate
                            </x-cards.button>
                        </x-slot>
                    </x-cards.heading>
                    <x-cards.body>
                        <x-lists.ul>
                            @foreach($project->estimates as $estimate)
                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Estimate ' . $estimate->id"
                                    href="{{route('estimates.show', $estimate->id)}}"
                                    :line_data="money($estimate->estimate_sections->sum('total'))"
                                    >
                                </x-lists.search_li>
                            @endforeach
                        </x-lists.ul>
                    </x-cards.body>
                </x-cards.wrapper>

                <br>

                @if(in_array($this->project->project_status->title, ['Active', 'Complete', 'VIEW ONLY']))
                    {{-- PROJECT FINANCIALS --}}
                    <x-cards.wrapper>
                        <x-cards.heading>
                            <x-slot name="left">
                                <h1 class="text-lg">Project Finances</b></h1>
                            </x-slot>

                            <x-slot name="right">
                                <x-cards.button
                                    wire:click="$dispatchTo('bids.bid-create', 'addBids', { project: {{$project->id}} })"
                                    >
                                    Edit Bid
                                </x-cards.button>
                            </x-slot>
                        </x-cards.heading>
                        <x-cards.body>
                            {{-- wire:loading should just target the Reimbursment search_li not the entire Proejct Finances wrapper--}}
                            <x-lists.ul
                                wire:target="print"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 text-opacity-40"
                                >
                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Estimate'"
                                    :line_data="money($project->finances['estimate'])"
                                    >
                                </x-lists.search_li>

                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Change Order'"
                                    :line_data="money($project->finances['change_orders'])"
                                    >
                                </x-lists.search_li>

                                <x-lists.search_li
                                    wire:click="print"
                                    :basic=true
                                    :line_title="'Reimbursements'"
                                    {{-- href="{{route('print_reimbursment', $project->id)}}" --}}
                                    {{-- :href_target="'blank'" --}}
                                    href="#"
                                    :line_data="money($project->finances['reimbursments'])"
                                    >
                                </x-lists.search_li>

                                <x-lists.search_li
                                    :basic=true
                                    :bold="TRUE"
                                    {{-- make gray --}}
                                    :line_title="'TOTAL PROJECT'"
                                    :line_data="money($project->finances['total_project'])"
                                    >
                                </x-lists.search_li>

                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Expenses'"
                                    :line_data="money($project->finances['expenses'])"
                                    >
                                </x-lists.search_li>

                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Timesheets'"
                                    :line_data="money($project->finances['timesheets'])"
                                    >
                                </x-lists.search_li>

                                <x-lists.search_li
                                    :basic=true
                                    :bold="TRUE"
                                    {{-- make gray --}}
                                    :line_title="'TOTAL COST'"
                                    :line_data="money($project->finances['total_cost'])"
                                    >
                                </x-lists.search_li>

                                <x-lists.search_li
                                    :basic=true
                                    :line_title="'Payments'"
                                    :line_data="money($project->finances['payments'])"
                                    >
                                </x-lists.search_li>

                                @if($project->project_status->title == 'Complete')
                                    <x-lists.search_li
                                        :basic=true
                                        :bold="TRUE"
                                        {{-- make gray --}}
                                        :line_title="'PROFIT'"
                                        :line_data="money($project->finances['profit'])"
                                        >
                                    </x-lists.search_li>
                                @endif

                                <x-lists.search_li
                                    :basic=true
                                    {{-- make gray --}}
                                    :line_title="'Balance'"
                                    :line_data="money($project->finances['balance'])"
                                    >
                                </x-lists.search_li>
                            </x-lists.ul>
                        </x-cards.body>
                    </x-cards.wrapper>

                    <br>

                    {{-- PROJECT DISTRIBUTIONS --}}
                    @if(!$this->project->distributions->isEmpty())
                        <x-cards.wrapper>
                            <x-cards.heading>
                                <x-slot name="left">
                                    <h1 class="text-lg">Project Distributions</b></h1>
                                </x-slot>
                            </x-cards.heading>
                            <x-cards.body>
                                <x-lists.ul>
                                    @foreach($this->project->distributions as $distribution)
                                        <x-lists.search_li
                                            :basic=true
                                            :line_title="$distribution->name"
                                            :line_data="money($distribution->pivot->amount) . ' | ' . $distribution->pivot->percent . '%'"
                                            >
                                        </x-lists.search_li>
                                    @endforeach
                                </x-lists.ul>
                            </x-cards.body>
                        </x-cards.wrapper>

                        <br>
                    @endif

                    {{-- PROJECT PAYMENTS --}}
                    <x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                        <x-cards.heading>
                            <x-slot name="left">
                                <h1 class="text-lg">Payments</b></h1>
                            </x-slot>

                            @can('create', App\Models\Payment::class)
                            <x-slot name="right">
                                {{-- 12-09-22 modal not page reload --}}
                                <x-cards.button href="{{route('payments.create', $project->client->id)}}">
                                    Add Payment
                                </x-cards.button>
                            </x-slot>
                            @endcan
                        </x-cards.heading>

                        @if(!$project->payments->isEmpty())
                            <x-lists.ul>
                                @foreach($project->payments()->orderBy('date', 'DESC')->get() as $payment)
                                    @php
                                        $line_details = [
                                            1 => [
                                                'text' => $payment->date->format('m/d/Y'),
                                                'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
                                                ],
                                            2 => [
                                                'text' => $payment->reference,
                                                'icon' => 'M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z'
                                                ],
                                            ];
                                    @endphp

                                    <x-lists.search_li
                                        href=""
                                        :line_details="$line_details"
                                        :line_title="money($payment->amount)"
                                        :bubble_message="$payment->transaction ? 'Complete' : 'No Transaction'"
                                        :bubble_color="$payment->transaction ? 'green' : 'red'"
                                        >
                                    </x-lists.search_li>
                                @endforeach
                            </x-lists.ul>
                        @endif
                    </x-cards.wrapper>

                    <br>
                @endif
            </div>
		@endcan
	</div>

    <livewire:bids.bid-create :project="$project" :vendor="auth()->user()->vendor"/>
</div>


<div>
	<x-page.top
        h1="{!! $client->name !!}"
        p="Client"
        {{-- right_button_href="{{route('clients.edit', $client->id)}}" --}}
        {{-- right_button_href="#"
        right_button_text="Edit Client" --}}
        >
    </x-page.top>

    <div class="grid max-w-xl grid-cols-4 gap-4 mx-auto lg:max-w-5xl sm:px-6">
		{{--  lg:h-32 lg:sticky lg:top-5 --}}
		<div class="col-span-4 lg:col-span-2">
			{{-- CLIENT DETAILS --}}
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1 class="text-lg">Client Details</b></h1>
					</x-slot>

                    @can('update', $client)
                        <x-slot name="right">
                            <x-cards.button
                                wire:click="$dispatchTo('clients.client-create', 'editClient', { client: {{$client->id}}})"
                                >
                                Edit Client
                            </x-cards.button>
                        </x-slot>
                    @endcan
				</x-cards.heading>

				<x-cards.body>
					<x-lists.ul>
						<x-lists.search_li
							:basic=true
							:line_title="'Client Name'"
							{{-- href="{{route('clients.show', $project->client)}}" --}}
							:line_data="$client->name"
							>
						</x-lists.search_li>

                        @can('update', $client)
                            <x-lists.search_li
                                :basic=true
                                :line_title="'Billing Address'"
                                :line_data="$client->full_address"
                                >
                            </x-lists.search_li>
                        @endcan
                        <x-lists.search_li
                            :basic=true
                            :line_title="'Client Source'"
                            :line_data="$client->source"
                            >
                        </x-lists.search_li>
					</x-lists.ul>
				</x-cards.body>
			</x-cards.wrapper>
		</div>

        <div class="col-span-4 lg:col-span-2">
			{{-- CLIENT USERS --}}
			<x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>Client Members</h1>
                    </x-slot>

                    <x-slot name="right">
                        @can('create_client_member', [App\Models\User::class, $client])
                            <x-cards.button
                                wire:click="$dispatchTo('users.user-create', 'newMember', { model: 'client', model_id: {{$client->id}} })"
                                >
                                Add Client Member
                            </x-cards.button>
                        @endcan
                    </x-slot>
                </x-cards.heading>

                <x-lists.ul>
                    @foreach($users as $user)
                        @php
                            $line_details = [
                                // 1 => [
                                //     'text' => 'Vendor role',
                                //     'icon' => 'M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z'
                                //     ],
                            ];
                        @endphp

                        <x-lists.search_li
                            href="{{route('users.show',  $user->id)}}"
                            :line_details="$line_details"
                            :line_title="$user->full_name"
                            :bubble_message="'Client User'"
                            >
                        </x-lists.search_li>

                        {{-- 2-7-2022 ..only render when clicked above... --}}
                        {{-- @livewire('users.users-show', ['user' => $user]) --}}
                    @endforeach
                </x-lists.ul>
			</x-cards.wrapper>
		</div>

        {{-- CLIENT PROJECT --}}
        {{-- show only Client Projects... --}}
        {{-- @if(!$client->projects->isEmpty()) --}}
            <div class="col-span-4 lg:col-span-3">
                <livewire:projects.projects-index :client_id="$client->id" :view="true" />
                {{-- @livewire('expenses.expense-index', ['project' => $project->id]) --}}
            </div>
        {{-- @endif --}}
	</div>

    <livewire:users.user-create />
    <livewire:clients.client-create />
</div>

{{-- NEW PROJECT MODAL --}}
{{-- @livewire('projects.projects-form', ['client_id' => $client->id]) --}}

<div>
	<div
		class="max-w-3xl mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-7xl lg:px-8">
		<div class="flex items-center space-x-5">
			{{-- <div class="flex-shrink-0">
				<div class="relative">
					<img class="h-16 w-16 rounded-full"
						src="https://images.unsplash.com/photo-1463453091185-61582044d556?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=1024&h=1024&q=80"
						alt="">
					<span class="absolute inset-0 shadow-inner rounded-full" aria-hidden="true"></span>
				</div>
			</div> --}}
			<div>
				<h1 class="text-2xl font-bold text-gray-900">{{$user->vendor->business_name}}</h1>
				<p class="text-sm font-medium text-gray-500">
					{{$user->full_name}}'s dashboard for {{$user->vendor->business_name}} 
				</p>
			</div>
		</div>
		<div
			class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-reverse sm:space-y-0 sm:space-x-3 md:mt-0 md:flex-row md:space-x-3">

			<x-cards.button href="{{route('expenses.edit', $user->id)}}">
				Edit User
			</x-cards.button>
			{{-- <button type="button"
				class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-blue-500">Advance
				to offer</button> --}}
		</div>
	</div>
    <div class="mt-8 max-w-3xl mx-auto grid grid-cols-1 gap-6 sm:px-6 lg:max-w-7xl lg:grid-flow-col-dense lg:grid-cols-3">
        <div class="space-y-6 lg:col-start-1 lg:col-span-2">
            <!-- Description list-->
            <x-sections.section>
                <x-slot name="heading">
                    <h2 
                        id="applicant-information-title" 
                        class="text-lg leading-6 font-medium text-gray-900"
                        >
                        Expense
                    </h2>
                    <p 
                        class="mt-1 max-w-2xl text-sm text-gray-500"
                        >
                        Expense and related details like Expense Splits and Expense Receipts.
                    </p>
                </x-slot>
    
                {{-- MAIN SLOT --}}
                {{-- <x-sections.item :title="'Amount'" :details="money($expense->amount)"></x-sections.item>
                <x-sections.item :title="'Date'" :details="$expense->date->format('m/d/Y')"></x-sections.item>
                <x-sections.item :href="route('vendors.show', $expense->vendor_id)" :title="'Vendor'" :details="$expense->project->project_name"></x-sections.item>
                <x-sections.item :title="'Project'" :details="$expense->vendor->business_name"></x-sections.item> --}}
    
                {{-- @if($expense->note)
                    <x-sections.item class="sm:col-span-2" :title="'Note'" :details="$expense->note"></x-sections.item>
                @endif
    
                <x-slot name="footer">
                    <a 
                        href="{{route('expenses.edit', $expense->id)}}"
                        class="block bg-gray-50 text-sm font-medium text-gray-500 text-center px-4 py-4 hover:text-gray-700 sm:rounded-b-lg"
                        >
                        Edit Expense
                    </a>
                </x-slot> --}}
            </x-sections.section>
        </div>
    
        {{-- VENDOR TEAM MEMBERS --}}
        <x-cards.wrapper class="max-w-2xl">
            <x-cards.heading>
                <x-slot name="left">
                    {{-- attribute --}}
                    <h1>Team Members</h1>
                </x-slot>
            
                <x-slot name="right">
                    @can('create', App\Models\User::class)
                        <x-cards.button wire:click="$emit('newMember', 'vendor', {{$user->vendor->id}})">
                            Add team member
                        </x-cards.button>
                    @endcan        
            
                    @livewire('users.users-form')                
                </x-slot>
            </x-cards.heading>
            
            <x-lists.ul>
                @foreach($vendor_users as $user_vendor)
                {{-- @dd($user_vendor->vendor_role); --}}
                    @php
                        $line_details = [
                            // 1 => [
                            //     'text' => 'Vendor role',
                            //     'icon' => 'M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z'
                            //     ],                
                        ];
                    @endphp
            
                    <x-lists.search_li
                        wire:click="$emit('showMember', {{$user->id}})"
                        href="#"
                        :line_details="$line_details"
                        :line_title="$user_vendor->full_name"
                        {{-- $user->vendor->user_role --}}
                        :bubble_message="$user_vendor->vendor_role"
                        >
                    </x-lists.search_li>
            
                    {{-- 2-7-2022 ..only render when clicked above... --}}
                    @livewire('users.users-show', ['user' => $user])
                @endforeach
            </x-lists.ul>
        </x-cards.wrapper>
    </div>
</div>
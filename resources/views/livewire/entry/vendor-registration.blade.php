<div>
	<div
		class="max-w-3xl px-4 mx-auto sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-7xl lg:px-8">
		<div class="flex items-center space-x-5">
			{{-- <div class="flex-shrink-0">
				<div class="relative">
					<img class="w-16 h-16 rounded-full"
						src="https://images.unsplash.com/photo-1463453091185-61582044d556?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=1024&h=1024&q=80"
						alt="">
					<span class="absolute inset-0 rounded-full shadow-inner" aria-hidden="true"></span>
				</div>
			</div> --}}
			<div>
				<h1 class="text-2xl font-bold text-gray-900">Hive Registration for {{$user->vendor->business_name}}</h1>
				<p class="text-sm font-medium text-gray-500">
					Registration for <b>{{$user->vendor->name}} | {{$user->vendor->business_type}}</b> Vendor.
				</p>
			</div>
		</div>
		<div
			class="flex flex-col-reverse mt-6 space-y-4 space-y-reverse justify-stretch sm:flex-row-reverse sm:justify-end sm:space-x-reverse sm:space-y-0 sm:space-x-3 md:mt-0 md:flex-row md:space-x-3">

			{{-- <x-cards.button href="{{route('expenses.edit', $user->id)}}">
				Edit User
			</x-cards.button> --}}
			{{-- <button type="button"
				class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-blue-500">Advance
				to offer</button> --}}
		</div>
	</div>

    <div class="grid max-w-3xl grid-cols-1 gap-6 mx-auto mt-8 sm:px-6 lg:max-w-7xl lg:grid-cols-5">
        <div class="space-y-4 lg:col-start-1 lg:col-span-2">
            {{-- PROGRESS --}}
            <x-sections.section cols="1" class="sticky top-5">
                <x-slot name="heading">
                    <h2
                        id="applicant-information-title"
                        class="text-lg font-medium leading-6 text-gray-900"
                        >
                        Progress
                    </h2>
                    <p
                        class="max-w-2xl mt-1 text-sm text-gray-500"
                        >
                        Registration Progress
                    </p>
                </x-slot>

                {{-- MAIN SLOT --}}
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        <li>
                        <div class="relative pb-8">
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="flex items-center justify-center w-8 h-8 bg-green-500 rounded-full ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="{{$icons['user']}}" clip-rule="evenodd" />
                                    </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-500">Owner <a href="#" class="font-medium text-gray-900">{{$user->full_name}}</a> registration</p>
                                    </div>
                                    {{-- <div class="text-sm text-right text-gray-500 whitespace-nowrap">
                                        <time datetime="2020-09-20">Sep 20</time>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                        </li>

                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                <div>
                                    <span class="flex items-center justify-center w-8 h-8 {{$this->registration['vendor_info'] === false && $this->registration['team_members'] === false ? 'bg-blue-500' : ($this->registration['vendor_info'] === true ? 'bg-green-500' : 'bg-gray-500')}} rounded-full ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="{{$icons['vendor']}}"/>
                                    </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                    <p class="text-sm text-gray-500">Confirm <a href="#" class="font-medium text-gray-900">{{$user->vendor->name}}</a> details</p>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                <div>
                                    <span class="flex items-center justify-center w-8 h-8 {{$this->registration['team_members'] === false && $this->registration['vendor_info'] === false ? 'bg-gray-500' : ($this->registration['vendor_info'] === true && $this->registration['team_members'] === true ? 'bg-green-500' : 'bg-blue-500')}} rounded-full ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="{{$icons['user_add']}}" clip-rule="evenodd" />
                                    </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                    <p class="text-sm text-gray-500">Add <a href="#" class="font-medium text-gray-900">Team Members</a></p>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                <div>
                                    <span class="flex items-center justify-center w-8 h-8 {{$this->registration['team_members'] === false && $this->registration['vendor_info'] === false ? 'bg-gray-500' : ($this->registration['vendor_info'] === true && $this->registration['team_members'] === true ? 'bg-green-500' : 'bg-blue-500')}} rounded-full ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="{{$icons['distributions']}}" clip-rule="evenodd" />
                                    </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                    <p class="text-sm text-gray-500">Add <a href="#" class="font-medium text-gray-900">Distributions</a></p>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <div class="relative pb-8">
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <div class="relative flex space-x-3">
                                <div>
                                    <span class="flex items-center justify-center w-8 h-8 {{$this->registration['emails_registered'] === false && $this->registration['team_members'] === false ? 'bg-gray-500' : ($this->registration['team_members'] === true && $this->registration['emails_registered'] === true ? 'bg-green-500' : 'bg-blue-500')}} rounded-full ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="{{$icons['email']}}" />
                                    </svg>
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                    <p class="text-sm text-gray-500">Add Receipt <a href="#" class="font-medium text-gray-900">Emails</a></p>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </li>

                        <li>
                        <div class="relative pb-8">
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            <div class="relative flex space-x-3">
                            <div>
                                <span class="flex items-center justify-center w-8 h-8 {{$this->registration['banks_registered'] === false && $this->registration['emails_registered'] === false ? 'bg-gray-500' : ($this->registration['emails_registered'] === true && $this->registration['banks_registered'] === true ? 'bg-green-500' : 'bg-blue-500')}} rounded-full ring-8 ring-white">
                                <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="{{$icons['credit_card']}}" />
                                </svg>
                                </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                <div>
                                <p class="text-sm text-gray-500">Add Company <a href="#" class="font-medium text-gray-900">Transaction</a> Accounts</p>
                                </div>
                            </div>
                            </div>
                        </div>
                        </li>

                        <li>
                        <div class="relative pb-8">
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="flex items-center justify-center w-8 h-8 {{$this->registration['registered'] === false && $this->registration['banks_registered'] === false ? 'bg-gray-500' : ($this->registration['banks_registered'] === true && $this->registration['registered'] === true ? 'bg-green-500' : 'bg-blue-500')}} rounded-full ring-8 ring-white">
                                    <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="{{$icons['checkmark']}}" />
                                    </svg>
                                    </span>
                                </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                <div>
                                <p class="text-sm text-gray-500"><a href="#" class="font-medium text-gray-900">{{$user->vendor->name}}</a> registration complete</p>
                                </div>
                            </div>
                            </div>
                        </div>
                        </li>
                    </ul>
                </div>
            </x-sections.section>
        </div>

        {{-- REGISTRATION ITEMS --}}
        <div class="space-y-4 lg:col-start-3 lg:col-span-3 xl:col-span-2">
            {{-- VENDOR DETAILS --}}
            <x-cards.wrapper>
				<x-cards.body>
					<livewire:vendors.vendor-details :vendor="$vendor" :registration="TRUE">
				</x-cards.body>
			</x-cards.wrapper>

            <div
                x-data="{ showMembers: @entangle('registration.vendor_info') }"
                x-show="showMembers"
                x-transition.duration.250ms
                >

                {{-- VENDOR TEAM MEMBERS --}}
                <x-cards.wrapper>
                    <x-cards.body>
                        <livewire:users.team-members :vendor="$vendor" :registration="TRUE">
                    </x-cards.body>
                </x-cards.wrapper>

                {{-- VENDOR COMPANY EMAILS --}}
                {{-- DISTRIBUTION LIST --}}
                <div
                    x-data="{ showEmails: @entangle('registration.team_members') }"
                    x-show="showEmails"
                    x-transition.duration.250ms
                    >

                    <div class="pt-4">
                        <livewire:distributions.distributions-list />
                    </div>
                    <livewire:company-emails.company-emails-index :view="'vendor-registration'">
                </div>

                {{-- VENDOR BANKS --}}
                <div
                    x-data="{ showBanks: @entangle('registration.emails_registered') }"
                    x-show="showBanks"
                    x-transition.duration.250ms
                    >
                    <div class="pt-4">
                        <livewire:banks.bank-index :view="'vendor-registration'">
                    </div>
                </div>

                {{-- VENDOR REGISTRATION FORM --}}
                <div
                    x-data="{ showRegister: @entangle('registration.banks_registered') }"
                    x-show="showRegister"
                    x-transition.duration.250ms
                    >
                    <form wire:submit="store" x-show="showRegister">
                        <button
                            x-show="showRegister"
                            type="sbumit"
                            {{-- x-bind:disabled="store" --}}
                            {{-- wire:click="showmodal" --}}
                            wire:loading.attr="disabled"
                            {{-- wire:target="{{$view_text['form_submit']}}, 'expense', 'createExpenseFromTransaction'" --}}
                            {{-- x-bind:disabled="expense.project_id" --}}
                            class="inline-flex justify-center w-full px-4 py-2 text-lg font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                            Register {{$user->vendor->business_name}}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- REGISTER OVERLAY --}}
        <div
            {{-- x-data="{open: @entangle('modal_show').live}"
            x-show="open" --}}
            wire:loading
            wire:target="store"
            class="z-40 flex justify-center"
            >

            <!-- Overlay -->
            <div
                {{-- x-show="open"  --}}
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50"
                >
            </div>

            <!-- Modal -->
            <div
                {{-- x-show="open" --}}
                {{-- style="display: none" --}}
                {{-- x-on:keydown.escape.prevent.stop="open = false" --}}
                role="dialog"
                aria-modal="true"
                {{-- x-id="['modaltitle{{ Str::random() }}']" --}}
                {{-- :aria-labelledby="$id(title)" --}}
                class="fixed inset-0 overflow-y-auto"
                >

                <!-- Panel -->
                <div
                    {{-- x-show="open" --}}
                    x-transition
                    {{-- x-on:click="open = false" --}}
                    class="relative flex items-center justify-center min-h-screen p-4"
                    >
                    <button type="button" class="inline-flex items-center px-4 py-2 text-sm font-semibold leading-6 text-white transition duration-150 ease-in-out bg-indigo-800 rounded-md shadow hover:bg-indigo-600" disabled="">
                        <svg class="w-10 h-10 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <h1>Registering {{$user->vendor->business_name}} ...</h1>
                            <span class="font-bold">Do Not Exit!</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <livewire:vendors.vendor-create />
</div>

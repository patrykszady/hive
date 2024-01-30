<div>
    <form wire:submit="{{$view_text['form_submit']}}">
        <x-page.top
            h1="{{$vendor->business_name}} Payment"
            p="Vendor Payment for {{$vendor->business_name}}"
            right_button_href="{{route('vendors.show', $vendor->id)}}"
            right_button_text="Vendor"
            >
        </x-page.top>

        <div class="grid max-w-xl grid-cols-5 gap-4 mx-auto xl:relative lg:max-w-5xl sm:px-6">
            <div class="col-span-5 space-y-4 lg:col-span-2 lg:h-32 lg:sticky lg:top-5">
                <x-cards.wrapper>
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1>Vendor Payment</h1>
                            <p class="text-gray-500"><i>Choose Projects to add for {{$vendor->business_name}} in this Payment</i></p>
                        </x-slot>
                    </x-cards.heading>

                    <x-cards.body :class="'space-y-2 my-2'">
                        {{-- FORM --}}
                        @include('livewire.checks._payment_form')
                    </x-cards.body>

                    <x-cards.footer>
                        <div class="w-full space-y-1 text-center">
                            <button
                                type="button"
                                class="w-full px-4 py-2 font-medium text-center text-gray-900 border-2 border-indigo-600 rounded-md shadow-sm focus:outline-none text-md cursor-text">
                                Check Total | <b>{{money($this->vendor_check_sum)}}</b>
                            </button>

                            <x-forms.error errorName="check_total_min" />

                            <button
                                type="submit"
                                class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow focus:outline-none hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                {{$view_text['button_text']}}
                            </button>
                        </div>
                    </x-cards.footer>
                </x-cards.wrapper>

                {{-- INSURANCE --}}
                {{-- @include('livewire.vendors._insurance') --}}
            </div>
            <div class="col-span-5 space-y-2 lg:col-span-3">
                {{-- SELECT PROJECT --}}
                <x-cards.wrapper>
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1>Choose Payment Projects</h1>
                        </x-slot>
                    </x-cards.heading>

                    <x-cards.body :class="'space-y-2 my-2'">
                        <x-forms.row
                            wire:model.live="form.project_id"
                            errorName="form.project_id"
                            name="project_id"
                            text="Project"
                            type="dropdown"
                            >

                            <option value="" readonly>Select Project</option>
                            @foreach ($projects->where('show', false) as $project)
                                <option value="{{$project->id}}">{{$project->name}}</option>
                            @endforeach
                        </x-forms.row>

                        <x-forms.row
                            wire:click="$dispatch('addProject')"
                            type="button"
                            errorName="project_id_DONT_SHOW"
                            text=""
                            buttonText="Add Project"
                            >
                        </x-forms.row>
                    </x-cards.body>
                </x-cards.wrapper>

                {{-- PAYMENT PROJECTS --}}
                {{-- ->sortByDesc('show_timestamp') --}}
                @foreach($projects->where('show', true) as $project_id => $project)
                    <x-cards.wrapper>
                        <x-cards.heading>
                            <x-slot name="left">
                                <h1>{{ $project->name }}</h1>
                            </x-slot>

                            <x-slot name="right">
                                <x-cards.button
                                    {{-- , {{$project['id']}}, {{$vendor->id}} --}}
                                    {{-- , { project: {{$project->id}} --}}
                                    wire:click="$dispatchTo('bids.bid-create', 'addBids', { project: {{$project->id}} })"
                                    {{-- name="add"
                                    id="add{{$project_id}}" --}}
                                    >
                                    Edit Bid
                                </x-cards.button>

                                {{-- 8/20/2022 x-cards.button --}}
                                <button
                                    type="button"
                                    wire:click="$dispatch('removeProject', { project_id_to_remove: {{$project->id}} })"
                                    x-transition
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                    Remove Project
                                </button>
                            </x-slot>
                        </x-cards.heading>

                        {{-- ROWS --}}
                        <x-cards.body :class="'space-y-2 my-2'">
                            {{-- VENDOR BIDS --}}
                            <x-forms.row
                                wire:model.live="projects.{{$project_id}}.vendor_bids_sum"
                                errorName="projects.{{$project_id}}.vendor_bids_sum"
                                name="projects.{{$project_id}}.vendor_bids_sum"
                                text="Total Bids"
                                type="number"
                                hint="$"
                                disabled
                                >
                            </x-forms.row>

                            {{-- VENDOR PROJECT SUM --}}
                            <x-forms.row
                                {{-- 09-05-2023 how to format wire:model.live --}}
                                wire:model.live="projects.{{$project_id}}.vendor_expenses_sum"
                                errorName="projects.{{$project_id}}.vendor_expenses_sum"
                                name="projects.{{$project_id}}.vendor_expenses_sum"
                                text="Total Paid"
                                type="number"
                                hint="$"
                                disabled
                                >
                            </x-forms.row>

                            {{-- AMOUNT --}}
                            <x-forms.row
                                wire:model.live.debounce.500ms="projects.{{$project_id}}.amount"
                                errorName="projects.{{$project_id}}.amount"
                                name="projects.{{$project_id}}.amount"
                                {{-- x-text="money(payment_projects.{{$index}}.amount)" --}}
                                text="Amount"
                                type="number"
                                hint="$"
                                textSize="xl"
                                placeholder="00.00"
                                inputmode="decimal"
                                step="0.01"
                                pattern="[0-9]*"
                                autofocus
                                >
                            </x-forms.row>

                            {{-- VENDOR PROJECT BALANCE --}}
                            <x-forms.row
                                wire:model.live="projects.{{$project_id}}.balance"
                                errorName="projects.{{$project_id}}.balance"
                                name="projects.{{$project_id}}.balance"
                                text="Balance"
                                type="number"
                                hint="$"
                                disabled
                                >
                            </x-forms.row>
                            {{-- total paid, bid, balance rows DISABLED --}}
                        </x-cards.body>
                    </x-cards.wrapper>
                    {{--  :project="$project" --}}
                @endforeach
            </div>
        </div>
    </form>

    {{-- @livewire('bids.bids-form') --}}
    {{-- @livewire('vendor-docs.vendor-docs-form') --}}
    {{-- :projects="$projects" :distributions="$distributions" --}}
    {{-- <livewire:bids.bid-create /> --}}
    <livewire:bids.bid-create :vendor="$vendor"/>
</div>


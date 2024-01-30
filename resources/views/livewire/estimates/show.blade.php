<div>
	<x-page.top
        h1="Estimate for {!! $estimate->project->client->name !!}"
        p=""
        {{-- right_button_href="{{auth()->user()->can('update', $vendor) ? route('vendors.show', $vendor->id) : ''}}"
        right_button_text="Edit Vendor" --}}
        >
    </x-page.top>

	<div class="grid max-w-3xl grid-cols-4 gap-4 mx-auto lg:max-w-5xl sm:px-6">
		<div class="col-span-4 lg:col-span-2">
			{{-- ESTIMATE DETAILS --}}
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1 class="text-lg">Estimate Details</h1>
					</x-slot>
                    <x-slot name="right">
                        <div
                            x-data="{expanded_menu: false}"
                            class="inline-flex rounded-md shadow-sm">
                            <button
                                type="button"
                                disabled
                                class="relative inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-500 cursor-none bg-gray-50 rounded-l-md ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-10"
                                >
                                Options
                            </button>
                            <div class="relative block -ml-px">
                                <button
                                    type="button"
                                    x-on:click="expanded_menu = !expanded_menu"
                                    x-on:click.away="expanded_menu = false"
                                    class="relative inline-flex items-center px-2 py-2 text-gray-400 bg-white rounded-r-md ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20"
                                    id="option-menu-button"
                                    aria-expanded="true"
                                    aria-haspopup="true"
                                    >
                                    <span class="sr-only">Open options</span>
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <!--
                                Dropdown menu, show/hide based on menu state.

                                Entering: "transition ease-out duration-100"
                                    From: "transform opacity-0 scale-95"
                                    To: "transform opacity-100 scale-100"
                                Leaving: "transition ease-in duration-75"
                                    From: "transform opacity-100 scale-100"
                                    To: "transform opacity-0 scale-95"
                                -->
                                <div
                                    class="absolute right-0 z-10 w-56 mt-2 -mr-1 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                    x-show="expanded_menu" x-transition
                                    role="menu"
                                    aria-orientation="vertical"
                                    aria-labelledby="option-menu-button"
                                    tabindex="-5"
                                    >
                                    <div class="py-1" role="none">
                                        <!-- Active: "bg-gray-100 text-gray-900", Not Active: "text-gray-700" -->
                                        {{-- <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-0">Save and schedule</a> --}}
                                        <a wire:click="$dispatchTo('estimates.estimate-accept', 'accept')" wire:loading.attr="disabled" wire:loading.class="opacity-50" class="block px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-3">Finalize Estimate</a>
                                        <a wire:click="$dispatchTo('estimates.estimate-duplicate', 'duplicateModal', { estimate: {{$estimate->id}} })" class="block px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-1">Duplicate Estimate</a>
                                        <a wire:click="print" wire:loading.attr="disabled" wire:loading.class="opacity-50" class="block px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-2">Export PDF</a>
                                        <a wire:click="delete" class="block px-4 py-2 text-sm text-red-700 cursor-pointer hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-2">Delete Estimate</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-slot>
				</x-cards.heading>

                <livewire:estimates.estimate-accept :estimate="$estimate"/>
                <livewire:estimates.estimate-duplicate />

				<x-cards.body>
					<x-lists.ul>
						<x-lists.search_li
							:basic=true
							:line_title="'Project Homeowner'"
                            href="{{route('clients.show', $estimate->project->client)}}"
							:line_data="$estimate->project->client->name"
							{{-- :bubble_message="'Success'" --}}
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Project Name'"
                            href="{{route('projects.show', $estimate->project->id)}}"
							:line_data="$estimate->project->project_name"
							>
						</x-lists.search_li>

						<x-lists.search_li
							:basic=true
							:line_title="'Jobsite Address'"
							href="{{$estimate->project->getAddressMapURI()}}"
							:href_target="'blank'"
							:line_data="$estimate->project->full_address"
							>
						</x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :line_title="'Estimate'"
                            :line_data="$estimate->number"
                            >
                        </x-lists.search_li>
					</x-lists.ul>
				</x-cards.body>
			</x-cards.wrapper>
		</div>

        <div class="col-span-4 space-y-4 lg:col-span-4">
            {{-- SECTIONS --}}
            @foreach($sections as $index => $section)
                <div x-data="{expanded_{{$index}}: false}">
                    <div class="bg-white rounded-lg shadow">
                        <x-cards.wrapper>
                            <x-cards.heading>
                                {{-- Section Name Update --}}
                                <x-slot name="left">
                                    <div
                                        class="flex max-w-md mx-auto gap-x-4"
                                        x-data="{ open: false }"
                                        >
                                        {{-- EXPAND BUTTON --}}
                                        <button
                                            x-on:click="expanded_{{$index}} = !expanded_{{$index}}"
                                            {{-- :aria-expanded="expanded_{{$index}}" --}}
                                            class="flex items-center justify-between"
                                            >

                                            <span x-show="expanded_{{$index}}" aria-hidden="true" class="ml-4">
                                                <svg x-transition.duration.250ms class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                                    aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                            <span x-show="!expanded_{{$index}}" aria-hidden="true" class="ml-4">
                                                <svg x-transition.duration.250ms class="w-5 h-5 ml-auto text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor"
                                                    aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </button>

                                        <label for="section" class="sr-only">Section Name</label>
                                        <input
                                            x-on:mousedown="open = true"
                                            x-on:click.away="open = false"
                                            x-bind:disabled="!expanded_{{$index}}"
                                            wire:model.blur="sections.{{$index}}.name"
                                            id="sections.{{$index}}.name"
                                            name="sections.{{$index}}.name"
                                            type="text"
                                            autocomplete="section"
                                            required
                                            class="flex-auto min-w-0 text-gray-900 border-0 rounded-md shadow-sm ring-1 ring-gray-300 focus:ring-2 ring-inset focus:ring-indigo sm:text-sm sm:leading-6"
                                            placeholder="Section Name"
                                        >

                                        <div
                                            x-data="{expanded_menu_{{$index}}: false}"
                                            x-show="expanded_{{$index}}" x-transition
                                            class="inline-flex rounded-md shadow-sm">
                                            <button
                                                wire:click="sectionUpdate({{$index}})"
                                                class="relative inline-flex items-center px-2 py-2 text-sm font-semibold text-gray-500 bg-white rounded-l-md ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-10"
                                                >
                                                Update Name
                                            </button>
                                            <div class="relative block -ml-px">
                                                <button
                                                    type="button"
                                                    x-on:click="expanded_menu_{{$index}} = !expanded_menu_{{$index}}"
                                                    x-on:click.away="expanded_menu_{{$index}} = false"
                                                    class="relative inline-flex items-center h-full px-2 py-2 text-gray-400 bg-white rounded-r-md ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                                    id="option-menu-button-{{$index}}"
                                                    aria-expanded="true"
                                                    aria-haspopup="true"
                                                    >
                                                    <span class="sr-only">Open options</span>
                                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>

                                                <!--
                                                Dropdown menu, show/hide based on menu state.

                                                Entering: "transition ease-out duration-100"
                                                    From: "transform opacity-0 scale-95"
                                                    To: "transform opacity-100 scale-100"
                                                Leaving: "transition ease-in duration-75"
                                                    From: "transform opacity-100 scale-100"
                                                    To: "transform opacity-0 scale-95"
                                                -->
                                                <div
                                                    class="absolute right-0 z-10 w-56 mt-2 -mr-1 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                                                    x-show="expanded_menu_{{$index}}" x-transition
                                                    role="menu"
                                                    aria-orientation="vertical"
                                                    aria-labelledby="option-menu-button"
                                                    tabindex="-5"
                                                    >
                                                    <div class="py-1" role="none">
                                                        <!-- Active: "bg-gray-100 text-gray-900", Not Active: "text-gray-700" -->
                                                        {{-- <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-0">Save and schedule</a> --}}
                                                        {{-- <a wire:click="$dispatchTo('estimates.estimate-accept', 'accept')" wire:loading.attr="disabled" wire:loading.class="opacity-50" class="block px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-{{$index}}-3">Update Section Name</a> --}}
                                                        <a wire:click="itemsRearrange" class="block px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-{{$index}}-3">{!! $items_rearrange == TRUE ? 'Finish Rearrange' : 'Rearrange Items' !!}</a>
                                                        <a wire:click="sectionDuplicate({{$section->id}})" class="block px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-{{$index}}-1">Duplicate Section</a>
                                                        <a wire:click="sectionRemove({{$section->id}})" class="block px-4 py-2 text-sm text-red-700 cursor-pointer hover:bg-gray-100" role="menuitem" tabindex="-1" id="option-menu-item-2">Delete Section</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </x-slot>

                                {{-- IF section is opened/not collapsed --}}
                                <x-slot name="right">
                                    <x-cards.button type="button" wire:click="$dispatchTo('line-items.estimate-line-item-create', 'addToEstimate', { section_id: {{$section->id}}, section_item_count: {{$estimate->estimate_line_items()->where('section_id', $section->id)->count()}} })">
                                        Add Item
                                    </x-cards.button>
                                </x-slot>
                            </x-cards.heading>
                            <div x-show="expanded_{{$index}}" x-collapse>
                                <x-cards.body>
                                    {{--  divide-y divide-gray-300 --}}
                                    <table class="min-w-full break-inside-auto">
                                        <thead class="text-gray-900 border-b border-gray-400">
                                            <tr>
                                                {{-- first th --}}
                                                <th
                                                    scope="col"
                                                    class="hidden px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:table-cell"
                                                    >
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-3/4 sm:w-1/2"
                                                    >
                                                    Item
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="hidden px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:table-cell"
                                                    >
                                                    Quantity
                                                </th>
                                                <th
                                                    scope="col"
                                                    class="hidden px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:table-cell"
                                                    >
                                                    Unit
                                                </th>
                                                <th scope="col"
                                                    class="hidden px-3 py-3.5 text-right text-sm font-semibold text-gray-900 sm:table-cell"
                                                    >
                                                    Cost
                                                </th>
                                                {{-- last th --}}
                                                <th
                                                    scope="col"
                                                    class="py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900 sm:pr-6"
                                                    >
                                                    Total
                                                </th>
                                            </tr>
                                        </thead>

                                        {{-- @if($items_rearrange == TRUE) wire:sortable="itemsRearrangeOrder" @endif --}}
                                        {{--  @else wire:sortable.disabled="itemsRearrangeOrder" --}}
                                        {{-- {!! $items_rearrange == TRUE ? 'wire:sortable="itemsRearrangeOrder"' : '' !!} --}}
                                        <tbody wire:sortable="itemsRearrangeOrder">
                                            {{-- line_items where section is this section_id... --}}
                                            @foreach($estimate->estimate_line_items()->where('section_id', $section->id)->orderBy('section_index', 'ASC')->get() as $key => $estimate_line_item)
                                                <tr
                                                    class="border-b border-gray-400 hover:bg-gray-100 break-inside-auto break-after-auto"
                                                    wire:sortable.item="{{ $estimate_line_item->id }}"
                                                    wire:key="item-{{ $estimate_line_item->id }}"
                                                    wire:target="itemsRearrangeOrder"
                                                    wire:loading.attr="disabled"
                                                    wire:loading.class="opacity-50"
                                                    >
                                                    {{-- {!! $items_rearrange == TRUE ? "wire:sortable.handle" : '' !!} --}}
                                                    <td class="hidden px-3 py-5 text-right text-gray-500 align-text-top text-md sm:table-cell bg-gray-50">{{$index + 1}}.{{$estimate_line_item->section_index}}</td>

                                                    <td class="pl-4 pr-3 text-md max-w-0 sm:pl-6 bg-gray-50">
                                                        <a
                                                            class="cursor-pointer"
                                                            wire:click="$dispatchTo('line-items.estimate-line-item-create', 'editOnEstimate', { estimate_line_item_id: {{$estimate_line_item}}, section_id: {{$section->id}} })"
                                                            >
                                                            <div class="text-lg font-medium text-gray-900">{{$estimate_line_item->name}}</div>
                                                            <div class="text-xs font-bold text-indigo-900">{{$estimate_line_item->category}}/{{$estimate_line_item->sub_category}}</div>
                                                        </a>
                                                    </td>

                                                    <td class="hidden px-3 py-5 text-right text-gray-500 align-text-top text-md sm:table-cell bg-gray-50">{{$estimate_line_item->unit_type !== 'no_unit' ? $estimate_line_item->quantity : ''}}</td>
                                                    <td class="hidden px-3 py-5 text-right text-gray-500 align-text-top text-md sm:table-cell bg-gray-50">{{$estimate_line_item->unit_type !== 'no_unit' ? $estimate_line_item->unit_type : ''}}</td>
                                                    <td class="hidden px-3 py-5 text-right text-gray-500 align-text-top text-md sm:table-cell bg-gray-50">{{$estimate_line_item->unit_type !== 'no_unit' ? money($estimate_line_item->cost) : ''}}</td>
                                                    {{-- last td --}}
                                                    <td class="py-5 pl-3 pr-4 text-right text-gray-800 align-text-top text-md sm:pr-6 bg-gray-50">{{money($estimate_line_item->total)}}</td>
                                                </tr>

                                                {{-- remove if sorting --}}
                                                <tr
                                                    x-data="{ items_rearrange: @entangle('items_rearrange') }"
                                                    x-show="!items_rearrange"
                                                    x-transition
                                                    class="border-b border-gray-400"
                                                    >
                                                    {{-- first td --}}
                                                    <td class="hidden sm:table-cell"></td>
                                                    <td class="pb-1 pl-4 pr-3 text-md max-w-0 sm:pl-6" colspan="5">
                                                        <div class="flex flex-col hidden mt-1 sm:block">
                                                            <span class="text-gray-700">{{$estimate_line_item->desc}}</span>
                                                            @if($estimate_line_item->notes)
                                                                <hr>
                                                                <span class="text-gray-500"><i>{{$estimate_line_item->notes}}</i></span>
                                                            @endif
                                                            {{-- <span class="hidden sm:inline">·</span>
                                                            <span>$100</span> --}}
                                                        </div>
                                                        {{-- MOBILE VIEW DIV --}}
                                                        <div class="flex flex-col mt-1 text-gray-500 sm:block sm:hidden">
                                                            <span>{{$estimate_line_item->unit_type !== 'no_unit' ? $estimate_line_item->quantity . ' ' . $estimate_line_item->unit_type . ' @ ' .money($estimate_line_item->cost) . '/each' : ''}}</span>
                                                            {{-- <span class="hidden sm:inline">·</span>
                                                            <span>$100</span> --}}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </x-cards.body>
                            </div>
                            <x-cards.footer>
                                <button></button>
                                <h3>Section Total: {{money($section->total)}}</h3>
                            </x-cards.footer>
                        </x-cards.wrapper>
                    </div>
                </div>
            @endforeach

            {{-- ADD ANTOHER SECTION --}}
            <x-cards.wrapper>
                <button
                    type="button"
                    wire:click="sectionAdd"
                    class="relative block w-full p-4 text-center border-2 border-gray-300 border-dashed sm:rounded-lg hover:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{-- <svg class="w-12 h-12 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                        fill="none" viewBox="0 0 48 48" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />
                    </svg> --}}
                    <h1 class="block mt-2 text-xl font-medium text-gray-800">
                        Add Another Section
                    </h1>
                </button>
            </x-cards.wrapper>
        </div>

        <livewire:line-items.estimate-line-item-create :estimate="$estimate"/>

        {{-- ESTIMATE PAYMENTS --}}
        @if($estimate->payments)
            <div class="col-span-4 space-y-4 lg:col-start-1 lg:col-span-2">
                <x-cards.wrapper>
                    <x-cards.heading>
                        <x-slot name="left">
                            <h1 class="text-lg">Payment Schedule</b></h1>
                        </x-slot>
                    </x-cards.heading>
                    <x-cards.body>
                        <x-lists.ul>
                            @foreach($estimate->payments as $payment)
                                <x-lists.search_li
                                    :basic=true
                                    :line_title="$payment['description']"
                                    :line_data="$loop->last && $payment['amount'] == '' ? 'Balance' : money($payment['amount'])"
                                    >
                                </x-lists.search_li>
                            @endforeach
                        </x-lists.ul>
                    </x-cards.body>
                </x-cards.wrapper>
            </div>
        @endif

        {{-- ESTIMATE TOTAL --}}
        <div class="col-span-4 space-y-4 lg:col-start-3 lg:col-span-2">
            <x-cards.wrapper>
                <x-cards.heading>
                    <x-slot name="left">
                        <h1 class="text-lg">Finances</b></h1>
                    </x-slot>
                </x-cards.heading>
                <x-cards.body>
                    {{-- wire:loading should just target the Reimbursment search_li not the entire Proejct Finances wrapper--}}
                    <x-lists.ul
                        {{-- wire:target="print"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 text-opacity-40" --}}
                        >
                        <x-lists.search_li
                            :basic=true
                            :line_title="'Estimate'"
                            :line_data="money($this->estimate_total)"
                            >
                        </x-lists.search_li>

                        {{-- <x-lists.search_li
                            :basic=true
                            :line_title="'Change Order'"
                            :line_data="money($project->finances['change_orders'])"
                            >
                        </x-lists.search_li> --}}
                        @if($estimate->reimbursments)
                            <x-lists.search_li
                                :basic=true
                                :line_title="'Reimbursements'"
                                :line_data="money($estimate->reimbursments)"
                                >
                            </x-lists.search_li>
                        @endif

                        <x-lists.search_li
                            :basic=true
                            :bold="TRUE"
                            {{-- make gray --}}
                            :line_title="'TOTAL ESTIMATE'"
                            :line_data="money($this->estimate_total + $estimate->reimbursments)"
                            >
                        </x-lists.search_li>

                        @foreach($estimate->project->payments as $payment)
                            <x-lists.search_li
                                :basic=true
                                :line_title="'Payment ' . $payment->reference"
                                :line_data="money($payment->amount)"
                                >
                            </x-lists.search_li>
                        @endforeach

                        <x-lists.search_li
                            :basic=true
                            :bold="TRUE"
                            {{-- make gray --}}
                            :line_title="'TOTAL PAYMENTS'"
                            :line_data="money($estimate->project->payments->sum('amount'))"
                            >
                        </x-lists.search_li>

                        <x-lists.search_li
                            :basic=true
                            :bold="TRUE"
                            {{-- make gray --}}
                            :line_title="'BALANCE'"
                            :line_data="money(($this->estimate_total + $estimate->reimbursments) - $estimate->project->payments->sum('amount'))"
                            >
                        </x-lists.search_li>
                    </x-lists.ul>
                </x-cards.body>
            </x-cards.wrapper>
        </div>

    </div>
    {{-- @push('custom_styles')
        <style>
            .draggable-mirror {
                background-color: white;
                width: 350px;
            }

            .draggable-source--is-dragging {
                background-color: black;
                border: 10px;
                border-color: red;
            }
        </style>
    @endpush --}}
</div>


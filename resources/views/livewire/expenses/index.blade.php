<x-cards.wrapper class="{{$view == NULL ? 'max-w-xl px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-3xl lg:px-8 pb-5 mb-1' : ''}}">
    {{-- HEADING --}}
    <x-cards.heading>
        <x-slot name="left">
            <h1>Expenses</h1>
        </x-slot>

        @can('create', App\Models\Expense::class)
        <x-slot name="right">
            <x-cards.button href="{{route('expenses.create')}}">
                Create expense
            </x-cards.button>
        </x-slot>
        @endcan
        {{-- @endcan --}}
    </x-cards.heading>

    {{-- SUB-HEADING --}}
    <x-cards.heading>
        {{-- class="mt-3 sm:mt-0 sm:ml-4 --}}
        <div class="mx-auto">
            {{-- <label for="mobile-search-candidate" class="sr-only">Search</label> --}}
            <label for="desktop-search-candidate" class="sr-only">Search</label>
            <div class="flex rounded-md shadow-sm">
                <div class="relative flex-grow focus-within:z-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <!-- Heroicon name: solid/search -->
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input 
                        wire:model="amount" 
                        type="number"
                        inputmode="numeric" 
                        step="0.01"
                        name="mobile-search-candidate"
                        id="mobile-search-candidate"
                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-none rounded-l-md pl-10 sm:hidden border-gray-300"
                        placeholder="Search"
                        autocomplete="mobile-search-candidate"
                        >
                    <input 
                        wire:model="amount" 
                        type="number"
                        inputmode="numeric" 
                        step="0.01"
                        name="desktop-search-candidate"
                        id="desktop-search-candidate"
                        class="hidden focus:ring-indigo-500 focus:border-indigo-500 w-full rounded-none rounded-l-md pl-10 sm:block sm:text-sm border-gray-300"
                        placeholder="Search amount"
                        autocomplete="desktop-search-candidate"
                        >
                </div>
                <button type="button"
                    class="-ml-px relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                    <!-- Heroicon name: solid/sort-ascending -->
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path
                            d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z" />
                    </svg>
                    <span class="ml-2">Sort</span>
                    <!-- Heroicon name: solid/chevron-down -->
                    <svg class="ml-2.5 -mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            @if($view == NULL)
            <div>
                <select
                    wire:model="project"
                    id="project" 
                    name="project"
                    @disabled($view == 'projects.show')
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="" readonly>All Projects</option>
                    <option value="SPLIT">Project Splits</option>
                    <option value="NO_PROJECT">No Project</option>
                    @foreach($projects as $index => $project)
                        <option value="{{$project->id}}">{{$project->name}}</option>
                    @endforeach
                    <option disabled>----------</option>
                    @foreach($distributions as $index => $distribution)
                        <option value="D-{{$distribution->id}}">{{$distribution->name}}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <select
                    wire:model="vendor"
                    id="vendor" 
                    name="vendor"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="" readonly>All Vendors</option>
                    @foreach($vendors as $index => $vendor)
                        <option value="{{$vendor->id}}">{{$vendor->business_name}}</option>
                    @endforeach
                </select>
            </div>
            {{-- perfect dropdown sitewide! --}}
            {{-- <div class="max-w-xs">
                <div x-data="
                    select({ data: { au: 'Australia', be: 'Belgium', cn: 'China', 
                    fr: 'France', de: 'Germany', it: 'Italy', mx: 'Mexico', 
                    es: 'Spain', tr: 'Turkey', gb: 'United Kingdom', 
                    'us': 'United States' }, 
                    emptyOptionsMessage: 'No projects match your search.', 
                    name: 'country', placeholder: 'Select a project' 
                    })" x-init="init()" @click.away="closeListbox()" @keydown.escape="closeListbox()"
                    class="relative">
                    <span class="inline-block w-full rounded-md shadow-sm">
                        <button x-ref="button" @click="toggleListboxVisibility()" :aria-expanded="open"
                            aria-haspopup="listbox"
                            class="relative z-0 w-full py-2 pl-3 pr-10 text-left transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md cursor-default focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5">
                            <span x-show="! open" x-text="value in options ? options[value] : placeholder"
                                :class="{ 'text-gray-500': ! (value in options) }" class="block truncate"></span>

                            <input x-ref="search" x-show="open" x-model="search"
                                @keydown.enter.stop.prevent="selectOption()"
                                @keydown.arrow-up.prevent="focusPreviousOption()"
                                @keydown.arrow-down.prevent="focusNextOption()" type="search"
                                class="w-full h-full form-control focus:outline-none" />

                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="none"
                                    stroke="currentColor">
                                    <path d="M7 7l3-3 3 3m0 6l-3 3-3-3" stroke-width="1.5" stroke-linecap="round"
                                        stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </button>
                    </span>

                    <div x-show="open" x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak
                        class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg">
                        <ul x-ref="listbox" @keydown.enter.stop.prevent="selectOption()"
                            @keydown.arrow-up.prevent="focusPreviousOption()"
                            @keydown.arrow-down.prevent="focusNextOption()" role="listbox"
                            :aria-activedescendant="focusedOptionIndex ? name + 'Option' + focusedOptionIndex : null"
                            tabindex="-1"
                            class="py-1 overflow-auto text-base leading-6 rounded-md shadow-xs max-h-60 focus:outline-none sm:text-sm sm:leading-5">
                            <template x-for="(key, index) in Object.keys(options)" :key="index">
                                <li :id="name + 'Option' + focusedOptionIndex" @click="selectOption()"
                                    @mouseenter="focusedOptionIndex = index" @mouseleave="focusedOptionIndex = null"
                                    role="option" :aria-selected="focusedOptionIndex === index"
                                    :class="{ 'text-white bg-indigo-600': index === focusedOptionIndex, 'text-gray-900': index !== focusedOptionIndex }"
                                    class="relative py-2 pl-3 text-gray-900 cursor-default select-none pr-9">
                                    <span x-text="Object.values(options)[index]"
                                        :class="{ 'font-semibold': index === focusedOptionIndex, 'font-normal': index !== focusedOptionIndex }"
                                        class="block font-normal truncate"></span>

                                    <span x-show="key === value"
                                        :class="{ 'text-white': index === focusedOptionIndex, 'text-indigo-600': index !== focusedOptionIndex }"
                                        class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
                                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </li>
                            </template>

                            <div x-show="! Object.keys(options).length" x-text="emptyOptionsMessage"
                                class="px-3 py-2 text-gray-900 cursor-default select-none"></div>
                        </ul>
                    </div>
                </div>

                <script>
                    function select(config) {
                        return {
                            data: config.data,
        
                            emptyOptionsMessage: config.emptyOptionsMessage ?? 'No results match your search.',
        
                            focusedOptionIndex: null,
        
                            name: config.name,
        
                            open: false,
        
                            options: {},
        
                            placeholder: config.placeholder ?? 'Select an option',
        
                            search: '',
        
                            value: config.value,
        
                            closeListbox: function () {
                                this.open = false
        
                                this.focusedOptionIndex = null
        
                                this.search = ''
                            },
        
                            focusNextOption: function () {
                                if (this.focusedOptionIndex === null) return this.focusedOptionIndex = Object.keys(this.options).length - 1
        
                                if (this.focusedOptionIndex + 1 >= Object.keys(this.options).length) return
        
                                this.focusedOptionIndex++
        
                                this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                                    block: "center",
                                })
                            },
        
                            focusPreviousOption: function () {
                                if (this.focusedOptionIndex === null) return this.focusedOptionIndex = 0
        
                                if (this.focusedOptionIndex <= 0) return
        
                                this.focusedOptionIndex--
        
                                this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                                    block: "center",
                                })
                            },
        
                            init: function () {
                                this.options = this.data
        
                                if (!(this.value in this.options)) this.value = null
        
                                this.$watch('search', ((value) => {
                                    if (!this.open || !value) return this.options = this.data
        
                                    this.options = Object.keys(this.data)
                                        .filter((key) => this.data[key].toLowerCase().includes(value.toLowerCase()))
                                        .reduce((options, key) => {
                                            options[key] = this.data[key]
                                            return options
                                        }, {})
                                }))
                            },
        
                            selectOption: function () {
                                if (!this.open) return this.toggleListboxVisibility()
        
                                this.value = Object.keys(this.options)[this.focusedOptionIndex]
        
                                this.closeListbox()
                            },
        
                            toggleListboxVisibility: function () {
                                if (this.open) return this.closeListbox()
        
                                this.focusedOptionIndex = Object.keys(this.options).indexOf(this.value)
        
                                if (this.focusedOptionIndex < 0) this.focusedOptionIndex = 0
        
                                this.open = true
        
                                this.$nextTick(() => {
                                    this.$refs.search.focus()
        
                                    this.$refs.listbox.children[this.focusedOptionIndex].scrollIntoView({
                                        block: "center"
                                    })
                                })
                            },
                        }
                    }
                </script>
            </div> --}}
        </div>
    </x-cards.heading>

    {{-- BODY --}}
    <x-cards.body>
        <x-lists.ul>
            @foreach($expenses as $expense)
                @php
                    if($view == 'projects.show'){
                        $line_details = [
                        1 => [
                            'text' => $expense->date->format('m/d/Y'),
                            'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'

                            ],
                        2 => [
                            'text' => $expense->vendor->business_name,
                            'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'

                            ],
                        ];
                    }else{
                        $line_details = [
                        1 => [
                            'text' => $expense->date->format('m/d/Y'),
                            'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'

                            ],
                        2 => [
                            'text' => $expense->vendor->business_name,
                            'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'

                            ],
                        3 => [
                            'text' => $expense->distribution ? $expense->distribution->name : $expense->project->name,
                            'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'

                            ],
                        ];
                    }
                @endphp
    
                <x-lists.search_li
                    href="{{route('expenses.show', $expense->id)}}"
                    :line_details="$line_details"
                    {{-- :no_hover=true --}}
                    :line_title="money($expense->amount)"
                    :bubble_message="'Success'"
                    >
                </x-lists.search_li>
            @endforeach
        </x-lists.ul>
    </x-cards.body>

    {{-- FOOTER --}}
    <x-cards.footer>
        {{-- 10/14/21 change/customize links view in resources/views/vendor/pagination/tailwind.blade.php to match our
        theme --}}
        {{ $expenses->links() }}
    </x-cards.footer>
</x-cards.wrapper>
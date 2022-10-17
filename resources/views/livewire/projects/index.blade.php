{{-- xl:relative max-w-xl lg:max-w-5xl grid grid-cols-4 gap-4 sm:px-6 mx-auto --}}
{{-- max-w-xl px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-3xl lg:px-8 pb-5 mb-1 --}}
<x-cards.wrapper class="{{$view == NULL ? 'max-w-xl px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-3xl lg:px-8 pb-5 mb-1' : ''}}">
    {{-- HEADING --}}
    <x-cards.heading>
        <x-slot name="left">
            <h1>Projects</h1>
        </x-slot>

        <x-slot name="right">
            {{-- <x-cards.button href="{{route('vendors.create')}}">
                Create vendor
            </x-cards.button> --}}
        </x-slot>
    </x-cards.heading>

    {{-- SUB-HEADING --}}
    <x-cards.heading>
        {{-- main $slot --}}
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
                    <input wire:model="project_name" type="text" name="mobile-search-candidate"
                        id="mobile-search-candidate"
                        class="focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-none rounded-l-md pl-10 sm:hidden border-gray-300"
                        placeholder="Search">
                    <input wire:model="project_name" type="text" name="desktop-search-candidate"
                        id="desktop-search-candidate"
                        class="hidden focus:ring-indigo-500 focus:border-indigo-500 w-full rounded-none rounded-l-md pl-10 sm:block sm:text-sm border-gray-300"
                        placeholder="Search Projects">
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
            <div>
                @if($view == NULL)
                <select
                    wire:model="client_id"
                    id="client_id" 
                    name="client_id"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="" readonly>All Clients</option>
                    
                    @foreach($clients as $client)
                        <option value="{{$client->id}}">{{$client->name}}</option>
                    @endforeach
                </select>
                @endif
                <div>
                    <select
                        wire:model="project_status_title"
                        id="project_status_title" 
                        name="project_status_title"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                        >
                        @include('livewire.projects._status_options')                       
                    </select>
                </div>
            </div>
        </div>
    </x-cards.heading>

    {{-- LIST / using List as a table because of mobile layouts vs a table mobile layout --}}
    <x-lists.ul>
        @foreach($projects as $project)
            @php
                $line_details = [
                    1 => [
                        'text' => $project->client->name,
                        'icon' => 'M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z'
                        ],
                    ];
            @endphp
 
            <x-lists.search_li
                href="{{route('projects.show', $project->id)}}"
                :line_details="$line_details"
                :line_title="$project->name"
                :bubble_message="$project->project_status->title"
                >

            </x-lists.search_li>
        @endforeach
    </x-lists.ul>

    {{-- FOOTER for forms for example --}}
    {{-- <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
        <button type="submit"
            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Save
        </button>
    </div> --}}

    {{-- FOOTER --}}
    <x-cards.footer>
        {{-- 10/14/21 change/customize links view in resources/views/vendor/pagination/tailwind.blade.php to match our
        theme --}}
        {{ $projects->links() }}
    </x-cards.footer>
</x-cards.wrapper>
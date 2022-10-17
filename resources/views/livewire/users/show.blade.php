<x-modals.modal>
    <x-cards.heading>
        <x-slot name="left">
            <h1>{{$user->full_name}}</h1>
        </x-slot>

        <x-slot name="right">
            {{-- <x-cards.button href="{{route('users.edit', $user->id)}}">
                Edit user
            </x-cards.button> --}}

            {{-- wire:click="$emit('newMember')" --}}
            <x-cards.button>
                Edit {{$user->first_name}}
            </x-cards.button>
        </x-slot>
    </x-cards.heading>

    <div class="px-4 py-5 sm:px-6">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500">
                    Name
                </dt>
                <dd class="mt-1 text-md text-gray-900">
                    {{$user->full_name}}
                </dd>
            </div>
            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500">
                    Email
                </dt>
                <dd class="mt-1 text-md text-gray-900">
                    {{$user->email}}
                </dd>
            </div>
            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500">
                    Cell Phone
                </dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{$user->cell_phone}}
                </dd>
            </div>
            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500">
                    Vendor Role
                </dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{-- {{$user->vendor_role}} --}}
                </dd>
            </div>
        </dl>
    </div>

    {{-- FOOTER --}}
    <x-cards.footer>
        {{-- 2-7-22 only show if there is at least one Admin...and only admins can see and access --}}
        <x-cards.button wire:click="$emit('removeMember')">
            {{-- Remove {{$user->first_name}} from {{$user->vendor->business_name}} --}}
        </x-cards.button>
        {{-- {{ $expenses->links() }} --}}
    </x-cards.footer>

    {{-- @livewire('users.users-show', ['vendor_id' => 1]) --}}
</x-modals.modal>
<div>
    <x-cards.wrapper class="w-full px-4 pb-5 mb-1 sm:px-6 lg:max-w-xl lg:px-8">
        {{-- HEADING --}}
        <x-cards.heading>
            <x-slot name="left">
                <h1>Retail Vendor Sheets Type</h1>
                <p class="text-sm text-gray-500">Assign Materials type to Vendors below. Leave NULL if Vendor is a General Expense and not Materials.</p>
            </x-slot>
        </x-cards.heading>

        <x-cards.body :class="'space-y-2 py-2'">
            @foreach($vendors as $index => $vendor)
                <x-forms.row
                    wire:model.live="vendors.{{$index}}.sheets_type"
                    errorName="vendors.{{$index}}.sheets_type"
                    {{-- wire:key="{{$vendor->id}}" --}}
                    name="vendors.{{$index}}.sheets_type"
                    text="{!! $vendor->name !!}"
                    type="dropdown"
                    >

                    <option value="" readonly>Type</option>
                    <option value="Materials" readonly>Materials</option>
                </x-forms.row>
            @endforeach
        </x-cards.body>
    </x-cards.wrapper>
</div>


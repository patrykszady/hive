<div>
	<div
		class="max-w-3xl px-4 mx-auto sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-5xl lg:px-8">
		<div class="flex items-center space-x-5">
			<div>
				<h1 class="text-2xl font-bold text-gray-900">{{$user->vendor->business_name}}</h1>
				<p class="text-sm font-medium text-gray-500">
					{{$user->full_name}}'s dashboard for <b>{{$user->vendor->name}} | {{$user->vendor->business_type}}</b> Vendor.
				</p>
			</div>
		</div>
	</div>

    <div class="grid max-w-2xl grid-cols-1 gap-6 mx-auto mt-8 sm:px-6 lg:max-w-5xl lg:grid-flow-col-dense lg:grid-cols-6">
        {{-- VENDOR DETAILS --}}
        <div class="space-y-6 lg:col-start-1 lg:col-span-3">
            <livewire:vendors.vendor-details :vendor="$user->vendor">
        </div>
        {{-- VENDOR TEAM MEMBERS --}}
        <div class="space-y-6 lg:col-start-4 lg:col-span-3">
            <livewire:users.team-members :vendor="$user->vendor">
        </div>
    </div>
    <livewire:vendors.vendor-create />
</div>

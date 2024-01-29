<div>
    {{-- CREATE RECEIPT ACCOUNT --}}
    <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-xl lg:px-8 pb-5 mb-1' : ''}}" key="{{ Str::random() }}">
        <form wire:submit="store">
            <x-cards.heading>
                <x-slot name="left">
                    <h1>
                        Add Receipt Accounts
                    </h1>
                    <p class="max-w-2xl mt-1 text-sm text-gray-500"
                        >
                        Select which Distribution you would like to automatically assign the below Vendor Email Receipts. If NO PROJECT is displayed next to a Vendor below it means we will process their Email Receipt and ask you to assign a Project for each of the Vendor Receipts.
                    </p>
                </x-slot>
            </x-cards.heading>
        </form>
    </x-cards.wrapper>
    {{-- VENDOR --}}
    @foreach ($vendors as $vendor_index => $vendor)
        <x-cards.wrapper class="{{$view == NULL ? 'w-full px-4 sm:px-6 lg:max-w-xl lg:px-8 pb-2 mb-1' : ''}}" key="{{ Str::random() }}">
            <form wire:submit="store">
                <x-cards.heading>
                    <x-slot name="left">
                        <h1>
                            {!! $vendor->name !!}
                        </h1>
                    </x-slot>

                    <x-slot name="right">
                        @if($vendor->receipt_accounts->first()->belongs_to_vendor_id == $auth_vendor->id && $vendor->id == 54)
                            <a
                                href="{{route('amazon_login')}}"
                                target="_blank"
                                type="button"
                                class="inline-flex justify-center px-4 py-2 text-sm text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                Login to Amazon
                            </a>
                        @endif
                    </x-slot>
                </x-cards.heading>

                <x-cards.body :class="'space-y-4 my-4'">
                    {{-- DISTRIBUTION --}}

                    {{-- IF this $vendor not vendor or vendor (this auth->user->vendor) show "add $vendor->name to auth->user->vendor->name ...
                    OTHERWISE IF $vendor not in_array of auth->user->vendor->id show NO PROJECT ...
                    if IN in_array of auth->user->vendor->id show option of $receipt_account..this one ->distribution_id.. if is_null(distribution_id) show NO PROJECT --}}

                    @if($vendor->receipt_accounts->first()->belongs_to_vendor_id == $auth_vendor->id)
                        <x-forms.row
                            wire:model.live.debounce.250ms="vendors.{{$vendor_index}}.receipt_accounts.0.distribution_id"
                            errorName="vendors.{{$vendor_index}}.receipt_accounts.0.distribution_id"
                            name="distribution_id"
                            text="Distribution"
                            type="dropdown"
                            >

                            <option value="">NO PROJECT</option>
                            @foreach ($distributions as $distribution)
                                {{-- {{ in_array($vendor->id, $this->receipt_account_ids) ? 'disabled' : '' }} --}}
                                {{-- active/selected if $vendor->receipt_accounts->first()->distribution_id == $distribution->id --}}
                                <option value="{{$distribution->id}}">{{$distribution->name}}</option>
                            @endforeach
                        </x-forms.row>
                    @else
                        <x-forms.row
                            {{-- wire:click="$dispatch('addSplits', {{$expense->amount}}, {{!empty($expense_splits) ? $expense_splits : NULL}})" --}}
                            type="button"
                            text="Add {{$vendor->name}} to {!! auth()->user()->vendor->name !!}"
                            wire:click="$emitSelf('addVendorToVendor', {{$vendor->id}})"
                            x-text="'Add {!! $vendor->name !!}'"
                            >
                        </x-forms.row>
                    @endif
                </x-cards.body>
{{--
                @if(in_array($vendor->id, $this->vendor_vendors_ids)) --}}
                    <x-cards.footer>
                        <button>
                        </button>

                        <button
                            {{-- x-data="{ open: @entangle('modal_show').live }" --}}
                            {{-- x-on:click="open = false && errors = false" --}}
                            type="submit"
                            x-text="'{{isset($vendor->receipt_account->distribution) ? 'Update Distribution' : 'Add Distribution'}}'"
                            class="inline-flex justify-center px-4 py-2 ml-3 text-sm text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        </button>
                    </x-cards.footer>
                {{-- @endif --}}
            </form>
        </x-cards.wrapper>
    @endforeach
</div>

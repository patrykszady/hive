{{--  x-data="{ check_input_existing: @entangle('check_input_existing') }" --}}
<div>
    <x-forms.row
        wire:model.live="form.bank_account_id"
        errorName="form.bank_account_id"
        name="bank_account_id"
        text="Bank"
        type="dropdown"
        {{-- x-bind:disabled="check_input_existing" --}}
        >

        <option value="" readonly>Select Bank</option>
        @foreach ($bank_accounts as $index => $bank_account)
            <option value="{{$bank_account->id}}">
                {{$bank_account->getNameAndType()}}
            </option>
        @endforeach
    </x-forms.row>


    <div
        x-data="{ bank_account: @entangle('form.bank_account_id') }"
        x-show="bank_account"
        x-transition
        class="mt-2 space-y-2"
        >
        <x-forms.row
            wire:model.live="form.check_type"
            errorName="form.check_type"
            name="check_type"
            text="Type"
            type="dropdown"
            {{-- x-bind:disabled="check_input_existing" --}}
            >
            <option value="" readonly>Select Payment Type</option>
            <option value="Check">Check</option>
            <option value="Transfer">Transfer</option>
            <option value="Cash">Cash</option>
        </x-forms.row>

        <div
            x-data="{ check_type: @entangle('form.check_type') }"
            x-show="check_type == 'Check'"
            x-transition
            >
            <x-forms.row
                wire:model.live="form.check_number"
                errorName="form.check_number"
                name="check_number"
                text="Check Number"
                type="number"
                placeholder="Check Number"
                inputmode="numeric"
                step="1"
                {{-- x-bind:disabled="check_input_existing" --}}
                >
            </x-forms.row>
        </div>
    </div>
</div>

<x-forms.row
    wire:model="check.bank_account_id"
    errorName="check.bank_account_id"
    name="check.bank_account_id"
    text="Bank"
    type="dropdown"
    >

    <option value="" readonly>Select Bank</option>
    @foreach ($bank_accounts as $index => $bank_account)
        <option value="{{$bank_account->id}}">
            {{$bank_account->getNameAndType()}}
        </option>
    @endforeach
</x-forms.row>

<div 
    x-data="{ open: @entangle('check.bank_account_id') }" 
    x-show="open" 
    x-transition.duration.150ms
    class="space-y-2 mt-2"
    >
    <x-forms.row
        wire:model="check.check_type"
        errorName="check.check_type" 
        name="check.check_type"
        text="Type" 
        type="dropdown"
        >
        <option value="" readonly>Select Payment Type</option>
        <option value="Check">Check</option>
        <option value="Transfer">Transfer</option>
        <option value="Cash">Cash</option>
    </x-forms.row>

    <div 
        x-data="{ open: @entangle('check_input') }" 
        x-show="open" 
        x-transition.duration.150ms
        >
        <x-forms.row 
            wire:model="check.check_number" 
            errorName="check.check_number" 
            name="check.check_number" 
            text="Check Number"
            type="number" 
            placeholder="Check Number"
            inputmode="numeric" 
            step="1"
            >
        </x-forms.row>
    </div>
</div>
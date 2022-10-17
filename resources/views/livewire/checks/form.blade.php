{{-- <form wire:submit.prevent="{{$view_text['form_submit']}}" class="space-y-2">  --}}
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
    <x-forms.row
        wire:model="check.check_type"
        errorName="check.check_type" 
        name="check.check_type"
        text="Type" 
        type="dropdown"
        >
        <option value="" readonly x-text="'Select Payment Type'"></option>
        <option value="Check" x-text="'Check'"></option>
        <option value="Transfer" x-text="'Transfer'"></option>
        <option value="Cash" x-text="'Cash'"></option>
    </x-forms.row>
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
{{-- </form>   --}}
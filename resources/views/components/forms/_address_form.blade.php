<x-forms.row
    wire:model.live.debounce.500ms="form.address"
    errorName="form.address"
    name="address"
    text="Address"
    type="text"
    placeholder="Street Address"
    >
</x-forms.row>

<x-forms.row
    wire:model.live.debounce.500ms="form.address_2"
    errorName="form.address_2"
    name="address_2"
    text=""
    type="text"
    placeholder="Unit Number"
    >
</x-forms.row>

<x-forms.row
    wire:model.live.debounce.500ms="form.city"
    errorName="form.city"
    name="city"
    text=""
    type="text"
    placeholder="City"
    >
</x-forms.row>

<x-forms.row
    wire:model.live.debounce.250ms="form.state"
    errorName="form.state"
    name="state"
    text=""
    type="text"
    placeholder="State"
    maxlength="2"
    minlength="2"
    >
</x-forms.row>

<x-forms.row
    wire:model.live.debounce.500ms="form.zip_code"
    errorName="form.zip_code"
    name="zip_code"
    text=""
    type="number"
    placeholder="Zip Code"
    maxlength="5"
    minlength="5"
    inputmode="numeric"
    >
</x-forms.row>

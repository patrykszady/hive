<x-forms.row
    wire:model.live.debounce.500ms="form.date"
    errorName="form.date"
    name="date"
    text="Date"
    type="date"
    >
</x-forms.row>

{{-- Paid by --}}
<div
    {{-- 'disable' entangle only used on TimesheetPaymentCreate, Console Error otherwire (Vendors Payment) --}}
    x-data="{ disable: @entangle('disable_paid_by') }"
    >
    <x-forms.row
        wire:model.live="form.paid_by"
        errorName="form.paid_by"
        name="paid_by"
        text="Paid By"
        type="dropdown"
        >

        <option value="" readonly>{{auth()->user()->vendor->business_name}}</option>
        @foreach ($employees as $employee)
            <option
                value="{{$employee->id}}"
                x-bind:disabled="disable"
                >
                {{$employee->first_name}}
            </option>
        @endforeach
    </x-forms.row>
</div>

<div
    x-data="{ open: @entangle('form.paid_by') }"
    x-show="!open"
    x-transition.duration.250ms
    >

    {{-- <livewire:checks.check-create /> --}}
    @include('livewire.checks._include_form')
</div>
<div
    x-data="{ open: @entangle('form.paid_by') }"
    x-show="open"
    x-transition.duration.250ms
    >
    <x-forms.row
        wire:model.live="form.invoice"
        errorName="form.invoice"
        name="invoice"
        text="Reference"
        type="text"
        >
    </x-forms.row>
</div>

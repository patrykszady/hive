<x-forms.row 
    wire:model.debounce.500ms="check.date" 
    errorName="check.date" 
    name="date" 
    text="Date" 
    type="date"
    >
</x-forms.row>

{{-- Paid by --}}
<x-forms.row 
    wire:model="check.paid_by" 
    errorName="check.paid_by" 
    name="paid_by" 
    text="Paid By"
    type="dropdown"
    >

    <option value="" readonly>{{auth()->user()->vendor->business_name}}</option>
    @foreach ($employees as $employee)
        <option value="{{$employee->id}}">{{$employee->first_name}}</option>
    @endforeach
</x-forms.row>

<div 
    x-data="{ open: @entangle('check.paid_by') }" 
    x-show="!open" 
    x-transition.duration.150ms
    >

    @include('livewire.checks._include_form')
</div>
<div
    x-data="{ open: @entangle('check.paid_by') }" 
    x-show="open" 
    x-transition.duration.150ms
    >
    <x-forms.row 
        wire:model="check.invoice" 
        name="check.invoice" 
        errorName="check.invoice"
        text="Reference"
        type="text"  
        >
    </x-forms.row>
</div>
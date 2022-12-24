<div>
	<x-page.top
		h1="{{ money($expense->amount) }}"
		p="Expense for {!! $expense->vendor->business_name !!}"
		{{-- right_button_href="{{auth()->user()->can('update', $expense) ? route('expenses.edit', $expense->id) : ''}}" --}}
		{{-- right_button_text="Edit Expense" --}}
		>
		
	</x-page.top>

	@include('livewire.expenses._show')
	@livewire('expenses.expenses-new-form')
</div>
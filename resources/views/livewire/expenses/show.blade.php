<div>
	<x-page.top
		h1="{{ money($expense->amount) }}"
		p="Expense for {!! $expense->vendor->business_name !!}"
		{{-- right_button_href="{{auth()->user()->can('update', $expense) ? route('expenses.edit', $expense->id) : ''}}" --}}
		{{-- right_button_text="Edit Expense" --}}
		>

	</x-page.top>

	@include('livewire.expenses._show')

	{{-- top level so content is in front of everything on page --}}
    @can('update', $expense)
	    <livewire:expenses.expense-create />
    @endif
</div>

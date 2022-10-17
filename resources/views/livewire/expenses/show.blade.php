<x-page.top
	h1="{{ money($expense->amount) }}"
	p="Expense for {{ $expense->vendor->business_name }}"
	right_button_href="{{auth()->user()->can('update', $expense) ? route('expenses.edit', $expense->id) : ''}}"

	{{-- {{ route('expenses.edit', $expense->id) }} --}}
	right_button_text="Edit Expense"
	>
</x-page.top>

<div class="xl:relative max-w-xl lg:max-w-5xl grid grid-cols-4 gap-4 sm:px-6 mx-auto">
	<div class="col-span-4 lg:col-span-2 lg:h-32 lg:sticky lg:top-5 space-y-4">
		{{-- EXPENSE DETAILS --}}
		<x-cards.wrapper>
			<x-cards.heading>
				<x-slot name="left">
					<h1>Expense Details</h1>
					{{-- <p>Expense and related details like Expense Splits and Expense Receipts.</p> --}}
				</x-slot>

				@can('update', $expense)
					<x-slot name="right">
						<x-cards.button href="{{ route('expenses.edit', $expense->id) }}">
							Edit Expense
						</x-cards.button>
					</x-slot>
				@endcan
			</x-cards.heading>
	
			<x-cards.body>
				<x-lists.ul>
					<x-lists.search_li
						:basic=true
						:line_title="'Amount'"
						:line_data="money($expense->amount)"
						>
					</x-lists.search_li>

					<x-lists.search_li
						:basic=true
						:line_title="'Date'"
						:line_data="$expense->date->format('m/d/Y')"
						>
					</x-lists.search_li>

					<x-lists.search_li
						:basic=true
						:line_title="'Vendor'"
						:line_data="$expense->vendor->business_name"
						href="{{route('vendors.show', $expense->vendor->id)}}"
						>
					</x-lists.search_li>

					@if($expense->distribution)
						<x-lists.search_li
							:basic=true
							:line_title="'Account'"
							:line_data="$expense->distribution->name"
							{{-- href="{{route('vendors.show', $expense->vendor->id)}}" --}}
							>
						</x-lists.search_li>
					@elseif($expense->splits()->exists())
						<x-lists.search_li
							:basic=true
							:line_title="'Project'"
							:line_data="$expense->project->name"
							{{-- href="{{route('projects.show', $expense->project->id)}}" --}}
							>
						</x-lists.search_li>
					@elseif($expense->project->name == 'No Project')
						<x-lists.search_li
							:basic=true
							:line_title="'Project'"
							:line_data="$expense->project->name"
							{{-- href="{{route('projects.show', $expense->project->id)}}" --}}
							>
						</x-lists.search_li>
					@else
						<x-lists.search_li
							:basic=true
							:line_title="'Project'"
							:line_data="$expense->project->name"
							href="{{route('projects.show', $expense->project->id)}}"
							>
						</x-lists.search_li>
					@endif

					@if($expense->paid_by)
						<x-lists.search_li
							:basic=true
							:line_title="'Paid By'"
							:line_data="$expense->paidby->full_name"
							>
						</x-lists.search_li>
					@endif

					@if($expense->invoice)
						<x-lists.search_li
							:basic=true
							:line_title="'Invoice/PO'"
							:line_data="$expense->invoice"
							>
						</x-lists.search_li>
					@endif

					@if($expense->note)
						<x-lists.search_li
							:basic=true
							:line_title="'Note'"
							:line_data="$expense->note"
							>
						</x-lists.search_li>
					@endif
				</x-lists.ul>
			</x-cards.body>
		</x-cards.wrapper>

		{{-- TRANSACTIONS --}}
		@if(!$expense->transactions->isEmpty())
		<x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
			<x-cards.heading>
				<x-slot name="left">
					<h1>Transactions</h1>
				</x-slot>
			</x-cards.heading>

			<x-lists.ul>
				@foreach($expense->transactions as $transaction)
					{{-- @dd($transaction->bank_account->bank) --}}
					@php
						$line_details = [
							1 => [
								'text' => $transaction->posted_date ? $transaction->posted_date->format('m/d/Y') : $transaction->transaction_date->format('m/d/Y'),
								'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'					
								],
							2 => [
								'text' => $transaction->bank_account->bank->name,
								'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z'
								],
							3 => [
								'text' => $transaction->plaid_merchant_name,
								'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'					
								],
							];
					@endphp
		
					<x-lists.search_li
						href=""
						:line_details="$line_details"
						:line_title="money($transaction->amount)"
						:bubble_message="'Transaction'"
						>
					</x-lists.search_li>
				@endforeach
			</x-lists.ul>
		</x-cards.wrapper>
		@endif

	</div>

	<div class="col-span-4 lg:col-span-2 space-y-2">
		{{-- SPLITS --}}
		@if(!$expense->splits->isEmpty())
		<x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
			<x-cards.heading>
				<x-slot name="left">
					<h1>Splits</h1>
				</x-slot>
			</x-cards.heading>

			<x-lists.ul>
				@foreach($splits as $split)
					@php
						$line_details = [
							1 => [
								'text' => $expense->date->format('m/d/Y'),
								'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'
								],
							2 => [
								'text' => $expense->vendor->business_name,
								'icon' => 'M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z'
								],
							3 => [
								'text' => $split->distribution ? $split->distribution->name : $split->project->name,
								'icon' => 'M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z'					
								],
							];
					@endphp

					@if($split->reimbursment == 'Client')
						@php
							$line_details[] = [
								'text' => $split->reimbursment,
								'icon' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z'
							];
						@endphp
					@endif
		
					<x-lists.search_li
						:line_details="$line_details"
						:line_title="money($split->amount)"
						:bubble_message="'Split'"
						>
					</x-lists.search_li>
				@endforeach
			</x-lists.ul>
		</x-cards.wrapper>
		@endif

		{{-- CHECK --}}
		@if($expense->check)
			<x-cards.wrapper>
				<x-cards.heading>
					<x-slot name="left">
						<h1>Check</h1>
					</x-slot>
				</x-cards.heading>
		
				<x-lists.ul>				
					@php
						$line_details = [
								1 => [
									'text' => $expense->check->check_type,
									'icon' => 'M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z'					
									],
								2 => [
									'text' => $expense->check->check_number,
									'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z'
									],
								3 => [
									'text' => is_null($expense->check->bank_account) ? '' : $expense->check->bank_account->getNameAndType(),
									'icon' => 'M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z'					
									],
							];
					@endphp
		
					<x-lists.search_li
						href="{{ route('checks.show', $expense->check->id) }}"
						:line_details="$line_details"
						:line_title="money($expense->check->amount)"
						:bubble_message="'Check'"
						>
					</x-lists.search_li>			
				</x-lists.ul>
			</x-cards.wrapper>
		@endif

		{{-- RECEIPTS --}}
		@if(!$expense->receipts->isEmpty())
		<x-cards.wrapper class="col-span-4 lg:col-span-2 lg:col-start-3">
			<x-cards.heading>
				<x-slot name="left">
					<h1>Receipt</h1>
				</x-slot>
				<x-slot name="right">
					{{-- {{route('projects.show', $project->id)}} --}}
					<x-cards.button href="{{ route('expenses.original_receipt', $expense->receipts()->first()->receipt_filename) }}">
						Original Receipt
					</x-cards.button>
				</x-slot>
			</x-cards.heading>

			<div class="flow-root border-t border-gray-200">
				<div class="m-2">
					<pre style="background-color:transparent; overflow: auto;" >{!! $expense->receipts()->first()->receipt_html !!}</pre>
				</div>
			</div>
		</x-cards.wrapper>
		@endif
	</div>
</div>
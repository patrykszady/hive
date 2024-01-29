<x-modals.modal>
    <form wire:submit="{{$view_text['form_submit']}}">
        <x-cards.heading>
            <x-slot name="left">
                <h1>{{$view_text['card_title']}}</h1>
            </x-slot>
            {{-- <x-slot name="right">
                @if(isset($form->expense->id))
                    <x-cards.button href="{{route('expenses.show', $form->expense->id)}}" target="_blank">
                        Show Expense
                    </x-cards.button>
                @endif
            </x-slot> --}}
        </x-cards.heading>

        {{-- ROWS --}}
        <x-cards.body :class="'space-y-4 my-4'">
            {{-- ADMIN USERS --}}
            <x-forms.row
                wire:model.live="form.user_id"
                errorName="form.user_id"
                name="form.user_id"
                text="User"
                type="dropdown"
                >
                <option value="" readonly>Select User</option>
                @foreach ($form->users as $user)
                    <option value="{{$user->id}}">{{$user->full_name}}</option>
                @endforeach
            </x-forms.row>

            <x-forms.row
                wire:model.live="form.name"
                errorName="form.name"
                name="form.name"
                text="Name"
                type="text"
                placeholder="Office"
                >
            </x-forms.row>
        </x-cards.body>

        <x-cards.footer>
            <button
                type="button"
                x-on:click="open = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                Cancel
            </button>

            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm disabled:opacity-50 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                {{$view_text['button_text']}}
            </button>
        </x-cards.footer>
    </form>
</x-modals.modal>

<div class="xl:relative max-w-xl lg:max-w-2xl sm:px-6 mx-auto">
    <form wire:submit.prevent="{{$view_text['form_submit']}}">
        <x-cards.wrapper class="max-w-3xl mx-auto">
            {{-- HEADER --}}
            <x-cards.heading>
                <x-slot name="left">
                    <h1>{{$view_text['card_title']}}</h1>
                </x-slot>
                <x-slot name="right">
                    <x-cards.button href="{{route('clients.index')}}">
                        All Clients
                    </x-cards.button>
{{-- 
                    @if(request()->routeIs('expenses.edit'))
                        <x-cards.button href="{{route('expenses.show', $expense->id)}}">
                            Show Client
                        </x-cards.button>
                    @endif --}}
                </x-slot>
            </x-cards.heading>

            {{-- ROWS --}}
            <x-cards.body :class="'space-y-4 my-4'">
                {{-- USER/Client NUMBER --}}
                <x-forms.row 
                    wire:model.debounce.500ms="client.number" 
                    errorName="client.number" 
                    name="client.number"
                    text="Phone Number"
                    type="tel" 
                    hint="#" 
                    textSize="xl" 
                    placeholder="000-000-0000" 
                    inputmode="tel" 
                    {{-- lenght = 10 --}}
                    autofocus
                    > 
                </x-forms.row>

                {{-- disabled unless above is filled without error --}}
                <x-forms.row
                    wire:click.prevent="user_cell"
                    errorName=""
                    name=""
                    text=""
                    type="button"
                    buttonText="Search Users"
                    >    
                </x-forms.row>
            </x-cards.body>
        </x-cards.wrapper>
    </form>
</div>
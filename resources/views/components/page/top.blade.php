<div
    class="max-w-xl mx-auto px-4 sm:px-6 md:flex md:items-center md:justify-between md:space-x-5 lg:max-w-5xl lg:px-8 pb-5 mb-1">
    <div class="flex items-center space-x-5">
        {{-- LEFT IMAGE --}}
        {{-- <div class="flex-shrink-0">
            <div class="relative">
                <img class="h-16 w-16 rounded-full"
                    src="https://images.unsplash.com/photo-1463453091185-61582044d556?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=1024&h=1024&q=80"
                    alt="">
                <span class="absolute inset-0 shadow-inner rounded-full" aria-hidden="true"></span>
            </div>
        </div> --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{$h1}}</h1>
            {{-- 8/23/22 make the p below href where applicable --}}
            <p class="text-sm font-medium text-gray-500">
                {{$p}}
            </p>
        </div>
    </div>

    @if(!empty($rightButtonHref))
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse hidden md:block lg:flex-row-reverse lg:justify-end lg:space-x-reverse lg:space-y-0 lg:space-x-3 md:mt-0 md:flex-row md:space-x-3"
            >
            <x-cards.button href="{{$rightButtonHref}}">
                {{$rightButtonText}}
            </x-cards.button>
        </div>
    @endif
</div>

{{-- <br> --}}
<div class="items-center justify-between px-6 py-3 border-t border-gray-200 bg-gray-50 lg:flex">
    {{-- sm:flex-1 sm:flex sm:items-center sm:justify-between --}}
    <div class="flex items-center justify-between flex-1">
        {{$slot}}
    </div>
</div>

@if(isset($bottom))
    <div class="items-center justify-between px-6 py-3 text-center border-t border-gray-200 bg-gray-50 lg:flex">
        <div class="flex items-center justify-between flex-1 text-center">
            {{$bottom}}
        </div>
    </div>
@endif

<div {{ $attributes->merge(['class' => 'mx-auto']) }}>
    <div class="bg-white overflow-hidden shadow-md sm:rounded-lg">
        {{$slot}}
    </div>
</div>
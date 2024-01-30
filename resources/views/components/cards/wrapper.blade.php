<div {{ $attributes->merge(['class' => 'mx-auto']) }}>
    <div class="overflow-hidden bg-white shadow-md sm:rounded-lg">
        {{$slot}}
    </div>
</div>

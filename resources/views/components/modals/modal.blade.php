<div
    x-data="{open: @entangle('modal_show')}" 
    x-show="open"
    class="flex justify-center"
    >

    <!-- Modal -->
    <div
        x-show="open"
        style="display: none"
        x-on:keydown.escape.prevent.stop="open = false"
        role="dialog"
        aria-modal="true"
        {{-- x-id="['modal-title']"
        :aria-labelledby="$id('modal-title')" --}}
        class="fixed inset-0 overflow-y-auto"
        >
        <!-- Overlay -->
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50"></div>

        <!-- Panel -->
        <div
            x-show="open" 
            x-transition
            x-on:click="open = false"
            class="relative min-h-screen flex items-center justify-center p-4"
            >
            <div
                x-on:click.stop
                x-trap.noscroll.inert="open"
                class="relative max-w-2xl w-full bg-white rounded-lg shadow-lg overflow-y-auto {{$attributes['class']}}"
                >

                {{$slot}}      
            </div>
        </div>
    </div>
</div>
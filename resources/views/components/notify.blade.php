<div
    x-data="{
        notifications: [],
        add(e) {
            this.notifications.push({
                id: e.timeStamp,
                type: e.detail.type,
                content: e.detail.content,
                route: e.detail.route,
            })
        },
        remove(notification) {
            this.notifications = this.notifications.filter(i => i.id !== notification.id)
        },
    }"
    @notify.window="add($event)"
    class="fixed z-50 flex flex-col w-full max-w-xs pb-4 pr-4 space-y-4 top-14 lg:top-0 right-5 sm:justify-start"
    role="status"
    aria-live="polite"
    >
    <!-- Notification -->
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-data="{
                show: false,
                init() {
                    this.$nextTick(() => this.show = true)

                    setTimeout(() => this.transitionOut(), 5000)
                },
                transitionOut() {
                    this.show = false

                    setTimeout(() => this.remove(this.notification), 500)
                },
            }"
            x-show="show"
            x-transition.duration.500ms
            class="relative w-full max-w-sm py-4 pl-6 pr-4 bg-white border border-gray-200 rounded-md shadow-lg pointer-events-auto"
            >
            <div class="flex items-start">
                <!-- Icons -->
                <div x-show="notification.type === 'info'" class="flex-shrink-0">
                    <span aria-hidden="true" class="inline-flex items-center justify-center w-6 h-6 text-xl font-bold text-gray-400 border-2 border-gray-400 rounded-full">!</span>
                    <span class="sr-only">Information:</span>
                </div>

                <div x-show="notification.type === 'success'" class="flex-shrink-0">
                    <span aria-hidden="true" class="inline-flex items-center justify-center w-6 h-6 text-lg font-bold text-green-600 border-2 border-green-600 rounded-full">&check;</span>
                    <span class="sr-only">Success:</span>
                </div>

                <div x-show="notification.type === 'error'" class="flex-shrink-0">
                    <span aria-hidden="true" class="inline-flex items-center justify-center w-6 h-6 text-lg font-bold text-red-600 border-2 border-red-600 rounded-full">&times;</span>
                    <span class="sr-only">Error:</span>
                </div>

                <!-- Text -->
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p x-text="notification.content"  class="text-sm font-medium leading-5 text-gray-900"></p>
                </div>

                <!-- Href -->
                {{-- if notification.route isset... --}}

                <div x-show="notification.route" class="ml-3 w-0 flex-1 pt-0.5">
                    <a :href="'/' + notification.route" target="_blank" x-text="'View'" class="text-sm font-medium leading-5 text-gray-900"></a>
                </div>

                <!-- Remove button -->
                <div class="flex flex-shrink-0 ml-4">
                    <button @click="transitionOut()" type="button" class="inline-flex text-gray-400">
                        <svg aria-hidden class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="sr-only">Close notification</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>



{{-- <div class="max-w-2xl mx-auto mb-3">
    <!-- Global notification live region, render this permanently at the end of the document -->

    <div class="w-full overflow-hidden text-green-800 bg-green-100 rounded-lg shadow-lg pointer-events-auto">
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <!-- Heroicon name: outline/check-circle -->
                    <svg class="w-6 h-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                    <p class="text-sm font-bold">Successfully saved!</p>
                    <p class="mt-1 text-sm">Anyone with a link can now view this file.</p>
                </div>
                <div class="flex flex-shrink-0 ml-4">

                    <button type="button" class="inline-flex text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span class="sr-only">Close</span>
                    <!-- Heroicon name: mini/x-mark -->
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> --}}

 {{-- class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{$bubbleColor}}-100 text-{{$bubbleColor}}-800" --}}

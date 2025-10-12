<script setup lang="ts">
    import { ref, onMounted } from 'vue'
    import { X } from 'lucide-vue-next'

    const show = ref(false)
    const connectionState = ref<string>('checking')
    const errors = ref<string[]>([])
    const config = ref<any>({})

    onMounted(() => {
        const echo = (window as any).Echo

        if (!echo) {
            connectionState.value = 'error'
            errors.value.push('Echo instance not found')
            return
        }

        // Get configuration
        config.value = {
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
            wsHost: import.meta.env.VITE_PUSHER_HOST,
            wsPort: import.meta.env.VITE_PUSHER_PORT,
            forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
        }

        const pusher = echo.connector?.pusher

        if (!pusher) {
            connectionState.value = 'error'
            errors.value.push('Pusher connection not found')
            return
        }

        // Update state
        connectionState.value = pusher.connection.state

        // Listen to events
        pusher.connection.bind('state_change', (states: any) => {
            console.log('Pusher state changed:', states)
            connectionState.value = states.current
        })

        pusher.connection.bind('error', (err: any) => {
            errors.value.push(err.error?.data?.message || err.message || 'Unknown error')
        })
    })

    function toggleDebug() {
        show.value = !show.value
    }
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50">
        <!-- Toggle button -->
        <button @click="toggleDebug"
            class="bg-gray-800 text-white px-3 py-2 rounded-lg shadow-lg hover:bg-gray-700 transition-colors text-sm font-medium">
            <span v-if="connectionState === 'connected'" class="text-green-400">● </span>
            <span v-else-if="connectionState === 'connecting'" class="text-yellow-400">● </span>
            <span v-else class="text-red-400">● </span>
            Pusher {{ show ? 'Debug' : '' }}
        </button>

        <!-- Debug panel -->
        <div v-if="show"
            class="absolute bottom-12 right-0 bg-white border border-gray-200 rounded-lg shadow-xl w-80 max-h-96 overflow-auto">
            <div class="sticky top-0 bg-gray-50 border-b border-gray-200 p-3 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Pusher Debug</h3>
                <button @click="show = false" class="text-gray-500 hover:text-gray-700">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="p-3 space-y-3">
                <!-- Connection Status -->
                <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Connection Status</div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full" :class="{
                            'bg-green-500': connectionState === 'connected',
                            'bg-yellow-500': connectionState === 'connecting',
                            'bg-red-500': connectionState === 'failed' || connectionState === 'error',
                            'bg-gray-400': connectionState === 'disconnected',
                        }"></div>
                        <span class="text-sm font-mono">{{ connectionState }}</span>
                    </div>
                </div>

                <!-- Configuration -->
                <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Configuration</div>
                    <div class="text-xs font-mono bg-gray-50 p-2 rounded border border-gray-200 space-y-1">
                        <div v-if="config.key">
                            <span class="text-gray-600">Key:</span>
                            <span class="text-gray-900">{{ config.key?.substring(0, 10) }}...</span>
                        </div>
                        <div v-if="config.cluster">
                            <span class="text-gray-600">Cluster:</span>
                            <span class="text-gray-900">{{ config.cluster }}</span>
                        </div>
                        <div v-if="config.wsHost">
                            <span class="text-gray-600">Host:</span>
                            <span class="text-gray-900">{{ config.wsHost }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">TLS:</span>
                            <span class="text-gray-900">{{ config.forceTLS ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Errors -->
                <div v-if="errors.length > 0">
                    <div class="text-xs font-medium text-red-600 mb-1">Errors</div>
                    <div class="text-xs bg-red-50 p-2 rounded border border-red-200 space-y-1">
                        <div v-for="(error, idx) in errors" :key="idx" class="text-red-700">
                            {{ error }}
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div v-if="connectionState !== 'connected'"
                    class="text-xs text-gray-600 bg-blue-50 p-2 rounded border border-blue-200">
                    <div class="font-medium text-blue-900 mb-1">Troubleshooting:</div>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Check .env file has VITE_PUSHER_* variables</li>
                        <li>Run: npm run dev or npm run build</li>
                        <li>Verify BROADCAST_CONNECTION=pusher</li>
                        <li>Check Pusher dashboard for connection</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

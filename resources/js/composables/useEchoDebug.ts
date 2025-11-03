import { ref, onMounted } from 'vue'

export function useEchoDebug() {
    const isConnected = ref(false)
    const connectionState = ref<string>('initializing')
    const errors = ref<string[]>([])

    onMounted(() => {
        const echo = (window as any).Echo

        if (!echo) {
            errors.value.push('Echo instance not found')
            connectionState.value = 'error'
            return
        }

        // Get Pusher connection from Echo
        const pusher = echo.connector?.pusher

        if (!pusher) {
            errors.value.push('Pusher connection not found')
            connectionState.value = 'error'
            return
        }

        // Listen to Pusher connection events
        pusher.connection.bind('connecting', () => {
            console.log('🔄 Pusher: Connecting...')
            connectionState.value = 'connecting'
        })

        pusher.connection.bind('connected', () => {
            console.log('✅ Pusher: Connected!')
            connectionState.value = 'connected'
            isConnected.value = true
        })

        pusher.connection.bind('unavailable', () => {
            console.log('❌ Pusher: Unavailable')
            connectionState.value = 'unavailable'
            errors.value.push('Pusher unavailable')
        })

        pusher.connection.bind('failed', () => {
            console.log('❌ Pusher: Failed')
            connectionState.value = 'failed'
            errors.value.push('Pusher connection failed')
        })

        pusher.connection.bind('disconnected', () => {
            console.log('⚠️ Pusher: Disconnected')
            connectionState.value = 'disconnected'
            isConnected.value = false
        })

        pusher.connection.bind('error', (err: any) => {
            console.error('❌ Pusher error:', err)
            errors.value.push(`Pusher error: ${err.error?.data?.message || err.message || 'Unknown'}`)
        })

        // Log current state
        console.log('📊 Pusher current state:', pusher.connection.state)
        connectionState.value = pusher.connection.state
    })

    return {
        isConnected,
        connectionState,
        errors,
    }
}

import { ref, onUnmounted, type Ref } from 'vue'
import { fetchLatestPositions, type LatestPosition } from '../api'

/**
 * Composable for subscribing to realtime vessel position updates via Reverb WebSocket.
 * Falls back to REST polling when WebSocket is unavailable or fails.
 *
 * @param onUpdate Callback invoked with each updated position
 * @returns reactive connection status and resync function
 */
export function useRealtimePositions(
  onUpdate: (position: LatestPosition) => void,
): {
  status: Ref<'connecting' | 'connected' | 'disconnected' | 'fallback'>
  resync: () => Promise<void>
} {
  const status = ref<'connecting' | 'connected' | 'disconnected' | 'fallback'>(
    'connecting',
  )
  let ws: WebSocket | null = null
  let pollTimer: ReturnType<typeof setInterval> | null = null
  let reconnectTimer: ReturnType<typeof setTimeout> | null = null
  let reconnectAttempts = 0
  const MAX_RECONNECT_ATTEMPTS = 5
  const POLL_INTERVAL = 30_000

  const reverbHost =
    import.meta.env.VITE_REVERB_HOST ?? window.location.hostname
  const reverbPort = import.meta.env.VITE_REVERB_PORT ?? '8080'
  const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'ws'
  const reverbAppKey = import.meta.env.VITE_REVERB_APP_KEY ?? ''
  const wsUrl = `${reverbScheme}://${reverbHost}:${reverbPort}/app/${reverbAppKey}`

  function startPolling(): void {
    status.value = 'fallback'
    if (pollTimer) clearInterval(pollTimer)
    pollTimer = setInterval(async () => {
      try {
        const positions = await fetchLatestPositions()
        for (const p of positions) onUpdate(p)
      } catch {
        // Silent fail — will retry next interval
      }
    }, POLL_INTERVAL)
  }

  function stopPolling(): void {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  }

  function connect(): void {
    if (!reverbAppKey) {
      startPolling()
      return
    }

    try {
      ws = new WebSocket(wsUrl)
    } catch {
      startPolling()
      return
    }

    ws.onopen = () => {
      status.value = 'connected'
      reconnectAttempts = 0
      stopPolling()
      // Subscribe to channel
      ws?.send(
        JSON.stringify({
          event: 'pusher:subscribe',
          data: { channel: 'vessel-positions' },
        }),
      )
    }

    ws.onmessage = (event) => {
      try {
        const msg = JSON.parse(event.data)
        if (msg.event === 'position-updated' && msg.data) {
          onUpdate(msg.data as LatestPosition)
        }
      } catch {
        // Ignore malformed messages
      }
    }

    ws.onerror = () => {
      status.value = 'disconnected'
    }

    ws.onclose = () => {
      status.value = 'disconnected'
      ws = null

      reconnectAttempts++
      if (reconnectAttempts <= MAX_RECONNECT_ATTEMPTS) {
        const delay = Math.min(1000 * 2 ** reconnectAttempts, 30_000)
        reconnectTimer = setTimeout(connect, delay)
      } else {
        // Fall back to REST polling after max reconnect attempts
        startPolling()
      }
    }
  }

  async function resync(): Promise<void> {
    try {
      const positions = await fetchLatestPositions()
      for (const p of positions) onUpdate(p)
    } catch {
      // Silent fail
    }
  }

  connect()

  onUnmounted(() => {
    if (ws) {
      ws.close()
      ws = null
    }
    if (reconnectTimer) clearTimeout(reconnectTimer)
    stopPolling()
  })

  return { status, resync }
}

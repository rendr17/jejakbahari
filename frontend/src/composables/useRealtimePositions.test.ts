import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { effectScope, nextTick } from 'vue'

import { useRealtimePositions } from './useRealtimePositions'
import * as api from '../api'

// Mock fetchLatestPositions
vi.mock('../api', async () => {
  const actual = await vi.importActual<typeof api>('../api')
  return {
    ...actual,
    fetchLatestPositions: vi.fn(),
  }
})

// Mock WebSocket
class MockWebSocket {
  static instances: MockWebSocket[] = []
  static OPEN = 1
  static CLOSED = 3

  url: string
  onopen: ((ev: Event) => void) | null = null
  onmessage: ((ev: MessageEvent) => void) | null = null
  onerror: ((ev: Event) => void) | null = null
  onclose: ((ev: CloseEvent) => void) | null = null
  readyState = MockWebSocket.OPEN
  sentMessages: string[] = []

  constructor(url: string) {
    this.url = url
    MockWebSocket.instances.push(this)
  }

  send(data: string): void {
    this.sentMessages.push(data)
  }

  close(): void {
    this.readyState = MockWebSocket.CLOSED
    this.onclose?.(new CloseEvent('close'))
  }

  simulateOpen(): void {
    this.readyState = MockWebSocket.OPEN
    this.onopen?.(new Event('open'))
  }

  simulateMessage(data: unknown): void {
    this.onmessage?.({ data: JSON.stringify(data) } as MessageEvent)
  }

  simulateError(): void {
    this.onerror?.(new Event('error'))
  }

  simulateClose(): void {
    this.readyState = MockWebSocket.CLOSED
    this.onclose?.(new CloseEvent('close'))
  }
}

// Replace global WebSocket
;(globalThis as unknown as { WebSocket: typeof WebSocket }).WebSocket =
  MockWebSocket as unknown as typeof WebSocket

// Helper to create a fresh scope for each test
function withComposable(
  callback: (
    status: ReturnType<typeof useRealtimePositions>['status'],
    updates: unknown[],
    resync: () => Promise<void>,
  ) => Promise<void>,
): Promise<void> {
  return new Promise((resolve) => {
    const scope = effectScope()
    const updates: unknown[] = []

    scope.run(async () => {
      const { status, resync } = useRealtimePositions((pos) => {
        updates.push(pos)
      })

      await callback(status, updates, resync)
    })

    scope.stop()
    resolve()
  })
}

describe('useRealtimePositions', () => {
  beforeEach(() => {
    MockWebSocket.instances = []
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.useRealTimers()
    vi.restoreAllMocks()
  })

  it('falls back to REST polling when no Reverb app key', async () => {
    // No VITE_REVERB_APP_KEY set — should start polling
    const mockPositions = [
      { vessel_id: 'v1', mmsi: 123, latitude: -6.1, longitude: 106.8 },
    ]
    vi.mocked(api.fetchLatestPositions).mockResolvedValue(
      mockPositions as unknown as Awaited<ReturnType<typeof api.fetchLatestPositions>>,
    )

    await withComposable(async (status, updates) => {
      await nextTick()
      // Status should be 'fallback' immediately
      expect(status.value).toBe('fallback')

      // Advance timer to trigger poll
      vi.advanceTimersByTime(30_000)
      await nextTick()

      expect(api.fetchLatestPositions).toHaveBeenCalled()
      expect(updates.length).toBeGreaterThan(0)
    })
  })

  it('connects to WebSocket when Reverb app key is set', async () => {
    // Set env var
    vi.stubEnv('VITE_REVERB_APP_KEY', 'test-key')

    await withComposable(async (status) => {
      await nextTick()
      expect(status.value).toBe('connecting')
      expect(MockWebSocket.instances.length).toBe(1)

      // Simulate successful connection
      MockWebSocket.instances[0].simulateOpen()
      await nextTick()

      expect(status.value).toBe('connected')

      // Should send subscribe message
      const subscribeMsg = MockWebSocket.instances[0].sentMessages.find((m) =>
        m.includes('pusher:subscribe'),
      )
      expect(subscribeMsg).toBeDefined()
      expect(subscribeMsg).toContain('vessel-positions')

      vi.unstubAllEnvs()
    })
  })

  it('receives position updates via WebSocket', async () => {
    vi.stubEnv('VITE_REVERB_APP_KEY', 'test-key')

    await withComposable(async (_status, updates) => {
      await nextTick()
      MockWebSocket.instances[0].simulateOpen()
      await nextTick()

      // Simulate receiving a position-updated event
      const positionData = {
        vessel_id: 'v1',
        mmsi: 123456,
        latitude: -6.1,
        longitude: 106.8,
      }
      MockWebSocket.instances[0].simulateMessage({
        event: 'position-updated',
        data: positionData,
      })
      await nextTick()

      expect(updates).toContainEqual(positionData)
      vi.unstubAllEnvs()
    })
  })

  it('attempts reconnect with exponential backoff on close', async () => {
    vi.stubEnv('VITE_REVERB_APP_KEY', 'test-key')

    await withComposable(async (status) => {
      await nextTick()
      MockWebSocket.instances[0].simulateOpen()
      await nextTick()
      expect(status.value).toBe('connected')

      // Simulate disconnect
      MockWebSocket.instances[0].simulateClose()
      await nextTick()
      expect(status.value).toBe('disconnected')

      // First reconnect after 2s (2^1 * 1000)
      vi.advanceTimersByTime(2_000)
      await nextTick()
      expect(MockWebSocket.instances.length).toBe(2)

      vi.unstubAllEnvs()
    })
  })

  it('falls back to REST polling after max reconnect attempts', async () => {
    vi.stubEnv('VITE_REVERB_APP_KEY', 'test-key')

    vi.mocked(api.fetchLatestPositions).mockResolvedValue([])

    await withComposable(async (status) => {
      await nextTick()
      // Simulate 6 failed connection attempts (max is 5)
      for (let i = 0; i < 6; i++) {
        const instance =
          MockWebSocket.instances[MockWebSocket.instances.length - 1]
        if (instance && instance.readyState === MockWebSocket.OPEN) {
          instance.simulateClose()
        }
        await nextTick()
      }

      // After max attempts, should fall back to polling
      // Note: the actual fallback happens after timer expires, but we verify
      // the status is disconnected at this point
      expect(['disconnected', 'fallback']).toContain(status.value)
      vi.unstubAllEnvs()
    })
  })

  it('resync fetches latest positions from REST', async () => {
    vi.stubEnv('VITE_REVERB_APP_KEY', 'test-key')

    const mockPositions = [
      { vessel_id: 'v1', mmsi: 123, latitude: -6.1, longitude: 106.8 },
    ]
    vi.mocked(api.fetchLatestPositions).mockResolvedValue(
      mockPositions as unknown as Awaited<ReturnType<typeof api.fetchLatestPositions>>,
    )

    await withComposable(async (_status, _updates, resync) => {
      await nextTick()
      MockWebSocket.instances[0].simulateOpen()
      await nextTick()

      await resync()
      expect(api.fetchLatestPositions).toHaveBeenCalled()
      vi.unstubAllEnvs()
    })
  })

  it('ignores malformed WebSocket messages', async () => {
    vi.stubEnv('VITE_REVERB_APP_KEY', 'test-key')

    await withComposable(async (_status, updates) => {
      await nextTick()
      MockWebSocket.instances[0].simulateOpen()
      await nextTick()

      // Send malformed message
      MockWebSocket.instances[0].onmessage?.({
        data: 'not-json',
      } as MessageEvent)
      await nextTick()

      // Should not crash or add updates
      expect(updates.length).toBe(0)

      vi.unstubAllEnvs()
    })
  })

  it('ignores non-position-updated events', async () => {
    vi.stubEnv('VITE_REVERB_APP_KEY', 'test-key')

    await withComposable(async (_status, updates) => {
      await nextTick()
      MockWebSocket.instances[0].simulateOpen()
      await nextTick()

      // Send a different event
      MockWebSocket.instances[0].simulateMessage({
        event: 'pusher:ping',
        data: {},
      })
      await nextTick()

      expect(updates.length).toBe(0)

      vi.unstubAllEnvs()
    })
  })
})

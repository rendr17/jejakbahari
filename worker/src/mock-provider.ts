import { EventEmitter } from 'events'
import type { ProviderAdapter, ProviderConfig } from './provider.js'
import { normalizeAisMessage } from './provider.js'
import type { NormalizedPosition } from './schemas.js'
import type { Logger } from './logger.js'

const MOCK_MMSI_LIST = ['525123456', '525789012', '525345678']

const MOCK_PORTS = [
  { lat: -6.0008, lon: 105.9961, name: 'MERAK' },
  { lat: -5.9306, lon: 106.0236, name: 'BAKAUHEUNI' },
  { lat: -7.8333, lon: 113.3333, name: 'KETAPANG' },
  { lat: -8.1167, lon: 114.3833, name: 'GILIMANUK' },
  { lat: -0.8833, lon: 119.8667, name: 'PANTOLOAN' },
]

export class MockProvider extends EventEmitter implements ProviderAdapter {
  private _isConnected = false
  private interval: ReturnType<typeof setInterval> | null = null
  private vesselPositions = new Map<
    string,
    { lat: number; lon: number; routeIndex: number; forward: boolean }
  >()

  constructor(
    private readonly config: ProviderConfig,
    private readonly logger: Logger,
  ) {
    super()
  }

  get isConnected(): boolean {
    return this._isConnected
  }

  async connect(): Promise<void> {
    this._isConnected = true
    this.logger.info('mock_provider_connected', {
      mmsi_count: MOCK_MMSI_LIST.length,
      route_count: MOCK_PORTS.length,
    })

    for (const mmsi of MOCK_MMSI_LIST) {
      const startPort =
        MOCK_PORTS[Math.floor(Math.random() * MOCK_PORTS.length)]
      if (!startPort) continue
      this.vesselPositions.set(mmsi, {
        lat: startPort.lat,
        lon: startPort.lon,
        routeIndex: 0,
        forward: Math.random() > 0.5,
      })
    }

    this.interval = setInterval(() => this.emitPosition(), 5000)
  }

  private emitPosition(): void {
    for (const mmsi of MOCK_MMSI_LIST) {
      const pos = this.vesselPositions.get(mmsi)
      if (!pos) continue

      const targetPort = MOCK_PORTS[pos.routeIndex % MOCK_PORTS.length]
      if (!targetPort) continue
      const dLat = (targetPort.lat - pos.lat) * 0.1
      const dLon = (targetPort.lon - pos.lon) * 0.1

      pos.lat += dLat + (Math.random() - 0.5) * 0.001
      pos.lon += dLon + (Math.random() - 0.5) * 0.001

      const distance = Math.sqrt(dLat * dLat + dLon * dLon)
      if (distance < 0.01) {
        pos.routeIndex++
      }

      const rawMessage = {
        MMSI: mmsi,
        Latitude: pos.lat,
        Longitude: pos.lon,
        SOG: Math.abs(distance) * 100,
        COG: (Math.atan2(dLon, dLat) * 180) / Math.PI + (360 % 360),
        Heading:
          Math.round((Math.atan2(dLon, dLat) * 180) / Math.PI + 360) % 360,
        NavigationStatus: 'UNDER_WAY_USING_ENGINE',
        Destination: targetPort.name,
        Timestamp: new Date().toISOString(),
        MessageId: `mock-${mmsi}-${Date.now()}`,
      }

      const normalized = normalizeAisMessage(
        rawMessage,
        this.config.providerName,
      )
      if (normalized) {
        this.emit('position', normalized)
      }
    }
  }

  async disconnect(): Promise<void> {
    if (this.interval) {
      clearInterval(this.interval)
      this.interval = null
    }
    this._isConnected = false
    this.logger.info('mock_provider_disconnected')
  }

  onMessage(handler: (position: NormalizedPosition) => void): void {
    this.on('position', handler)
  }

  onError(handler: (error: Error) => void): void {
    this.on('error', handler)
  }

  onClose(handler: (code: number, reason: string) => void): void {
    this.on('close', handler)
  }
}

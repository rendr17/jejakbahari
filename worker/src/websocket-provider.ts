import { EventEmitter } from 'events'
import { WebSocket } from 'ws'
import type { ProviderAdapter, ProviderConfig } from './provider.js'
import { normalizeAisMessage } from './provider.js'
import type { NormalizedPosition } from './schemas.js'
import type { Logger } from './logger.js'

export class WebSocketProvider extends EventEmitter implements ProviderAdapter {
  private ws: WebSocket | null = null
  private _isConnected = false

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
    return new Promise((resolve, reject) => {
      this.logger.info('provider_connecting', { url: this.config.url })

      this.ws = new WebSocket(this.config.url, {
        headers: { Authorization: `Bearer ${this.config.apiKey}` },
      })

      this.ws.on('open', () => {
        this._isConnected = true
        this.logger.info('provider_connected')
        this.sendSubscription()
        resolve()
      })

      this.ws.on('message', (data: Buffer) => {
        try {
          const raw = JSON.parse(data.toString())
          const positions = Array.isArray(raw) ? raw : [raw]

          for (const pos of positions) {
            const normalized = normalizeAisMessage(
              pos,
              this.config.providerName,
            )
            if (normalized) {
              this.emit('position', normalized)
            }
          }
        } catch {
          this.logger.warn('provider_parse_error', {
            raw: data.toString().slice(0, 200),
          })
        }
      })

      this.ws.on('error', (error: Error) => {
        this.logger.error('provider_error', { error: error.message })
        if (!this._isConnected) {
          reject(error)
        }
        this.emit('error', error)
      })

      this.ws.on('close', (code: number, reason: Buffer) => {
        this._isConnected = false
        const reasonStr = reason.toString()
        this.logger.warn('provider_disconnected', { code, reason: reasonStr })
        this.emit('close', code, reasonStr)
      })
    })
  }

  private sendSubscription(): void {
    if (!this.ws || !this._isConnected) return

    const subscribeMsg = {
      APIKey: this.config.apiKey,
      BoundingBoxes: this.config.boundingBoxes.split(';').filter(Boolean),
      Filters: [
        'MMSI',
        'Latitude',
        'Longitude',
        'SOG',
        'COG',
        'Heading',
        'NavigationStatus',
        'Destination',
        'Timestamp',
        'MessageId',
      ],
      FilterMessageTypes: ['PositionReport'],
    }

    this.ws.send(JSON.stringify(subscribeMsg))
    this.logger.info('provider_subscribed')
  }

  async disconnect(): Promise<void> {
    if (this.ws) {
      this.ws.close()
      this.ws = null
      this._isConnected = false
    }
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

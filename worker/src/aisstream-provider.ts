import { EventEmitter } from 'events'
import { WebSocket } from 'ws'
import type { ProviderAdapter, ProviderConfig } from './provider.js'
import { normalizeAisMessage } from './provider.js'
import type { NormalizedPosition } from './schemas.js'
import type { Logger } from './logger.js'

interface AisStreamPositionReport {
  MessageID?: number
  UserID?: number
  Sog?: number
  Cog?: number
  TrueHeading?: number
  Valid?: boolean
}

interface AisStreamEnvelope {
  MessageType: string
  MetaData: {
    MMSI: number
    ShipName?: string
    Latitude?: number
    Longitude?: number
  }
  Message: {
    PositionReport?: AisStreamPositionReport
    ShipStaticData?: {
      Destination?: string
    }
    StandardClassBPositionReport?: AisStreamPositionReport
    ExtendedClassBPositionReport?: AisStreamPositionReport
    CompressionEnabled?: boolean
  }
}

export class AisStreamProvider extends EventEmitter implements ProviderAdapter {
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
      this.logger.info('aisstream_connecting', { url: this.config.url })

      this.ws = new WebSocket(this.config.url, {
        perMessageDeflate: true,
      })

      this.ws.on('open', () => {
        this._isConnected = true
        this.logger.info('aisstream_connected')
        this.sendSubscription()
        resolve()
      })

      this.ws.on('message', (data: Buffer) => {
        try {
          const envelope: AisStreamEnvelope = JSON.parse(data.toString())

          if (envelope.MessageType === 'SubscriptionConfirmation') {
            this.logger.info('aisstream_subscribed', {
              compression: envelope.Message?.['CompressionEnabled'] ?? false,
            })
            return
          }

          const position = this.extractPosition(envelope)
          if (position) {
            const normalized = normalizeAisMessage(
              position,
              this.config.providerName,
            )
            if (normalized) {
              this.emit('position', normalized)
            }
          }
        } catch {
          this.logger.warn('aisstream_parse_error', {
            raw: data.toString().slice(0, 200),
          })
        }
      })

      this.ws.on('error', (error: Error) => {
        this.logger.error('aisstream_error', { error: error.message })
        if (!this._isConnected) {
          reject(error)
        }
        this.emit('error', error)
      })

      this.ws.on('close', (code: number, reason: Buffer) => {
        this._isConnected = false
        const reasonStr = reason.toString()
        this.logger.warn('aisstream_disconnected', { code, reason: reasonStr })
        this.emit('close', code, reasonStr)
      })
    })
  }

  private sendSubscription(): void {
    if (!this.ws || !this._isConnected) return

    const boxes = this.config.boundingBoxes
      .split(';')
      .filter(Boolean)
      .map((box) => {
        const [latMin, lonMin, latMax, lonMax] = box.split(',').map(Number)
        return [
          [latMin, lonMin],
          [latMax, lonMax],
        ]
      })

    const subscribeMsg = {
      APIKey: this.config.apiKey,
      BoundingBoxes: boxes,
      FilterMessageTypes: ['PositionReport'],
    }

    this.ws.send(JSON.stringify(subscribeMsg))
  }

  private extractPosition(
    envelope: AisStreamEnvelope,
  ): Record<string, unknown> | null {
    const meta = envelope.MetaData
    if (!meta || meta.MMSI == null) return null

    if (meta.Latitude == null || meta.Longitude == null) return null

    const msg = envelope.Message
    const posReport =
      msg?.PositionReport ??
      msg?.StandardClassBPositionReport ??
      msg?.ExtendedClassBPositionReport

    const timestamp = new Date().toISOString()

    return {
      MMSI: String(meta.MMSI),
      Latitude: meta.Latitude,
      Longitude: meta.Longitude,
      SOG: posReport?.Sog ?? null,
      COG: posReport?.Cog ?? null,
      Heading: posReport?.TrueHeading ?? null,
      Destination: msg?.ShipStaticData?.Destination ?? null,
      Timestamp: timestamp,
      MessageID:
        msg?.PositionReport?.MessageID != null
          ? String(msg.PositionReport.MessageID)
          : null,
    }
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

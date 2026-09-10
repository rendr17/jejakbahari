import type { Logger } from './logger.js'
import type { NormalizedPosition, PositionDeliveryResult } from './schemas.js'

export interface DeliveryConfig {
  backendUrl: string
  internalToken: string
  maxRetries: number
  timeoutMs: number
}

export class DeliveryClient {
  constructor(
    private readonly config: DeliveryConfig,
    private readonly logger: Logger,
  ) {}

  async deliverPosition(
    position: NormalizedPosition,
  ): Promise<PositionDeliveryResult> {
    const url = `${this.config.backendUrl}/positions`
    let lastError: Error | null = null

    for (let attempt = 0; attempt <= this.config.maxRetries; attempt++) {
      try {
        const controller = new AbortController()
        const timeout = setTimeout(
          () => controller.abort(),
          this.config.timeoutMs,
        )

        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${this.config.internalToken}`,
          },
          body: JSON.stringify(position),
          signal: controller.signal,
        })

        clearTimeout(timeout)

        if (response.ok) {
          const body = await response.json()
          return body.data as PositionDeliveryResult
        }

        if (response.status >= 400 && response.status < 500) {
          const body = await response.json().catch(() => null)
          this.logger.warn('delivery_client_error', {
            status: response.status,
            mmsi: position.mmsi,
            error: body?.error?.code ?? 'UNKNOWN',
          })
          return { accepted: false, history_saved: false, geofence_events: [] }
        }

        lastError = new Error(`HTTP ${response.status}`)
        this.logger.warn('delivery_retry', {
          attempt: attempt + 1,
          status: response.status,
          mmsi: position.mmsi,
        })
      } catch (error) {
        lastError = error instanceof Error ? error : new Error(String(error))
        this.logger.warn('delivery_network_error', {
          attempt: attempt + 1,
          mmsi: position.mmsi,
          error: lastError.message,
        })
      }

      if (attempt < this.config.maxRetries) {
        const delay = Math.min(1000 * 2 ** attempt, 10000)
        await new Promise((resolve) => setTimeout(resolve, delay))
      }
    }

    this.logger.error('delivery_failed', {
      mmsi: position.mmsi,
      error: lastError?.message ?? 'unknown',
    })
    throw lastError ?? new Error('delivery failed')
  }

  async fetchWhitelist(): Promise<unknown> {
    const url = `${this.config.backendUrl}/vessel-whitelist`
    const controller = new AbortController()
    const timeout = setTimeout(() => controller.abort(), this.config.timeoutMs)

    try {
      const response = await fetch(url, {
        headers: { Authorization: `Bearer ${this.config.internalToken}` },
        signal: controller.signal,
      })

      clearTimeout(timeout)

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      return await response.json()
    } catch (error) {
      clearTimeout(timeout)
      throw error
    }
  }
}

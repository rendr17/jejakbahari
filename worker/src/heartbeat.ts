import type { Logger } from './logger.js'
import type { HeartbeatPayload } from './schemas.js'

export interface HeartbeatConfig {
  backendUrl: string
  internalToken: string
  intervalSeconds: number
  timeoutMs: number
}

export class HeartbeatSender {
  private timer: ReturnType<typeof setInterval> | null = null

  constructor(
    private readonly config: HeartbeatConfig,
    private readonly logger: Logger,
  ) {}

  start(getPayload: () => HeartbeatPayload): void {
    if (this.timer) {
      return
    }

    this.timer = setInterval(() => {
      this.send(getPayload()).catch((error) => {
        this.logger.warn('heartbeat_failed', {
          error: error instanceof Error ? error.message : String(error),
        })
      })
    }, this.config.intervalSeconds * 1000)
  }

  async sendFinal(payload: HeartbeatPayload): Promise<void> {
    await this.send(payload)
    this.stop()
  }

  stop(): void {
    if (this.timer) {
      clearInterval(this.timer)
      this.timer = null
    }
  }

  private async send(payload: HeartbeatPayload): Promise<void> {
    const url = `${this.config.backendUrl}/worker-heartbeat`
    const controller = new AbortController()
    const timeout = setTimeout(() => controller.abort(), this.config.timeoutMs)

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${this.config.internalToken}`,
        },
        body: JSON.stringify(payload),
        signal: controller.signal,
      })

      clearTimeout(timeout)

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }
    } catch (error) {
      clearTimeout(timeout)
      throw error
    }
  }
}

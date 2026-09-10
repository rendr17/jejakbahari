import { loadConfig } from './config.js'
import { Logger } from './logger.js'
import { ExponentialBackoff } from './backoff.js'
import { WhitelistCache } from './whitelist.js'
import { Deduplicator } from './dedup.js'
import { DeliveryClient } from './delivery.js'
import { HeartbeatSender } from './heartbeat.js'
import { WebSocketProvider } from './websocket-provider.js'
import { MockProvider } from './mock-provider.js'
import type { ProviderAdapter } from './provider.js'
import type { HeartbeatPayload } from './schemas.js'

export interface WorkerMetrics {
  messagesReceivedTotal: number
  messagesInvalidTotal: number
  messagesUnknownMmsiTotal: number
  messagesDuplicateTotal: number
  positionsDeliveredTotal: number
  deliveryFailuresTotal: number
  lastMessageTimestamp: string | null
}

export class Worker {
  private readonly logger: Logger
  private readonly backoff: ExponentialBackoff
  private readonly whitelist: WhitelistCache
  private readonly dedup: Deduplicator
  private readonly delivery: DeliveryClient
  private readonly heartbeat: HeartbeatSender
  private readonly provider: ProviderAdapter

  private running = false
  private deliveryQueue: Array<() => Promise<void>> = []

  private metrics: WorkerMetrics = {
    messagesReceivedTotal: 0,
    messagesInvalidTotal: 0,
    messagesUnknownMmsiTotal: 0,
    messagesDuplicateTotal: 0,
    positionsDeliveredTotal: 0,
    deliveryFailuresTotal: 0,
    lastMessageTimestamp: null,
  }

  constructor(private readonly config = loadConfig()) {
    this.logger = new Logger(config.WORKER_ID, config.LOG_LEVEL)
    this.backoff = new ExponentialBackoff(
      config.BACKOFF_MIN_MS,
      config.BACKOFF_MAX_MS,
    )
    this.whitelist = new WhitelistCache()
    this.dedup = new Deduplicator()

    const deliveryConfig = {
      backendUrl: `${config.BACKEND_INTERNAL_URL}/positions`,
      internalToken: config.BACKEND_INTERNAL_TOKEN,
      maxRetries: 3,
      timeoutMs: 10000,
    }
    // Fix: delivery needs the base URL for whitelist fetch
    this.delivery = new DeliveryClient(
      {
        ...deliveryConfig,
        backendUrl: config.BACKEND_INTERNAL_URL,
      },
      this.logger,
    )

    this.heartbeat = new HeartbeatSender(
      {
        backendUrl: config.BACKEND_INTERNAL_URL,
        internalToken: config.BACKEND_INTERNAL_TOKEN,
        intervalSeconds: 30,
        timeoutMs: 5000,
      },
      this.logger,
    )

    const providerConfig = {
      url: config.AIS_PROVIDER_URL,
      apiKey: config.AIS_PROVIDER_API_KEY,
      boundingBoxes: config.AIS_BOUNDING_BOXES,
      providerName: config.WORKER_ID,
    }

    this.provider =
      config.AIS_PROVIDER_TYPE === 'mock'
        ? new MockProvider(providerConfig, this.logger)
        : new WebSocketProvider(providerConfig, this.logger)
  }

  async start(): Promise<void> {
    this.logger.info('worker_starting')
    this.running = true

    await this.refreshWhitelist()
    if (this.whitelist.isEmpty()) {
      this.logger.warn('whitelist_empty_starting_anyway')
    }

    this.setupProviderHandlers()
    this.heartbeat.start(() => this.buildHeartbeatPayload())

    await this.connectLoop()

    this.setupShutdownHandlers()
  }

  private async refreshWhitelist(): Promise<void> {
    try {
      const raw = await this.delivery.fetchWhitelist()
      this.whitelist.update(raw)
      this.logger.info('whitelist_loaded', {
        count: this.whitelist.count,
        version: this.whitelist.version,
      })
    } catch (error) {
      this.logger.warn('whitelist_fetch_failed', {
        error: error instanceof Error ? error.message : String(error),
      })
    }
  }

  private setupProviderHandlers(): void {
    this.provider.onMessage((position) => {
      this.metrics.messagesReceivedTotal++
      this.metrics.lastMessageTimestamp = position.source_timestamp
      this.handlePosition(position)
    })

    this.provider.onError((error) => {
      this.logger.error('provider_error', { error: error.message })
    })

    this.provider.onClose(() => {
      if (this.running) {
        this.connectLoop().catch((error) => {
          this.logger.error('reconnect_failed', {
            error: error instanceof Error ? error.message : String(error),
          })
        })
      }
    })
  }

  private async connectLoop(): Promise<void> {
    while (this.running) {
      try {
        await this.provider.connect()
        this.backoff.reset()
        this.logger.info('provider_connected_waiting_for_messages')
        return
      } catch (error) {
        const delay = this.backoff.nextDelay()
        this.logger.warn('provider_connect_failed', {
          error: error instanceof Error ? error.message : String(error),
          retry_in_ms: delay,
        })

        if (this.running) {
          await new Promise((resolve) => setTimeout(resolve, delay))
        }
      }
    }
  }

  private handlePosition(
    position: import('./schemas.js').NormalizedPosition,
  ): void {
    if (!this.whitelist.has(position.mmsi)) {
      this.metrics.messagesUnknownMmsiTotal++
      return
    }

    if (
      this.dedup.isDuplicate(
        position.mmsi,
        position.source_timestamp,
        position.latitude,
        position.longitude,
        position.raw_message_id,
      )
    ) {
      this.metrics.messagesDuplicateTotal++
      return
    }

    this.enqueueDelivery(position)
  }

  private enqueueDelivery(
    position: import('./schemas.js').NormalizedPosition,
  ): void {
    if (this.deliveryQueue.length >= this.config.DELIVERY_QUEUE_MAX) {
      this.deliveryQueue.shift()
      this.logger.warn('queue_full_dropped_oldest', {
        queue_depth: this.deliveryQueue.length,
      })
    }

    this.deliveryQueue.push(async () => {
      try {
        await this.delivery.deliverPosition(position)
        this.metrics.positionsDeliveredTotal++
      } catch (error) {
        this.metrics.deliveryFailuresTotal++
        this.logger.error('delivery_failed', {
          mmsi: position.mmsi,
          error: error instanceof Error ? error.message : String(error),
        })
      }
    })

    this.processQueue()
  }

  private processing = false

  private async processQueue(): Promise<void> {
    if (this.processing) return
    this.processing = true

    while (this.deliveryQueue.length > 0) {
      const task = this.deliveryQueue.shift()
      if (task) {
        await task()
      }
    }

    this.processing = false
  }

  private buildHeartbeatPayload(): HeartbeatPayload {
    return {
      worker_id: this.config.WORKER_ID,
      status: this.provider.isConnected ? 'HEALTHY' : 'DEGRADED',
      provider_connected: this.provider.isConnected,
      messages_received_total: this.metrics.messagesReceivedTotal,
      positions_delivered_total: this.metrics.positionsDeliveredTotal,
      delivery_failures_total: this.metrics.deliveryFailuresTotal,
      queue_depth: this.deliveryQueue.length,
      last_message_timestamp: this.metrics.lastMessageTimestamp,
      whitelist_version: this.whitelist.version || null,
    }
  }

  private setupShutdownHandlers(): void {
    const shutdown = async () => {
      this.logger.info('worker_shutting_down')
      this.running = false

      await this.provider.disconnect()

      // Flush queue with timeout
      const flushPromise = this.processQueue()
      const timeoutPromise = new Promise((resolve) => setTimeout(resolve, 5000))
      await Promise.race([flushPromise, timeoutPromise])

      await this.heartbeat.sendFinal(this.buildHeartbeatPayload())
      this.logger.info('worker_stopped')
      process.exit(0)
    }

    process.on('SIGTERM', shutdown)
    process.on('SIGINT', shutdown)
  }
}

export type LogLevel = 'debug' | 'info' | 'warn' | 'error'

export interface LogEntry {
  level: LogLevel
  event: string
  worker_id?: string
  mmsi?: string
  provider?: string
  source_timestamp?: string
  message?: string
  [key: string]: unknown
}

const LEVEL_PRIORITY: Record<LogLevel, number> = {
  debug: 10,
  info: 20,
  warn: 30,
  error: 40,
}

export class Logger {
  constructor(
    private readonly _workerId: string,
    private readonly minLevel: LogLevel = 'info',
  ) {}

  get workerId(): string {
    return this._workerId
  }

  log(entry: LogEntry): void {
    if (LEVEL_PRIORITY[entry.level] < LEVEL_PRIORITY[this.minLevel]) {
      return
    }

    const fullEntry: LogEntry = {
      ...entry,
      worker_id: this._workerId,
    }

    console.log(JSON.stringify(fullEntry))
  }

  debug(event: string, extra: Record<string, unknown> = {}): void {
    this.log({ level: 'debug', event, ...extra })
  }

  info(event: string, extra: Record<string, unknown> = {}): void {
    this.log({ level: 'info', event, ...extra })
  }

  warn(event: string, extra: Record<string, unknown> = {}): void {
    this.log({ level: 'warn', event, ...extra })
  }

  error(event: string, extra: Record<string, unknown> = {}): void {
    this.log({ level: 'error', event, ...extra })
  }
}

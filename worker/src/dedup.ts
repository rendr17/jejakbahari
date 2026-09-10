interface DedupeEntry {
  key: string
  timestamp: number
}

export class Deduplicator {
  private seen = new Map<string, DedupeEntry>()
  private readonly maxEntries: number

  constructor(maxEntries = 10000) {
    this.maxEntries = maxEntries
  }

  isDuplicate(
    mmsi: string,
    sourceTimestamp: string,
    latitude: number,
    longitude: number,
    rawMessageId?: string | null,
  ): boolean {
    const key = this.buildKey(
      mmsi,
      sourceTimestamp,
      latitude,
      longitude,
      rawMessageId,
    )
    const now = Date.now()

    if (this.seen.has(key)) {
      return true
    }

    this.seen.set(key, { key, timestamp: now })
    this.evictOldEntries(now)

    return false
  }

  private buildKey(
    mmsi: string,
    sourceTimestamp: string,
    latitude: number,
    longitude: number,
    rawMessageId?: string | null,
  ): string {
    if (rawMessageId) {
      return `id:${rawMessageId}`
    }
    return `${mmsi}:${sourceTimestamp}:${latitude.toFixed(6)}:${longitude.toFixed(6)}`
  }

  private evictOldEntries(now: number): void {
    if (this.seen.size <= this.maxEntries) {
      return
    }

    const ttlMs = 5 * 60 * 1000
    for (const [key, entry] of this.seen) {
      if (now - entry.timestamp > ttlMs) {
        this.seen.delete(key)
      }
    }

    if (this.seen.size > this.maxEntries) {
      const sorted = [...this.seen.entries()].sort(
        (a, b) => a[1].timestamp - b[1].timestamp,
      )
      const toRemove = this.seen.size - this.maxEntries
      for (let i = 0; i < toRemove; i++) {
        const entry = sorted[i]
        if (entry) {
          this.seen.delete(entry[0])
        }
      }
    }
  }

  clear(): void {
    this.seen.clear()
  }

  get size(): number {
    return this.seen.size
  }
}

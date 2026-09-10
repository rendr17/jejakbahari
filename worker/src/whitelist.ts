import { whitelistResponseSchema } from './schemas.js'

export class WhitelistCache {
  private mmsiSet = new Set<string>()
  private versionHash = ''
  private lastUpdated: Date | null = null

  get count(): number {
    return this.mmsiSet.size
  }

  get version(): string {
    return this.versionHash
  }

  get ageSeconds(): number {
    if (!this.lastUpdated) return Infinity
    return Math.round((Date.now() - this.lastUpdated.getTime()) / 1000)
  }

  has(mmsi: string): boolean {
    return this.mmsiSet.has(mmsi)
  }

  update(rawData: unknown): void {
    const parsed = whitelistResponseSchema.parse(rawData)
    this.mmsiSet = new Set(parsed.data.mmsi_list.map((item) => item.mmsi))
    this.versionHash = parsed.data.version_hash
    this.lastUpdated = new Date()
  }

  isStale(refreshSeconds: number): boolean {
    return this.ageSeconds >= refreshSeconds
  }

  isEmpty(): boolean {
    return this.mmsiSet.size === 0
  }
}

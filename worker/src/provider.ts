import { aisPositionSchema, type NormalizedPosition } from './schemas.js'

export interface ProviderAdapter {
  connect(): Promise<void>
  disconnect(): Promise<void>
  onMessage(handler: (position: NormalizedPosition) => void): void
  onError(handler: (error: Error) => void): void
  onClose(handler: (code: number, reason: string) => void): void
  get isConnected(): boolean
}

export interface ProviderConfig {
  url: string
  apiKey: string
  boundingBoxes: string
  providerName: string
}

export function normalizeAisMessage(
  raw: unknown,
  providerName: string,
): NormalizedPosition | null {
  const parsed = aisPositionSchema.safeParse(raw)
  if (!parsed.success) {
    return null
  }

  const ais = parsed.data
  const mmsi = String(ais.MMSI)
  const sourceTimestamp =
    typeof ais.Timestamp === 'number'
      ? new Date(ais.Timestamp * 1000).toISOString()
      : ais.Timestamp

  return {
    mmsi,
    latitude: ais.Latitude,
    longitude: ais.Longitude,
    sog_knots: ais.SOG ?? null,
    cog_degrees: ais.COG ?? null,
    heading_degrees: ais.Heading ?? null,
    nav_status: ais.NavigationStatus ?? null,
    destination_text: ais.Destination ?? null,
    source_timestamp: sourceTimestamp,
    received_at: new Date().toISOString(),
    provider_name: providerName,
    raw_message_id: ais.MessageId != null ? String(ais.MessageId) : null,
  }
}

export type Freshness = 'LIVE' | 'DELAYED' | 'STALE' | 'OFFLINE'

export const FRESHNESS_COLORS: Record<Freshness, string> = {
  LIVE: '#22c55e',
  DELAYED: '#eab308',
  STALE: '#f97316',
  OFFLINE: '#6b7280',
}

export const FRESHNESS_LABELS: Record<Freshness, string> = {
  LIVE: 'Live',
  DELAYED: 'Delayed',
  STALE: 'Stale',
  OFFLINE: 'Offline',
}

export function getHeading(position: {
  heading_degrees: number | null
  cog_degrees: number | null
}): number {
  if (position.heading_degrees != null && position.heading_degrees >= 0) {
    return position.heading_degrees
  }
  if (position.cog_degrees != null && position.cog_degrees >= 0) {
    return position.cog_degrees
  }
  return 0
}

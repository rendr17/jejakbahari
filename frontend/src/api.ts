const API_BASE = import.meta.env.VITE_API_BASE_URL ?? '/api/v1'

export interface VesselSummary {
  id: string
  name: string
  mmsi: string
  imo: string | null
  call_sign: string | null
  vessel_category: string
  operator: { id: string; name: string } | null
  freshness: 'LIVE' | 'DELAYED' | 'STALE' | 'OFFLINE'
  last_position_at: string | null
}

export interface VesselDetail {
  id: string
  name: string
  mmsi: string
  imo: string | null
  call_sign: string | null
  vessel_category: string
  operator: { id: string; name: string } | null
  freshness: 'LIVE' | 'DELAYED' | 'STALE' | 'OFFLINE'
  last_position_at: string | null
  latest_position: {
    latitude: number
    longitude: number
    sog_knots: number | null
    cog_degrees: number | null
    heading_degrees: number | null
    nav_status: string | null
    destination_text: string | null
    source_timestamp: string | null
    received_at: string | null
  } | null
  verification: {
    status: string
    confidence_score: number | null
    is_verified: boolean
  }
  evidence: Array<{
    id: string
    evidence_type: string
    source_reference: string | null
    observed_value: Record<string, unknown> | null
    confidence_score: number | null
    data_source: {
      id: string
      name: string
      source_type: string
      url: string | null
      license_name: string | null
      attribution_text: string | null
    } | null
  }>
  disclaimer: string
}

export interface LatestPosition {
  vessel_id: string
  name: string | null
  mmsi: string | null
  latitude: number
  longitude: number
  sog_knots: number | null
  cog_degrees: number | null
  heading_degrees: number | null
  nav_status: string | null
  destination_text: string | null
  freshness: 'LIVE' | 'DELAYED' | 'STALE' | 'OFFLINE'
  source_timestamp: string | null
  received_at: string | null
}

interface ApiResponse<T> {
  success: boolean
  data: T
  meta?: Record<string, unknown>
}

async function fetchJson<T>(url: string): Promise<T> {
  const response = await fetch(`${API_BASE}${url}`)
  if (!response.ok) {
    throw new Error(`API error: ${response.status}`)
  }
  const json: ApiResponse<T> = await response.json()
  return json.data
}

export async function fetchVessels(params?: {
  q?: string
  operator_id?: string
  status?: string
  page?: number
  per_page?: number
}): Promise<VesselSummary[]> {
  const query = new URLSearchParams()
  if (params?.q) query.set('q', params.q)
  if (params?.operator_id) query.set('operator_id', params.operator_id)
  if (params?.status) query.set('status', params.status)
  if (params?.page) query.set('page', String(params.page))
  if (params?.per_page) query.set('per_page', String(params.per_page))
  const qs = query.toString()
  return fetchJson(`/vessels${qs ? `?${qs}` : ''}`)
}

export async function fetchVesselDetail(id: string): Promise<VesselDetail> {
  return fetchJson(`/vessels/${id}`)
}

export async function fetchLatestPositions(params?: {
  bbox?: string
  operator_id?: string
  freshness?: string
}): Promise<LatestPosition[]> {
  const query = new URLSearchParams()
  if (params?.bbox) query.set('bbox', params.bbox)
  if (params?.operator_id) query.set('operator_id', params.operator_id)
  if (params?.freshness) query.set('freshness', params.freshness)
  const qs = query.toString()
  return fetchJson(`/positions/latest${qs ? `?${qs}` : ''}`)
}

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

interface ApiError {
  success: false
  error: {
    code: string
    message: string
    details?: unknown
  }
}

export class ApiError2 extends Error {
  status: number
  code: string

  constructor(status: number, code: string, message: string) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.code = code
  }
}

async function fetchJson<T>(url: string): Promise<T> {
  const response = await fetch(`${API_BASE}${url}`)
  if (!response.ok) {
    let message = `API error: ${response.status}`
    let code = 'UNKNOWN'
    try {
      const body: ApiError = await response.json()
      if (body?.error?.message) message = body.error.message
      if (body?.error?.code) code = body.error.code
    } catch {
      // Response body is not JSON or empty; fall back to generic message
    }
    throw new ApiError2(response.status, code, message)
  }
  const json: ApiResponse<T> = await response.json()
  return json.data
}

async function fetchJsonWithMeta<T>(
  url: string,
): Promise<{ data: T; meta: Record<string, unknown> | undefined }> {
  const response = await fetch(`${API_BASE}${url}`)
  if (!response.ok) {
    let message = `API error: ${response.status}`
    let code = 'UNKNOWN'
    try {
      const body: ApiError = await response.json()
      if (body?.error?.message) message = body.error.message
      if (body?.error?.code) code = body.error.code
    } catch {
      // Response body is not JSON or empty; fall back to generic message
    }
    throw new ApiError2(response.status, code, message)
  }
  const json: ApiResponse<T> = await response.json()
  return { data: json.data, meta: json.meta }
}

export interface PaginationMeta {
  total: number
  current_page: number
  per_page: number
  last_page: number
}

export async function fetchVessels(params?: {
  q?: string
  operator_id?: string
  status?: string
  page?: number
  per_page?: number
}): Promise<{ vessels: VesselSummary[]; pagination: PaginationMeta | null }> {
  const query = new URLSearchParams()
  if (params?.q) query.set('q', params.q)
  if (params?.operator_id) query.set('operator_id', params.operator_id)
  if (params?.status) query.set('status', params.status)
  if (params?.page) query.set('page', String(params.page))
  if (params?.per_page) query.set('per_page', String(params.per_page))
  const qs = query.toString()
  const { data, meta } = await fetchJsonWithMeta<VesselSummary[]>(
    `/vessels${qs ? `?${qs}` : ''}`,
  )
  const pagination = meta
    ? {
        total: Number(meta.total ?? 0),
        current_page: Number(meta.current_page ?? 1),
        per_page: Number(meta.per_page ?? 20),
        last_page: Number(meta.last_page ?? 1),
      }
    : null
  return { vessels: data, pagination }
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

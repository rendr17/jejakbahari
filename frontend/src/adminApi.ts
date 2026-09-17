import { ApiError2 } from './api'

const ADMIN_BASE = `${import.meta.env.VITE_API_BASE_URL ?? '/api/v1'}/admin`
const TOKEN_KEY = 'jb_admin_token'

export function getAdminToken(): string | null {
  return sessionStorage.getItem(TOKEN_KEY)
}

export function setAdminToken(token: string): void {
  sessionStorage.setItem(TOKEN_KEY, token)
}

export function clearAdminToken(): void {
  sessionStorage.removeItem(TOKEN_KEY)
}

interface ApiResponse<T> {
  success: boolean
  data: T
  meta?: Record<string, unknown>
}

interface ApiErrorBody {
  success: false
  error: { code: string; message: string; details?: unknown }
}

async function adminFetch<T>(
  path: string,
  options: RequestInit = {},
): Promise<{ data: T; meta: Record<string, unknown> | undefined }> {
  const token = getAdminToken()
  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...(options.headers as Record<string, string>),
  }
  if (token) headers['Authorization'] = `Bearer ${token}`
  if (options.body) headers['Content-Type'] = 'application/json'

  const response = await fetch(`${ADMIN_BASE}${path}`, {
    ...options,
    headers,
  })

  if (response.status === 401) {
    clearAdminToken()
    if (typeof window !== 'undefined') {
      window.location.href = '/admin/login'
    }
    throw new ApiError2(401, 'UNAUTHENTICATED', 'Sesi berakhir.')
  }

  if (!response.ok) {
    let message = `API error: ${response.status}`
    let code = 'UNKNOWN'
    let details: unknown
    try {
      const body: ApiErrorBody = await response.json()
      if (body?.error?.message) message = body.error.message
      if (body?.error?.code) code = body.error.code
      details = body?.error?.details
    } catch {
      // non-JSON error body
    }
    const err = new ApiError2(response.status, code, message)
    ;(err as ApiError2 & { details?: unknown }).details = details
    throw err
  }

  const json: ApiResponse<T> = await response.json()
  return { data: json.data, meta: json.meta }
}

// ── Auth ─────────────────────────────────────────────────────────

export interface AdminUser {
  id: string
  name: string
  email: string
  role: 'admin' | 'reviewer' | string
}

export async function adminLogin(
  email: string,
  password: string,
): Promise<{ token: string; user: AdminUser }> {
  const { data } = await adminFetch<{ token: string; user: AdminUser }>(
    '/auth/login',
    { method: 'POST', body: JSON.stringify({ email, password }) },
  )
  return data
}

export async function adminLogout(): Promise<void> {
  try {
    await adminFetch('/auth/logout', { method: 'POST' })
  } finally {
    clearAdminToken()
  }
}

export async function adminMe(): Promise<AdminUser> {
  const { data } = await adminFetch<AdminUser>('/me')
  return data
}

// ── Shared types ─────────────────────────────────────────────────

export interface PaginationMeta {
  total: number
  current_page: number
  per_page: number
  last_page: number
}

function toPagination(
  meta: Record<string, unknown> | undefined,
): PaginationMeta | null {
  if (!meta) return null
  return {
    total: Number(meta.total ?? 0),
    current_page: Number(meta.current_page ?? 1),
    per_page: Number(meta.per_page ?? 20),
    last_page: Number(meta.last_page ?? 1),
  }
}

function qs(params: Record<string, string | number | undefined>): string {
  const q = new URLSearchParams()
  for (const [k, v] of Object.entries(params)) {
    if (v !== undefined && v !== '') q.set(k, String(v))
  }
  const s = q.toString()
  return s ? `?${s}` : ''
}

// ── Vessels ──────────────────────────────────────────────────────

export interface AdminVessel {
  id: string
  name: string
  mmsi: string
  imo: string | null
  call_sign: string | null
  vessel_category: string
  verification_status: 'DRAFT' | 'REVIEW' | 'VERIFIED' | 'REJECTED' | string
  confidence_score: number
  active: boolean
  public_visible: boolean
  operator: { id: string; name: string } | null
  created_at: string
  updated_at: string
}

export interface AdminVesselPayload {
  name: string
  mmsi: string
  imo?: string | null
  call_sign?: string | null
  vessel_category: string
  operator_id?: string | null
  confidence_score?: number
  active?: boolean
  public_visible?: boolean
  verification_status?: string
}

export async function listAdminVessels(params?: {
  q?: string
  status?: string
  operator_id?: string
  page?: number
  per_page?: number
}): Promise<{ vessels: AdminVessel[]; pagination: PaginationMeta | null }> {
  const { data, meta } = await adminFetch<AdminVessel[]>(
    `/vessels${qs({ q: params?.q, status: params?.status, operator_id: params?.operator_id, page: params?.page, per_page: params?.per_page })}`,
  )
  return { vessels: data, pagination: toPagination(meta) }
}

export async function getAdminVessel(id: string): Promise<AdminVessel> {
  const { data } = await adminFetch<AdminVessel>(`/vessels/${id}`)
  return data
}

export async function createAdminVessel(
  payload: AdminVesselPayload,
): Promise<AdminVessel> {
  const { data } = await adminFetch<AdminVessel>('/vessels', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
  return data
}

export async function updateAdminVessel(
  id: string,
  payload: Partial<AdminVesselPayload>,
): Promise<AdminVessel> {
  const { data } = await adminFetch<AdminVessel>(`/vessels/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
  return data
}

export async function deleteAdminVessel(id: string): Promise<void> {
  await adminFetch(`/vessels/${id}`, { method: 'DELETE' })
}

export async function verifyAdminVessel(id: string): Promise<AdminVessel> {
  const { data } = await adminFetch<AdminVessel>(`/vessels/${id}/verify`, {
    method: 'POST',
  })
  return data
}

export async function rejectAdminVessel(
  id: string,
  reason: string,
): Promise<AdminVessel> {
  const { data } = await adminFetch<AdminVessel>(`/vessels/${id}/reject`, {
    method: 'POST',
    body: JSON.stringify({ reason }),
  })
  return data
}

// ── Operators ────────────────────────────────────────────────────

export interface AdminOperator {
  id: string
  name: string
  slug: string
  website_url: string | null
  active: boolean
}

export async function listAdminOperators(params?: {
  q?: string
  page?: number
  per_page?: number
}): Promise<{ operators: AdminOperator[]; pagination: PaginationMeta | null }> {
  const { data, meta } = await adminFetch<AdminOperator[]>(
    `/operators${qs({ q: params?.q, page: params?.page, per_page: params?.per_page ?? 100 })}`,
  )
  return { operators: data, pagination: toPagination(meta) }
}

export async function createAdminOperator(payload: {
  name: string
  website_url?: string | null
  active?: boolean
}): Promise<AdminOperator> {
  const { data } = await adminFetch<AdminOperator>('/operators', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
  return data
}

export async function updateAdminOperator(
  id: string,
  payload: { name?: string; website_url?: string | null; active?: boolean },
): Promise<AdminOperator> {
  const { data } = await adminFetch<AdminOperator>(`/operators/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
  return data
}

export async function deleteAdminOperator(id: string): Promise<void> {
  await adminFetch(`/operators/${id}`, { method: 'DELETE' })
}

// ── Ports ────────────────────────────────────────────────────────

export interface AdminPort {
  id: string
  code: string
  name: string
  city_name: string | null
  province_name: string | null
  latitude: number | null
  longitude: number | null
  geofence_radius_m: number | null
  geofence_type: string
  verification_status: string
  active: boolean
}

export interface AdminPortPayload {
  code: string
  name: string
  city_name?: string | null
  province_name?: string | null
  latitude: number
  longitude: number
  geofence_radius_m?: number | null
  verification_status?: string
  active?: boolean
}

export async function listAdminPorts(params?: {
  q?: string
  page?: number
  per_page?: number
}): Promise<{ ports: AdminPort[]; pagination: PaginationMeta | null }> {
  const { data, meta } = await adminFetch<AdminPort[]>(
    `/ports${qs({ q: params?.q, page: params?.page, per_page: params?.per_page ?? 100 })}`,
  )
  return { ports: data, pagination: toPagination(meta) }
}

export async function createAdminPort(
  payload: AdminPortPayload,
): Promise<AdminPort> {
  const { data } = await adminFetch<AdminPort>('/ports', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
  return data
}

export async function updateAdminPort(
  id: string,
  payload: Partial<AdminPortPayload>,
): Promise<AdminPort> {
  const { data } = await adminFetch<AdminPort>(`/ports/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
  return data
}

export async function deleteAdminPort(id: string): Promise<void> {
  await adminFetch(`/ports/${id}`, { method: 'DELETE' })
}

// ── Routes ───────────────────────────────────────────────────────

export interface AdminRoute {
  id: string
  name: string
  route_type: string
  bidirectional: boolean
  active: boolean
  origin_port: { id: string; name: string; code: string } | null
  destination_port: { id: string; name: string; code: string } | null
}

export interface AdminRoutePayload {
  name: string
  route_type: string
  origin_port_id: string
  destination_port_id: string
  bidirectional?: boolean
  active?: boolean
}

export async function listAdminRoutes(params?: {
  q?: string
  page?: number
  per_page?: number
}): Promise<{ routes: AdminRoute[]; pagination: PaginationMeta | null }> {
  const { data, meta } = await adminFetch<AdminRoute[]>(
    `/routes${qs({ q: params?.q, page: params?.page, per_page: params?.per_page ?? 100 })}`,
  )
  return { routes: data, pagination: toPagination(meta) }
}

export async function createAdminRoute(
  payload: AdminRoutePayload,
): Promise<AdminRoute> {
  const { data } = await adminFetch<AdminRoute>('/routes', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
  return data
}

export async function updateAdminRoute(
  id: string,
  payload: Partial<AdminRoutePayload>,
): Promise<AdminRoute> {
  const { data } = await adminFetch<AdminRoute>(`/routes/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
  return data
}

export async function deleteAdminRoute(id: string): Promise<void> {
  await adminFetch(`/routes/${id}`, { method: 'DELETE' })
}

// ── Data sources ─────────────────────────────────────────────────

export interface AdminDataSource {
  id: string
  name: string
  source_type: string
  url: string | null
  license_name: string | null
  terms_url: string | null
  attribution_text: string | null
  access_method: string
  active: boolean
  last_reviewed_at: string | null
}

export interface AdminDataSourcePayload {
  name: string
  source_type: string
  access_method: string
  url?: string | null
  license_name?: string | null
  terms_url?: string | null
  attribution_text?: string | null
  active?: boolean
  last_reviewed_at?: string | null
}

export async function listAdminDataSources(params?: {
  q?: string
  page?: number
  per_page?: number
}): Promise<{
  dataSources: AdminDataSource[]
  pagination: PaginationMeta | null
}> {
  const { data, meta } = await adminFetch<AdminDataSource[]>(
    `/data-sources${qs({ q: params?.q, page: params?.page, per_page: params?.per_page ?? 100 })}`,
  )
  return { dataSources: data, pagination: toPagination(meta) }
}

export async function createAdminDataSource(
  payload: AdminDataSourcePayload,
): Promise<AdminDataSource> {
  const { data } = await adminFetch<AdminDataSource>('/data-sources', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
  return data
}

export async function updateAdminDataSource(
  id: string,
  payload: Partial<AdminDataSourcePayload>,
): Promise<AdminDataSource> {
  const { data } = await adminFetch<AdminDataSource>(`/data-sources/${id}`, {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
  return data
}

export async function deleteAdminDataSource(id: string): Promise<void> {
  await adminFetch(`/data-sources/${id}`, { method: 'DELETE' })
}

// ── Evidence ─────────────────────────────────────────────────────

export interface AdminEvidence {
  id: string
  vessel_id: string
  data_source_id: string
  evidence_type: string
  source_reference: string
  observed_value: Record<string, unknown>
  confidence_score: number
  reviewed_by: string | null
  reviewed_at: string | null
  created_at: string
}

export interface AdminEvidencePayload {
  data_source_id: string
  evidence_type: string
  source_reference: string
  observed_value: Record<string, unknown>
  confidence_score: number
}

export async function listAdminEvidence(
  vesselId: string,
): Promise<AdminEvidence[]> {
  const { data } = await adminFetch<AdminEvidence[]>(
    `/vessels/${vesselId}/evidence`,
  )
  return data
}

export async function createAdminEvidence(
  vesselId: string,
  payload: AdminEvidencePayload,
): Promise<AdminEvidence> {
  const { data } = await adminFetch<AdminEvidence>(
    `/vessels/${vesselId}/evidence`,
    { method: 'POST', body: JSON.stringify(payload) },
  )
  return data
}

export async function deleteAdminEvidence(
  vesselId: string,
  evidenceId: string,
): Promise<void> {
  await adminFetch(`/vessels/${vesselId}/evidence/${evidenceId}`, {
    method: 'DELETE',
  })
}

// ── Audit logs ───────────────────────────────────────────────────

export interface AdminAuditLog {
  id: string
  action: string
  entity_type: string
  entity_id: string
  before_data: Record<string, unknown> | null
  after_data: Record<string, unknown> | null
  ip_address: string | null
  user: { id: string; name: string } | null
  created_at: string
}

export async function listAdminAuditLogs(params?: {
  page?: number
  per_page?: number
}): Promise<{ logs: AdminAuditLog[]; pagination: PaginationMeta | null }> {
  const { data, meta } = await adminFetch<AdminAuditLog[]>(
    `/audit-logs${qs({ page: params?.page, per_page: params?.per_page })}`,
  )
  return { logs: data, pagination: toPagination(meta) }
}

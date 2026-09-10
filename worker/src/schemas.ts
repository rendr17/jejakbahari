import { z } from 'zod'

export const aisPositionSchema = z.object({
  MMSI: z
    .string()
    .regex(/^\d{9}$/)
    .or(z.number().int().min(100000000).max(999999999)),
  Latitude: z.number().min(-90).max(90),
  Longitude: z.number().min(-180).max(180),
  SOG: z.number().min(0).max(102.2).nullable().optional(),
  COG: z.number().min(0).max(360).nullable().optional(),
  Heading: z.number().int().min(0).max(359).nullable().optional(),
  NavigationStatus: z.string().max(60).nullable().optional(),
  Destination: z.string().max(200).nullable().optional(),
  Timestamp: z.string().or(z.number()),
  // Optional provider-specific fields
  MessageId: z.string().or(z.number()).nullable().optional(),
})

export type AisPosition = z.infer<typeof aisPositionSchema>

export const normalizedPositionSchema = z.object({
  mmsi: z.string().regex(/^\d{9}$/),
  latitude: z.number().min(-90).max(90),
  longitude: z.number().min(-180).max(180),
  sog_knots: z.number().min(0).max(102.2).nullable().optional(),
  cog_degrees: z.number().min(0).max(360).nullable().optional(),
  heading_degrees: z.number().int().min(0).max(359).nullable().optional(),
  nav_status: z.string().max(60).nullable().optional(),
  destination_text: z.string().max(200).nullable().optional(),
  source_timestamp: z.string(),
  received_at: z.string(),
  provider_name: z.string().max(80),
  raw_message_id: z.string().max(120).nullable().optional(),
})

export type NormalizedPosition = z.infer<typeof normalizedPositionSchema>

export const whitelistResponseSchema = z.object({
  success: z.boolean(),
  data: z.object({
    mmsi_list: z.array(z.object({ mmsi: z.string() })),
    count: z.number().int(),
    version_hash: z.string(),
  }),
})

export type WhitelistResponse = z.infer<typeof whitelistResponseSchema>

export const heartbeatPayloadSchema = z.object({
  worker_id: z.string().min(1),
  status: z.enum(['HEALTHY', 'DEGRADED', 'DISCONNECTED']),
  provider_connected: z.boolean(),
  messages_received_total: z.number().int().min(0),
  positions_delivered_total: z.number().int().min(0),
  delivery_failures_total: z.number().int().min(0),
  queue_depth: z.number().int().min(0),
  last_message_timestamp: z.string().nullable().optional(),
  whitelist_version: z.string().nullable().optional(),
})

export type HeartbeatPayload = z.infer<typeof heartbeatPayloadSchema>

export interface PositionDeliveryResult {
  accepted: boolean
  history_saved: boolean
  geofence_events: unknown[]
}

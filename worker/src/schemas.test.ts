import { describe, it, expect } from 'vitest'
import {
  aisPositionSchema,
  normalizedPositionSchema,
  heartbeatPayloadSchema,
} from './schemas.js'

describe('aisPositionSchema', () => {
  it('accepts valid position with string MMSI', () => {
    const valid = {
      MMSI: '525123456',
      Latitude: -5.87,
      Longitude: 105.77,
      SOG: 12.4,
      COG: 95.2,
      Heading: 92,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    expect(aisPositionSchema.safeParse(valid).success).toBe(true)
  })

  it('accepts valid position with numeric MMSI', () => {
    const valid = {
      MMSI: 525123456,
      Latitude: -5.87,
      Longitude: 105.77,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    expect(aisPositionSchema.safeParse(valid).success).toBe(true)
  })

  it('rejects MMSI with less than 9 digits', () => {
    const invalid = {
      MMSI: '12345',
      Latitude: -5.87,
      Longitude: 105.77,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    expect(aisPositionSchema.safeParse(invalid).success).toBe(false)
  })

  it('rejects latitude out of range', () => {
    const invalid = {
      MMSI: '525123456',
      Latitude: 91,
      Longitude: 105.77,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    expect(aisPositionSchema.safeParse(invalid).success).toBe(false)
  })

  it('rejects longitude out of range', () => {
    const invalid = {
      MMSI: '525123456',
      Latitude: -5.87,
      Longitude: 181,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    expect(aisPositionSchema.safeParse(invalid).success).toBe(false)
  })

  it('accepts nullable optional fields', () => {
    const valid = {
      MMSI: '525123456',
      Latitude: -5.87,
      Longitude: 105.77,
      SOG: null,
      COG: null,
      Heading: null,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    expect(aisPositionSchema.safeParse(valid).success).toBe(true)
  })
})

describe('normalizedPositionSchema', () => {
  it('accepts valid normalized position', () => {
    const valid = {
      mmsi: '525123456',
      latitude: -5.87,
      longitude: 105.77,
      sog_knots: 12.4,
      cog_degrees: 95.2,
      heading_degrees: 92,
      source_timestamp: '2026-08-01T12:00:00Z',
      received_at: '2026-08-01T12:00:02Z',
      provider_name: 'test-provider',
    }

    expect(normalizedPositionSchema.safeParse(valid).success).toBe(true)
  })

  it('rejects invalid MMSI', () => {
    const invalid = {
      mmsi: 'abc',
      latitude: -5.87,
      longitude: 105.77,
      source_timestamp: '2026-08-01T12:00:00Z',
      received_at: '2026-08-01T12:00:02Z',
      provider_name: 'test-provider',
    }

    expect(normalizedPositionSchema.safeParse(invalid).success).toBe(false)
  })
})

describe('heartbeatPayloadSchema', () => {
  it('accepts valid heartbeat', () => {
    const valid = {
      worker_id: 'worker-1',
      status: 'HEALTHY' as const,
      provider_connected: true,
      messages_received_total: 100,
      positions_delivered_total: 95,
      delivery_failures_total: 0,
      queue_depth: 5,
    }

    expect(heartbeatPayloadSchema.safeParse(valid).success).toBe(true)
  })

  it('rejects invalid status', () => {
    const invalid = {
      worker_id: 'worker-1',
      status: 'INVALID',
      provider_connected: true,
      messages_received_total: 100,
      positions_delivered_total: 95,
      delivery_failures_total: 0,
      queue_depth: 5,
    }

    expect(heartbeatPayloadSchema.safeParse(invalid).success).toBe(false)
  })
})

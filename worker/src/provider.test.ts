import { describe, it, expect } from 'vitest'
import { normalizeAisMessage } from './provider.js'

describe('normalizeAisMessage', () => {
  it('normalizes valid AIS message with string MMSI', () => {
    const raw = {
      MMSI: '525123456',
      Latitude: -5.87,
      Longitude: 105.77,
      SOG: 12.4,
      COG: 95.2,
      Heading: 92,
      NavigationStatus: 'UNDER_WAY_USING_ENGINE',
      Destination: 'MERAK',
      Timestamp: '2026-08-01T12:00:00Z',
      MessageId: 'msg-001',
    }

    const result = normalizeAisMessage(raw, 'test-provider')

    expect(result).not.toBeNull()
    expect(result?.mmsi).toBe('525123456')
    expect(result?.latitude).toBe(-5.87)
    expect(result?.longitude).toBe(105.77)
    expect(result?.sog_knots).toBe(12.4)
    expect(result?.cog_degrees).toBe(95.2)
    expect(result?.heading_degrees).toBe(92)
    expect(result?.nav_status).toBe('UNDER_WAY_USING_ENGINE')
    expect(result?.destination_text).toBe('MERAK')
    expect(result?.source_timestamp).toBe('2026-08-01T12:00:00Z')
    expect(result?.provider_name).toBe('test-provider')
    expect(result?.raw_message_id).toBe('msg-001')
  })

  it('normalizes valid AIS message with numeric timestamp', () => {
    const raw = {
      MMSI: '525123456',
      Latitude: -5.87,
      Longitude: 105.77,
      Timestamp: 1722523200,
    }

    const result = normalizeAisMessage(raw, 'test-provider')

    expect(result).not.toBeNull()
    expect(result?.source_timestamp).toBe('2024-08-01T14:40:00.000Z')
  })

  it('returns null for invalid MMSI', () => {
    const raw = {
      MMSI: '12345',
      Latitude: -5.87,
      Longitude: 105.77,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    expect(normalizeAisMessage(raw, 'test-provider')).toBeNull()
  })

  it('returns null for invalid coordinates', () => {
    const raw = {
      MMSI: '525123456',
      Latitude: 91,
      Longitude: 105.77,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    expect(normalizeAisMessage(raw, 'test-provider')).toBeNull()
  })

  it('returns null for missing required fields', () => {
    expect(normalizeAisMessage({}, 'test-provider')).toBeNull()
  })

  it('handles nullable optional fields', () => {
    const raw = {
      MMSI: '525123456',
      Latitude: -5.87,
      Longitude: 105.77,
      SOG: null,
      COG: null,
      Heading: null,
      Timestamp: '2026-08-01T12:00:00Z',
    }

    const result = normalizeAisMessage(raw, 'test-provider')

    expect(result).not.toBeNull()
    expect(result?.sog_knots).toBeNull()
    expect(result?.cog_degrees).toBeNull()
    expect(result?.heading_degrees).toBeNull()
  })
})

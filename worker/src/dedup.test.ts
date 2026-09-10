import { describe, it, expect } from 'vitest'
import { Deduplicator } from './dedup.js'

describe('Deduplicator', () => {
  it('detects duplicate by raw message id', () => {
    const dedup = new Deduplicator()

    expect(
      dedup.isDuplicate(
        '525123456',
        '2026-08-01T12:00:00Z',
        -5.87,
        105.77,
        'msg-001',
      ),
    ).toBe(false)
    expect(
      dedup.isDuplicate(
        '525123456',
        '2026-08-01T12:00:00Z',
        -5.87,
        105.77,
        'msg-001',
      ),
    ).toBe(true)
  })

  it('detects duplicate by mmsi+timestamp+coords', () => {
    const dedup = new Deduplicator()

    expect(
      dedup.isDuplicate('525123456', '2026-08-01T12:00:00Z', -5.87, 105.77),
    ).toBe(false)
    expect(
      dedup.isDuplicate('525123456', '2026-08-01T12:00:00Z', -5.87, 105.77),
    ).toBe(true)
  })

  it('does not flag different positions as duplicate', () => {
    const dedup = new Deduplicator()

    expect(
      dedup.isDuplicate('525123456', '2026-08-01T12:00:00Z', -5.87, 105.77),
    ).toBe(false)
    expect(
      dedup.isDuplicate('525123456', '2026-08-01T12:01:00Z', -5.8, 105.7),
    ).toBe(false)
  })

  it('does not flag different MMSI as duplicate', () => {
    const dedup = new Deduplicator()

    expect(
      dedup.isDuplicate('525123456', '2026-08-01T12:00:00Z', -5.87, 105.77),
    ).toBe(false)
    expect(
      dedup.isDuplicate('525789012', '2026-08-01T12:00:00Z', -5.87, 105.77),
    ).toBe(false)
  })

  it('clears all entries', () => {
    const dedup = new Deduplicator()
    dedup.isDuplicate('525123456', '2026-08-01T12:00:00Z', -5.87, 105.77)
    expect(dedup.size).toBe(1)

    dedup.clear()
    expect(dedup.size).toBe(0)
  })

  it('evicts old entries when max is exceeded', () => {
    const dedup = new Deduplicator(5)

    for (let i = 0; i < 10; i++) {
      dedup.isDuplicate(
        '525123456',
        `2026-08-01T12:0${i}:00Z`,
        -5.87 + i * 0.01,
        105.77,
      )
    }

    expect(dedup.size).toBeLessThanOrEqual(5)
  })
})

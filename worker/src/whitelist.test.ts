import { describe, it, expect } from 'vitest'
import { WhitelistCache } from './whitelist.js'

describe('WhitelistCache', () => {
  const validResponse = {
    success: true,
    data: {
      mmsi_list: [{ mmsi: '525111111' }, { mmsi: '525222222' }],
      count: 2,
      version_hash: 'abc123',
    },
  }

  it('updates from API response', () => {
    const cache = new WhitelistCache()
    cache.update(validResponse)

    expect(cache.count).toBe(2)
    expect(cache.version).toBe('abc123')
    expect(cache.has('525111111')).toBe(true)
    expect(cache.has('525222222')).toBe(true)
    expect(cache.has('999999999')).toBe(false)
  })

  it('starts empty', () => {
    const cache = new WhitelistCache()

    expect(cache.isEmpty()).toBe(true)
    expect(cache.count).toBe(0)
  })

  it('is no longer empty after update', () => {
    const cache = new WhitelistCache()
    cache.update(validResponse)

    expect(cache.isEmpty()).toBe(false)
  })

  it('tracks age', () => {
    const cache = new WhitelistCache()
    cache.update(validResponse)

    expect(cache.ageSeconds).toBeGreaterThanOrEqual(0)
    expect(cache.ageSeconds).toBeLessThan(5)
  })

  it('detects stale cache', () => {
    const cache = new WhitelistCache()

    // Empty cache is always stale
    expect(cache.isStale(300)).toBe(true)
  })

  it('rejects invalid response', () => {
    const cache = new WhitelistCache()

    expect(() => cache.update({ wrong: 'shape' })).toThrow()
  })
})

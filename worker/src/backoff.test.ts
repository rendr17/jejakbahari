import { describe, it, expect } from 'vitest'
import { ExponentialBackoff } from './backoff.js'

describe('ExponentialBackoff', () => {
  it('starts at min delay', () => {
    const backoff = new ExponentialBackoff(1000, 60000)
    const delay = backoff.nextDelay()

    expect(delay).toBeGreaterThanOrEqual(1000)
    expect(delay).toBeLessThanOrEqual(1250)
  })

  it('increases exponentially', () => {
    const backoff = new ExponentialBackoff(1000, 60000)
    const d1 = backoff.nextDelay()
    const d2 = backoff.nextDelay()
    const d3 = backoff.nextDelay()

    expect(d2).toBeGreaterThan(d1)
    expect(d3).toBeGreaterThan(d2)
  })

  it('caps at max delay', () => {
    const backoff = new ExponentialBackoff(1000, 5000)
    for (let i = 0; i < 20; i++) {
      backoff.nextDelay()
    }
    const delay = backoff.nextDelay()

    expect(delay).toBeLessThanOrEqual(6250) // max + jitter
  })

  it('resets attempt counter', () => {
    const backoff = new ExponentialBackoff(1000, 60000)
    backoff.nextDelay()
    backoff.nextDelay()
    backoff.nextDelay()
    backoff.reset()

    expect(backoff.currentAttempt).toBe(0)
  })
})

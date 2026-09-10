import { describe, expect, it } from 'vitest'

import { loadConfig } from './config.js'

const validEnvironment = {
  WORKER_ID: 'worker-test',
  AIS_PROVIDER_TYPE: 'websocket' as const,
  AIS_PROVIDER_URL: 'wss://stream.aisstream.io/v0/stream',
  AIS_PROVIDER_API_KEY: 'test-provider-key',
  AIS_BOUNDING_BOXES: '-11,95,6,141',
  BACKEND_INTERNAL_URL: 'http://backend.test/api/internal/v1',
  BACKEND_INTERNAL_TOKEN: 'test-internal-token',
}

describe('worker configuration', () => {
  it('applies safe defaults and rejects missing credentials', () => {
    expect(loadConfig(validEnvironment)).toMatchObject({
      NODE_ENV: 'development',
      AIS_PROVIDER_TYPE: 'websocket',
      BACKOFF_MIN_MS: 1000,
      BACKOFF_MAX_MS: 60000,
      DELIVERY_QUEUE_MAX: 1000,
    })

    expect(() =>
      loadConfig({ ...validEnvironment, BACKEND_INTERNAL_TOKEN: '' }),
    ).toThrow()
  })

  it('defaults to websocket provider type', () => {
    const { AIS_PROVIDER_TYPE: _removed, ...withoutType } = validEnvironment
    void _removed
    const config = loadConfig(withoutType)

    expect(config.AIS_PROVIDER_TYPE).toBe('websocket')
  })

  it('allows mock provider without URL or API key', () => {
    const config = loadConfig({
      WORKER_ID: 'worker-test',
      AIS_PROVIDER_TYPE: 'mock',
      AIS_BOUNDING_BOXES: '-11,95,6,141',
      BACKEND_INTERNAL_URL: 'http://backend.test/api/internal/v1',
      BACKEND_INTERNAL_TOKEN: 'test-internal-token',
    })

    expect(config.AIS_PROVIDER_TYPE).toBe('mock')
    expect(config.AIS_PROVIDER_URL).toBe('')
    expect(config.AIS_PROVIDER_API_KEY).toBe('')
  })

  it('rejects websocket provider without URL', () => {
    expect(() =>
      loadConfig({
        ...validEnvironment,
        AIS_PROVIDER_URL: '',
      }),
    ).toThrow()
  })

  it('rejects websocket provider without API key', () => {
    expect(() =>
      loadConfig({
        ...validEnvironment,
        AIS_PROVIDER_API_KEY: '',
      }),
    ).toThrow()
  })

  it('accepts aisstream provider with URL and API key', () => {
    const config = loadConfig({
      ...validEnvironment,
      AIS_PROVIDER_TYPE: 'aisstream',
    })

    expect(config.AIS_PROVIDER_TYPE).toBe('aisstream')
  })

  it('rejects aisstream provider without URL', () => {
    expect(() =>
      loadConfig({
        ...validEnvironment,
        AIS_PROVIDER_TYPE: 'aisstream',
        AIS_PROVIDER_URL: '',
      }),
    ).toThrow()
  })
})

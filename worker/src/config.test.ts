import { describe, expect, it } from 'vitest'

import { loadConfig } from './config.js'

const validEnvironment = {
  WORKER_ID: 'worker-test',
  AIS_PROVIDER_URL: 'wss://provider.example.test/ais',
  AIS_PROVIDER_API_KEY: 'test-provider-key',
  AIS_BOUNDING_BOXES: '-11,95,6,141',
  BACKEND_INTERNAL_URL: 'http://backend.test/api/internal/v1',
  BACKEND_INTERNAL_TOKEN: 'test-internal-token',
}

describe('worker configuration', () => {
  it('applies safe defaults and rejects missing credentials', () => {
    expect(loadConfig(validEnvironment)).toMatchObject({
      NODE_ENV: 'development',
      BACKOFF_MIN_MS: 1000,
      BACKOFF_MAX_MS: 60000,
      DELIVERY_QUEUE_MAX: 1000,
    })

    expect(() =>
      loadConfig({ ...validEnvironment, BACKEND_INTERNAL_TOKEN: '' }),
    ).toThrow()
  })
})

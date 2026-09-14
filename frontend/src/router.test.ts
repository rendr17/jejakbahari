import { describe, expect, it } from 'vitest'
import { createMemoryHistory, createRouter } from 'vue-router'

import { routes } from './routes'

describe('public routes', () => {
  it('resolves the landing, map, vessel, port, and route routes', async () => {
    const router = createRouter({ history: createMemoryHistory(), routes })

    await router.push('/')
    expect(router.currentRoute.value.name).toBe('landing')

    await router.push('/peta')
    expect(router.currentRoute.value.name).toBe('map')

    await router.push('/kapal')
    expect(router.currentRoute.value.name).toBe('vessel-list')

    await router.push('/kapal/abc-123')
    expect(router.currentRoute.value).toMatchObject({
      name: 'vessel-detail',
      params: { id: 'abc-123' },
    })

    await router.push('/pelabuhan')
    expect(router.currentRoute.value.name).toBe('port-list')

    await router.push('/lintasan')
    expect(router.currentRoute.value.name).toBe('route-list')

    await router.push('/#transparansi-data')
    expect(router.currentRoute.value).toMatchObject({
      name: 'landing',
      hash: '#transparansi-data',
    })
  })
})

import { describe, expect, it } from 'vitest'
import { createMemoryHistory, createRouter } from 'vue-router'

import { routes } from './routes'

describe('public routes', () => {
  it('resolves the landing and map routes', async () => {
    const router = createRouter({ history: createMemoryHistory(), routes })

    await router.push('/')
    expect(router.currentRoute.value.name).toBe('landing')

    await router.push('/peta')
    expect(router.currentRoute.value.name).toBe('map')
  })
})

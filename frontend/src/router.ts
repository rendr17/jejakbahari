import { createRouter, createWebHistory } from 'vue-router'

import { getAdminToken } from './adminApi'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: (to) => (to.hash ? { el: to.hash, top: 80 } : { top: 0 }),
})

router.beforeEach((to) => {
  if (to.meta.requiresAuth && !getAdminToken()) {
    return { name: 'admin-login', query: { redirect: to.fullPath } }
  }
  if (to.name === 'admin-login' && getAdminToken()) {
    return { name: 'admin-dashboard' }
  }
})

export default router

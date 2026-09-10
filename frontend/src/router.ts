import { createRouter, createWebHistory } from 'vue-router'

import { routes } from './routes'

export default createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: (to) => (to.hash ? { el: to.hash, top: 80 } : { top: 0 }),
})

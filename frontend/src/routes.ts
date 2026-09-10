import type { RouteRecordRaw } from 'vue-router'

import LandingPage from './views/LandingPage.vue'
import MapPage from './views/MapPage.vue'

export const routes: RouteRecordRaw[] = [
  { path: '/', name: 'landing', component: LandingPage },
  { path: '/peta', name: 'map', component: MapPage },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

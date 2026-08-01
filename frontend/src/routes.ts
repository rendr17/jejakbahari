import type { RouteRecordRaw } from 'vue-router'

import LandingPage from './views/LandingPage.vue'
import MapPlaceholderPage from './views/MapPlaceholderPage.vue'

export const routes: RouteRecordRaw[] = [
  { path: '/', name: 'landing', component: LandingPage },
  { path: '/peta', name: 'map', component: MapPlaceholderPage },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

import type { RouteRecordRaw } from 'vue-router'

import LandingPage from './views/LandingPage.vue'
import MapPage from './views/MapPage.vue'
import VesselListPage from './views/VesselListPage.vue'
import VesselDetailPage from './views/VesselDetailPage.vue'

export const routes: RouteRecordRaw[] = [
  { path: '/', name: 'landing', component: LandingPage },
  { path: '/peta', name: 'map', component: MapPage },
  { path: '/kapal', name: 'vessel-list', component: VesselListPage },
  { path: '/kapal/:id', name: 'vessel-detail', component: VesselDetailPage },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

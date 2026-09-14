import type { RouteRecordRaw } from 'vue-router'

import LandingPage from './views/LandingPage.vue'
import MapPage from './views/MapPage.vue'
import VesselListPage from './views/VesselListPage.vue'
import VesselDetailPage from './views/VesselDetailPage.vue'
import PortListPage from './views/PortListPage.vue'
import RouteListPage from './views/RouteListPage.vue'

export const routes: RouteRecordRaw[] = [
  { path: '/', name: 'landing', component: LandingPage },
  { path: '/peta', name: 'map', component: MapPage },
  { path: '/kapal', name: 'vessel-list', component: VesselListPage },
  { path: '/kapal/:id', name: 'vessel-detail', component: VesselDetailPage },
  { path: '/pelabuhan', name: 'port-list', component: PortListPage },
  { path: '/lintasan', name: 'route-list', component: RouteListPage },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

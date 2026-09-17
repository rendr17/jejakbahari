import type { RouteRecordRaw } from 'vue-router'

import LandingPage from './views/LandingPage.vue'
import MapPage from './views/MapPage.vue'
import VesselListPage from './views/VesselListPage.vue'
import VesselDetailPage from './views/VesselDetailPage.vue'
import PortListPage from './views/PortListPage.vue'
import PortEventListPage from './views/PortEventListPage.vue'
import RouteListPage from './views/RouteListPage.vue'
import AdminLoginPage from './views/admin/AdminLoginPage.vue'
import AdminLayout from './views/admin/AdminLayout.vue'
import AdminDashboardPage from './views/admin/AdminDashboardPage.vue'
import AdminVesselsPage from './views/admin/AdminVesselsPage.vue'
import AdminVesselDetailPage from './views/admin/AdminVesselDetailPage.vue'
import AdminOperatorsPage from './views/admin/AdminOperatorsPage.vue'
import AdminPortsPage from './views/admin/AdminPortsPage.vue'
import AdminRoutesPage from './views/admin/AdminRoutesPage.vue'
import AdminDataSourcesPage from './views/admin/AdminDataSourcesPage.vue'
import AdminAuditLogPage from './views/admin/AdminAuditLogPage.vue'

export const routes: RouteRecordRaw[] = [
  { path: '/', name: 'landing', component: LandingPage },
  { path: '/peta', name: 'map', component: MapPage },
  { path: '/kapal', name: 'vessel-list', component: VesselListPage },
  { path: '/kapal/:id', name: 'vessel-detail', component: VesselDetailPage },
  { path: '/pelabuhan', name: 'port-list', component: PortListPage },
  {
    path: '/pelabuhan/:id/event',
    name: 'port-events',
    component: PortEventListPage,
  },
  { path: '/lintasan', name: 'route-list', component: RouteListPage },
  {
    path: '/admin/login',
    name: 'admin-login',
    component: AdminLoginPage,
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '', name: 'admin-dashboard', component: AdminDashboardPage },
      { path: 'kapal', name: 'admin-vessels', component: AdminVesselsPage },
      {
        path: 'kapal/:id',
        name: 'admin-vessel-detail',
        component: AdminVesselDetailPage,
      },
      {
        path: 'operator',
        name: 'admin-operators',
        component: AdminOperatorsPage,
      },
      { path: 'pelabuhan', name: 'admin-ports', component: AdminPortsPage },
      { path: 'lintasan', name: 'admin-routes', component: AdminRoutesPage },
      {
        path: 'sumber-data',
        name: 'admin-data-sources',
        component: AdminDataSourcesPage,
      },
      { path: 'audit', name: 'admin-audit', component: AdminAuditLogPage },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

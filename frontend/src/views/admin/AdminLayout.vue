<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'

import { useAdminAuth } from '../../composables/useAdminAuth'

const router = useRouter()
const { user, fetchUser, logout } = useAdminAuth()

onMounted(() => {
  fetchUser()
})

async function handleLogout() {
  await logout()
  router.push({ name: 'admin-login' })
}

const navItems = [
  { to: '/admin', label: 'Dashboard', exact: true },
  { to: '/admin/kapal', label: 'Kapal' },
  { to: '/admin/operator', label: 'Operator' },
  { to: '/admin/pelabuhan', label: 'Pelabuhan' },
  { to: '/admin/lintasan', label: 'Lintasan' },
  { to: '/admin/sumber-data', label: 'Sumber Data' },
  { to: '/admin/audit', label: 'Audit Log' },
]
</script>

<template>
  <div class="site-container py-6">
    <div class="grid gap-6 lg:grid-cols-[14rem_1fr]">
      <aside>
        <nav
          aria-label="Navigasi admin"
          class="flex flex-wrap gap-1 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-2 lg:sticky lg:top-20 lg:flex-col"
        >
          <RouterLink
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            :class="[
              'rounded-md px-3 py-2 text-sm font-medium transition hover:bg-[var(--color-surface-elevated)]',
              (
                item.exact
                  ? $route.path === item.to
                  : $route.path.startsWith(item.to)
              )
                ? 'bg-[var(--color-primary)] text-white'
                : 'text-[var(--color-text-primary)]',
            ]"
          >
            {{ item.label }}
          </RouterLink>
        </nav>

        <div
          class="mt-3 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3 text-xs text-[var(--color-text-secondary)]"
        >
          <p class="font-semibold text-[var(--color-text-primary)]">
            {{ user?.name ?? '…' }}
          </p>
          <p>{{ user?.email }}</p>
          <p class="mt-1 uppercase tracking-wider">{{ user?.role }}</p>
          <button
            class="mt-2 text-[var(--color-danger)] underline"
            data-testid="admin-logout"
            @click="handleLogout"
          >
            Keluar
          </button>
        </div>
      </aside>

      <div class="min-w-0">
        <RouterView />
      </div>
    </div>
  </div>
</template>

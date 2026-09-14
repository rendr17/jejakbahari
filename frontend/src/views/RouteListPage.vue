<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { fetchRoutes, type RouteSummary, type PaginationMeta } from '../api'

const routes = ref<RouteSummary[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const page = ref(1)
const pagination = ref<PaginationMeta | null>(null)

async function loadRoutes(): Promise<void> {
  try {
    loading.value = true
    error.value = null
    const result = await fetchRoutes({ page: page.value, per_page: 50 })
    routes.value = result.routes
    pagination.value = result.pagination
  } catch (e) {
    error.value =
      e instanceof Error ? e.message : 'Gagal memuat daftar lintasan'
  } finally {
    loading.value = false
  }
}

function prevPage(): void {
  page.value = page.value - 1
  loadRoutes()
}

function nextPage(): void {
  page.value = page.value + 1
  loadRoutes()
}

onMounted(loadRoutes)

function routeTypeLabel(type: string): string {
  return type === 'ROPAX' ? 'RoPax' : 'RoRo'
}
</script>

<template>
  <div data-testid="route-list-page" class="site-container py-8">
    <header class="mb-6">
      <h1 class="text-2xl font-extrabold tracking-tight md:text-3xl">
        Lintasan
      </h1>
      <p class="mt-2 text-sm text-[var(--color-text-secondary)]">
        Lintasan RoRo/RoPax antar pelabuhan Indonesia.
      </p>
    </header>

    <div
      v-if="loading"
      data-testid="route-list-loading"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Memuat daftar lintasan…
    </div>

    <div
      v-else-if="error"
      data-testid="route-list-error"
      role="alert"
      aria-live="assertive"
      class="rounded-lg border border-[var(--color-danger)] bg-[var(--color-surface)] p-4 text-sm text-[var(--color-danger)]"
    >
      {{ error }}
      <button class="ml-2 underline" @click="loadRoutes">Coba lagi</button>
    </div>

    <div
      v-else-if="routes.length === 0"
      data-testid="route-list-empty"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Belum ada lintasan terdaftar.
    </div>

    <ul v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <li
        v-for="route in routes"
        :key="route.id"
        :data-testid="`route-card-${route.id}`"
        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
      >
        <div class="flex items-start justify-between gap-2">
          <h3 class="font-bold tracking-tight">{{ route.name }}</h3>
          <span
            class="rounded-full px-2 py-0.5 text-xs font-semibold"
            :style="{
              color: 'var(--color-text-secondary)',
              background: 'var(--color-surface)',
            }"
          >
            {{ routeTypeLabel(route.route_type) }}
          </span>
        </div>
        <dl class="mt-2 space-y-1 text-xs text-[var(--color-text-secondary)]">
          <div class="flex justify-between">
            <dt>Asal</dt>
            <dd>{{ route.origin_port?.name ?? '-' }}</dd>
          </div>
          <div class="flex justify-between">
            <dt>Tujuan</dt>
            <dd>{{ route.destination_port?.name ?? '-' }}</dd>
          </div>
          <div class="flex justify-between">
            <dt>Bidirectional</dt>
            <dd>{{ route.bidirectional ? 'Ya' : 'Tidak' }}</dd>
          </div>
        </dl>
      </li>
    </ul>

    <nav
      v-if="pagination && pagination.last_page > 1"
      aria-label="Paginasi"
      class="mt-6 flex items-center justify-center gap-4"
    >
      <button
        class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm disabled:opacity-50"
        :disabled="page <= 1"
        @click="prevPage"
      >
        Sebelumnya
      </button>
      <span class="text-sm text-[var(--color-text-secondary)]">
        Halaman {{ page }} dari {{ pagination.last_page }}
      </span>
      <button
        class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm disabled:opacity-50"
        :disabled="page >= pagination.last_page"
        @click="nextPage"
      >
        Berikutnya
      </button>
    </nav>
  </div>
</template>

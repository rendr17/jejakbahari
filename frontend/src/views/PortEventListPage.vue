<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  fetchPortEvents,
  fetchPorts,
  type PortEvent,
  type Port,
  type PaginationMeta,
} from '../api'

const route = useRoute()
const router = useRouter()
const port = ref<Port | null>(null)
const events = ref<PortEvent[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const page = ref(1)
const pagination = ref<PaginationMeta | null>(null)

const portId = computed(() => String(route.params.id ?? ''))

async function loadPort(): Promise<void> {
  try {
    const result = await fetchPorts({ per_page: 100 })
    port.value = result.ports.find((p) => p.id === portId.value) ?? null
  } catch {
    // Silent fail
  }
}

async function loadEvents(): Promise<void> {
  try {
    loading.value = true
    error.value = null
    const result = await fetchPortEvents(portId.value, {
      page: page.value,
      per_page: 20,
    })
    events.value = result.events
    pagination.value = result.pagination
  } catch (e) {
    error.value =
      e instanceof Error ? e.message : 'Gagal memuat event pelabuhan'
  } finally {
    loading.value = false
  }
}

function prevPage(): void {
  page.value = page.value - 1
  loadEvents()
}

function nextPage(): void {
  page.value = page.value + 1
  loadEvents()
}

function goBack(): void {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/pelabuhan')
  }
}

const EVENT_LABELS: Record<string, string> = {
  ENTERED_GEOFENCE: 'Memasuki area',
  ARRIVED: 'Tiba',
  DEPARTED: 'Berangkat',
  EXITED_GEOFENCE: 'Meninggalkan area',
}

const EVENT_COLORS: Record<string, string> = {
  ENTERED_GEOFENCE: 'var(--color-info)',
  ARRIVED: 'var(--color-success)',
  DEPARTED: 'var(--color-warning)',
  EXITED_GEOFENCE: 'var(--color-text-secondary)',
}

onMounted(() => {
  loadPort()
  loadEvents()
})

watch(portId, () => {
  page.value = 1
  loadPort()
  loadEvents()
})
</script>

<template>
  <div data-testid="port-event-page" class="site-container py-8">
    <button
      class="mb-4 text-sm text-[var(--color-text-secondary)] underline"
      @click="goBack"
    >
      &larr; Kembali
    </button>

    <header class="mb-6">
      <h1 class="text-2xl font-extrabold tracking-tight md:text-3xl">
        Event Pelabuhan{{ port ? ` — ${port.name}` : '' }}
      </h1>
      <p class="mt-2 text-sm text-[var(--color-text-secondary)]">
        Riwayat event geofence: tiba, berangkat, masuk, dan keluar area
        pelabuhan.
      </p>
    </header>

    <div
      v-if="loading"
      data-testid="port-event-loading"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Memuat event pelabuhan…
    </div>

    <div
      v-else-if="error"
      data-testid="port-event-error"
      role="alert"
      aria-live="assertive"
      class="rounded-lg border border-[var(--color-danger)] bg-[var(--color-surface)] p-4 text-sm text-[var(--color-danger)]"
    >
      {{ error }}
      <button class="ml-2 underline" @click="loadEvents">Coba lagi</button>
    </div>

    <div
      v-else-if="events.length === 0"
      data-testid="port-event-empty"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Belum ada event tercatat untuk pelabuhan ini.
    </div>

    <ul v-else class="space-y-2">
      <li
        v-for="event in events"
        :key="event.id"
        :data-testid="`port-event-${event.id}`"
        class="flex items-center gap-3 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
      >
        <span
          class="inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full"
          :style="{
            background:
              EVENT_COLORS[event.event_type] ?? 'var(--color-text-secondary)',
          }"
          aria-hidden="true"
        />
        <div class="flex-1">
          <p class="text-sm font-semibold">
            {{ EVENT_LABELS[event.event_type] ?? event.event_type }}
          </p>
          <p class="text-xs text-[var(--color-text-secondary)]">
            Kapal {{ event.vessel_name ?? 'tidak diketahui' }}
          </p>
        </div>
        <time
          class="font-mono text-xs tabular-nums text-[var(--color-text-secondary)]"
          :datetime="event.event_time"
        >
          {{ new Date(event.event_time).toLocaleString('id-ID') }}
        </time>
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

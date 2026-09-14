<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed, shallowRef } from 'vue'
import VesselMap from '../components/VesselMap.vue'
import FreshnessLegend from '../components/FreshnessLegend.vue'
import VesselCard from '../components/VesselCard.vue'
import PortLayer from '../components/PortLayer.vue'
import RouteLayer from '../components/RouteLayer.vue'
import { useRealtimePositions } from '../composables/useRealtimePositions'
import {
  fetchLatestPositions,
  fetchVessels,
  fetchPorts,
  fetchRoutes,
  type LatestPosition,
  type VesselSummary,
  type Port,
  type RouteSummary,
} from '../api'

const positions = ref<LatestPosition[]>([])
const vessels = ref<VesselSummary[]>([])
const ports = ref<Port[]>([])
const routes = ref<RouteSummary[]>([])
const mapInstance = shallowRef<unknown>(null)
const selectedId = ref<string | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const tileError = ref(false)
let refreshTimer: ReturnType<typeof setInterval> | null = null

const selectedPosition = computed(
  () => positions.value.find((p) => p.vessel_id === selectedId.value) ?? null,
)

// Realtime WebSocket subscription with REST fallback
const { status: realtimeStatus } = useRealtimePositions((updated) => {
  const idx = positions.value.findIndex(
    (p) => p.vessel_id === updated.vessel_id,
  )
  if (idx >= 0) {
    positions.value[idx] = updated
  } else {
    positions.value.push(updated)
  }
})

async function loadPositions(): Promise<void> {
  try {
    error.value = null
    positions.value = await fetchLatestPositions()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat data posisi'
  } finally {
    loading.value = false
  }
}

async function loadVesselList(): Promise<void> {
  try {
    vessels.value = (await fetchVessels({ per_page: 100 })).vessels
  } catch {
    // Silent fail — fallback list is best-effort
  }
}

async function loadPortsAndRoutes(): Promise<void> {
  try {
    const [portsResult, routesResult] = await Promise.all([
      fetchPorts({ per_page: 100 }),
      fetchRoutes({ per_page: 100 }),
    ])
    ports.value = portsResult.ports
    routes.value = routesResult.routes
  } catch {
    // Silent fail — port/route layers are best-effort
  }
}

function onMapReady(map: unknown): void {
  mapInstance.value = map
}

onMounted(() => {
  loadPositions()
  loadVesselList()
  loadPortsAndRoutes()
  // REST resync fallback every 60s even when WebSocket is connected
  refreshTimer = setInterval(loadPositions, 60_000)
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
})
</script>

<template>
  <div
    data-testid="map-page"
    class="relative h-[calc(100svh-4rem)] w-full overflow-hidden bg-slate-900"
  >
    <VesselMap
      :positions="positions"
      :selected-id="selectedId"
      @select="selectedId = $event"
      @tile-error="tileError = true"
      @ready="onMapReady"
    />

    <RouteLayer
      v-if="mapInstance"
      :map="mapInstance"
      :routes="routes"
      :ports="ports"
    />
    <PortLayer v-if="mapInstance" :map="mapInstance" :ports="ports" />

    <FreshnessLegend />

    <!-- Realtime connection status indicator -->
    <div
      v-if="realtimeStatus !== 'connected'"
      data-testid="realtime-status"
      role="status"
      aria-live="polite"
      class="absolute bottom-4 left-1/2 z-10 -translate-x-1/2 rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-1 text-xs text-[var(--color-text-secondary)] shadow-md"
    >
      <span v-if="realtimeStatus === 'connecting'"
        >Menghubungkan realtime…</span
      >
      <span v-else-if="realtimeStatus === 'fallback'">Mode polling (60s)</span>
      <span v-else-if="realtimeStatus === 'disconnected'"
        >Realtime terputus, mencoba ulang…</span
      >
    </div>

    <VesselCard
      v-if="selectedPosition"
      :position="selectedPosition"
      @close="selectedId = null"
    />

    <div
      v-if="tileError && !loading"
      role="alert"
      class="absolute left-1/2 top-4 z-10 -translate-x-1/2 max-w-xs rounded-lg border border-[var(--color-attention)] bg-[var(--color-surface)] p-4 shadow-lg md:left-auto md:right-4 md:top-4 md:translate-x-0"
    >
      <p class="text-sm font-semibold text-[var(--color-attention)]">
        Tile peta gagal dimuat
      </p>
      <p class="mt-1 text-xs text-[var(--color-text-secondary)]">
        Menampilkan daftar kapal sebagai alternatif.
      </p>
    </div>

    <div
      v-if="tileError && vessels.length > 0"
      class="absolute left-4 right-4 top-4 z-10 max-h-80 overflow-y-auto rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3 shadow-lg md:right-auto md:w-72"
    >
      <p
        class="mb-2 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-text-secondary)]"
      >
        Daftar Kapal
      </p>
      <ul class="space-y-1.5 text-sm">
        <li
          v-for="v in vessels"
          :key="v.id"
          class="flex items-center justify-between gap-2"
        >
          <span>{{ v.name }}</span>
          <span
            class="font-mono text-xs tabular-nums text-[var(--color-text-secondary)]"
          >
            {{ v.mmsi }}
          </span>
        </li>
      </ul>
    </div>

    <div
      v-if="loading"
      data-testid="map-loading"
      role="status"
      aria-live="polite"
      class="absolute inset-0 z-20 flex items-center justify-center bg-slate-900/80"
    >
      <div class="text-center">
        <div
          class="mx-auto h-10 w-10 animate-spin rounded-full border-2 border-slate-600 border-t-[var(--color-primary)]"
          aria-hidden="true"
        />
        <p class="mt-3 text-sm text-slate-400">Memuat peta kapal…</p>
      </div>
    </div>

    <div
      v-else-if="error"
      data-testid="map-error"
      role="alert"
      aria-live="assertive"
      class="absolute left-1/2 top-4 z-20 -translate-x-1/2 rounded-lg border border-red-500/30 bg-red-950/80 px-4 py-2 text-sm text-red-200"
    >
      {{ error }}
      <button
        data-testid="map-error-retry"
        class="ml-2 underline"
        @click="loadPositions"
      >
        Coba lagi
      </button>
    </div>

    <div
      v-else-if="positions.length === 0"
      data-testid="map-empty"
      role="status"
      aria-live="polite"
      class="absolute left-1/2 top-4 z-20 -translate-x-1/2 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2 text-sm text-[var(--color-text-secondary)]"
    >
      Belum ada posisi kapal yang tersedia.
    </div>
  </div>
</template>

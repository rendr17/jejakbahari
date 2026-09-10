<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed } from 'vue'
import VesselMap from '../components/VesselMap.vue'
import FreshnessLegend from '../components/FreshnessLegend.vue'
import VesselCard from '../components/VesselCard.vue'
import { fetchLatestPositions, type LatestPosition } from '../api'

const positions = ref<LatestPosition[]>([])
const selectedId = ref<string | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
let refreshTimer: ReturnType<typeof setInterval> | null = null

const selectedPosition = computed(
  () => positions.value.find((p) => p.vessel_id === selectedId.value) ?? null,
)

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

onMounted(() => {
  loadPositions()
  refreshTimer = setInterval(loadPositions, 30_000)
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
})
</script>

<template>
  <div
    class="relative h-[calc(100svh-4rem)] w-full overflow-hidden bg-slate-900"
  >
    <VesselMap
      :positions="positions"
      :selected-id="selectedId"
      @select="selectedId = $event"
    />

    <FreshnessLegend />

    <VesselCard
      v-if="selectedPosition"
      :position="selectedPosition"
      @close="selectedId = null"
    />

    <div
      v-if="loading"
      class="absolute inset-0 z-20 flex items-center justify-center bg-slate-900/80"
    >
      <div class="text-center">
        <div
          class="mx-auto h-10 w-10 animate-spin rounded-full border-2 border-slate-600 border-t-[var(--color-primary)]"
        />
        <p class="mt-3 text-sm text-slate-400">Memuat peta kapal…</p>
      </div>
    </div>

    <div
      v-else-if="error"
      class="absolute left-1/2 top-4 z-20 -translate-x-1/2 rounded-lg border border-red-500/30 bg-red-950/80 px-4 py-2 text-sm text-red-200"
    >
      {{ error }}
      <button class="ml-2 underline" @click="loadPositions">Coba lagi</button>
    </div>

    <div
      v-else-if="positions.length === 0"
      class="absolute left-1/2 top-4 z-20 -translate-x-1/2 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2 text-sm text-[var(--color-text-secondary)]"
    >
      Belum ada posisi kapal yang tersedia.
    </div>
  </div>
</template>

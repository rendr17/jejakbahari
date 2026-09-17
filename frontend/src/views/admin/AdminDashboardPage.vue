<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import { fetchLatestPositions, type LatestPosition } from '../../api'
import {
  listAdminVessels,
  listAdminPorts,
  listAdminRoutes,
  listAdminOperators,
} from '../../adminApi'

interface PipelineStatus {
  status: 'OPERATIONAL' | 'DEGRADED' | 'DOWN'
  database: string
  worker: {
    status: string
    worker_id: string | null
    last_heartbeat: string | null
  }
  data: {
    tracked_vessels: number
    vessels_with_positions: number
    last_position_received_at: string | null
  }
}

const status = ref<PipelineStatus | null>(null)
const counts = ref({ vessels: 0, ports: 0, routes: 0, operators: 0 })
const liveCount = ref(0)
const loading = ref(true)

const heartbeatAge = computed(() => {
  const ts = status.value?.worker.last_heartbeat
  if (!ts) return null
  return Math.max(0, Math.round((Date.now() - new Date(ts).getTime()) / 1000))
})

const positionAge = computed(() => {
  const ts = status.value?.data.last_position_received_at
  if (!ts) return null
  return Math.max(0, Math.round((Date.now() - new Date(ts).getTime()) / 1000))
})

onMounted(async () => {
  try {
    const [statusRes, vessels, ports, routes, operators, positions] =
      await Promise.all([
        fetch(`${import.meta.env.VITE_API_BASE_URL ?? '/api/v1'}/status`).then(
          (r) => r.json(),
        ),
        listAdminVessels({ per_page: 1 }),
        listAdminPorts({ per_page: 1 }),
        listAdminRoutes({ per_page: 1 }),
        listAdminOperators({ per_page: 1 }),
        fetchLatestPositions(),
      ])
    status.value = statusRes.data ?? null
    counts.value = {
      vessels: vessels.pagination?.total ?? 0,
      ports: ports.pagination?.total ?? 0,
      routes: routes.pagination?.total ?? 0,
      operators: operators.pagination?.total ?? 0,
    }
    liveCount.value = positions.filter(
      (p: LatestPosition) => p.freshness === 'LIVE',
    ).length
  } catch {
    // Dashboard degrades gracefully — cards show zeros
  } finally {
    loading.value = false
  }
})

const pipelineColor: Record<string, string> = {
  OPERATIONAL: 'text-emerald-400',
  DEGRADED: 'text-amber-400',
  DOWN: 'text-red-400',
}
</script>

<template>
  <div data-testid="admin-dashboard">
    <h1 class="text-2xl font-extrabold tracking-tight">Dashboard</h1>

    <div
      v-if="loading"
      class="py-12 text-sm text-[var(--color-text-secondary)]"
    >
      Memuat…
    </div>

    <template v-else>
      <div
        v-if="status"
        class="mt-4 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
        data-testid="pipeline-status"
      >
        <p
          class="text-xs uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          Pipeline Status
        </p>
        <p
          class="mt-1 text-xl font-bold"
          :class="pipelineColor[status.status] ?? ''"
          data-testid="pipeline-state"
        >
          {{ status.status }}
        </p>
        <dl
          class="mt-3 grid grid-cols-2 gap-2 text-xs text-[var(--color-text-secondary)] md:grid-cols-4"
        >
          <div>
            <dt>Worker</dt>
            <dd class="font-mono tabular-nums">
              {{ status.worker.status }}
              <span v-if="heartbeatAge !== null">
                ({{ heartbeatAge }}s lalu)</span
              >
            </dd>
          </div>
          <div>
            <dt>Posisi terakhir</dt>
            <dd class="font-mono tabular-nums">
              {{ positionAge === null ? '-' : `${positionAge}s lalu` }}
            </dd>
          </div>
          <div>
            <dt>Vessel tracked</dt>
            <dd class="font-mono tabular-nums">
              {{ status.data.tracked_vessels }}
            </dd>
          </div>
          <div>
            <dt>Dengan posisi</dt>
            <dd class="font-mono tabular-nums">
              {{ status.data.vessels_with_positions }}
            </dd>
          </div>
        </dl>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
        <div
          class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
        >
          <p class="text-xs text-[var(--color-text-secondary)]">Kapal</p>
          <p
            class="mt-1 text-2xl font-bold tabular-nums"
            data-testid="count-vessels"
          >
            {{ counts.vessels }}
          </p>
        </div>
        <div
          class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
        >
          <p class="text-xs text-[var(--color-text-secondary)]">Pelabuhan</p>
          <p class="mt-1 text-2xl font-bold tabular-nums">{{ counts.ports }}</p>
        </div>
        <div
          class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
        >
          <p class="text-xs text-[var(--color-text-secondary)]">Lintasan</p>
          <p class="mt-1 text-2xl font-bold tabular-nums">
            {{ counts.routes }}
          </p>
        </div>
        <div
          class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
        >
          <p class="text-xs text-[var(--color-text-secondary)]">Posisi LIVE</p>
          <p class="mt-1 text-2xl font-bold tabular-nums text-emerald-400">
            {{ liveCount }}
          </p>
        </div>
      </div>
    </template>
  </div>
</template>

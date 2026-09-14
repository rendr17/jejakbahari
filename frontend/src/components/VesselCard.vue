<script setup lang="ts">
import type { LatestPosition } from '../api'
import {
  FRESHNESS_COLORS,
  FRESHNESS_LABELS,
  type Freshness,
} from '../freshness'

defineProps<{
  position: LatestPosition
}>()

defineEmits<{
  close: []
}>()

function formatTime(ts: string | null): string {
  if (!ts) return '-'
  return new Date(ts).toLocaleString('id-ID', {
    timeZone: 'UTC',
    hour: '2-digit',
    minute: '2-digit',
    day: 'numeric',
    month: 'short',
  })
}
</script>

<template>
  <div
    data-testid="vessel-card"
    class="absolute bottom-4 left-4 right-4 z-10 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4 shadow-lg md:bottom-auto md:left-auto md:right-4 md:top-4 md:w-72"
  >
    <div class="flex items-start justify-between">
      <div>
        <p
          class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary)]"
        >
          Detail Kapal
        </p>
        <h3
          data-testid="vessel-card-name"
          class="mt-1 text-lg font-bold tracking-tight"
        >
          {{ position.name ?? 'Unknown' }}
        </h3>
      </div>
      <button
        data-testid="vessel-card-close"
        aria-label="Tutup detail kapal"
        class="text-[var(--color-text-secondary)] hover:text-[var(--color-text)]"
        @click="$emit('close')"
      >
        ✕
      </button>
    </div>

    <dl class="mt-3 space-y-2 text-sm">
      <div class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">MMSI</dt>
        <dd class="font-mono tabular-nums">{{ position.mmsi ?? '-' }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">Status</dt>
        <dd
          class="flex items-center gap-1.5 font-semibold"
          :style="{ color: FRESHNESS_COLORS[position.freshness as Freshness] }"
        >
          <span
            class="inline-block h-2 w-2 rounded-full"
            :style="{
              background: FRESHNESS_COLORS[position.freshness as Freshness],
            }"
          />
          {{ FRESHNESS_LABELS[position.freshness as Freshness] }}
        </dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">Koordinat</dt>
        <dd class="font-mono text-xs tabular-nums">
          {{ position.latitude.toFixed(4) }},
          {{ position.longitude.toFixed(4) }}
        </dd>
      </div>
      <div v-if="position.sog_knots != null" class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">Kecepatan</dt>
        <dd class="tabular-nums">{{ position.sog_knots }} kn</dd>
      </div>
      <div v-if="position.cog_degrees != null" class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">Arah</dt>
        <dd class="tabular-nums">{{ position.cog_degrees }}°</dd>
      </div>
      <div v-if="position.destination_text" class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">Tujuan</dt>
        <dd>{{ position.destination_text }}</dd>
      </div>
      <div class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">Update</dt>
        <dd class="text-xs">{{ formatTime(position.source_timestamp) }}</dd>
      </div>
    </dl>

    <p class="mt-3 text-xs text-[var(--color-text-secondary)]">
      Data AIS bersifat indikatif, bukan untuk navigasi.
    </p>
  </div>
</template>

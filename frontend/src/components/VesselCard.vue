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
    class="absolute right-4 top-4 z-10 w-72 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4 shadow-lg"
  >
    <div class="flex items-start justify-between">
      <div>
        <p
          class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary)]"
        >
          Detail Kapal
        </p>
        <h3 class="mt-1 text-lg font-bold tracking-tight">
          {{ position.name ?? 'Unknown' }}
        </h3>
      </div>
      <button
        class="text-[var(--color-text-secondary)] hover:text-[var(--color-text)]"
        @click="$emit('close')"
      >
        ✕
      </button>
    </div>

    <dl class="mt-3 space-y-2 text-sm">
      <div class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">MMSI</dt>
        <dd class="font-mono">{{ position.mmsi ?? '-' }}</dd>
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
        <dd class="font-mono text-xs">
          {{ position.latitude.toFixed(4) }},
          {{ position.longitude.toFixed(4) }}
        </dd>
      </div>
      <div v-if="position.sog_knots != null" class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">Kecepatan</dt>
        <dd>{{ position.sog_knots }} kn</dd>
      </div>
      <div v-if="position.cog_degrees != null" class="flex justify-between">
        <dt class="text-[var(--color-text-secondary)]">Arah</dt>
        <dd>{{ position.cog_degrees }}°</dd>
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

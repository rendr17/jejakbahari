<script setup lang="ts">
import {
  FRESHNESS_COLORS,
  FRESHNESS_LABELS,
  type Freshness,
} from '../freshness'

const items: Freshness[] = ['LIVE', 'DELAYED', 'STALE', 'OFFLINE']
</script>

<template>
  <div
    class="absolute bottom-4 left-4 z-10 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3 shadow-lg"
  >
    <p
      class="mb-2 font-mono text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-text-secondary)]"
    >
      Status Data
    </p>
    <ul class="space-y-1.5">
      <li
        v-for="status in items"
        :key="status"
        class="flex items-center gap-2 text-sm"
      >
        <span
          class="inline-block h-3 w-3 rounded-full"
          :style="{ background: FRESHNESS_COLORS[status] }"
        />
        {{ FRESHNESS_LABELS[status] }}
      </li>
    </ul>
  </div>
</template>

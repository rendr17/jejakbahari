<script setup lang="ts">
import {
  FRESHNESS_COLORS,
  FRESHNESS_LABELS,
  type Freshness,
} from '../freshness'

defineProps<{
  freshness: Freshness
}>()
</script>

<template>
  <span
    class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-semibold"
    :style="{
      color: FRESHNESS_COLORS[freshness as Freshness],
      background: 'var(--color-surface)',
    }"
  >
    <span
      class="inline-block h-2 w-2 rounded-full"
      :style="{ background: FRESHNESS_COLORS[freshness as Freshness] }"
      aria-hidden="true"
    />
    {{ FRESHNESS_LABELS[freshness as Freshness] }}
  </span>
</template>

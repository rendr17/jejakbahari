<script setup lang="ts">
import { ref, watch, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { fetchVessels, type VesselSummary } from '../api'

const router = useRouter()

const query = ref('')
const results = ref<VesselSummary[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const isOpen = ref(false)
const activeIndex = ref(-1)

let debounceTimer: ReturnType<typeof setTimeout> | null = null

const hasQuery = computed(() => query.value.trim().length >= 2)
const hasResults = computed(() => results.value.length > 0)
const noResults = computed(
  () => hasQuery.value && !loading.value && !hasResults.value && !error.value,
)

async function performSearch(): Promise<void> {
  if (!hasQuery.value) {
    results.value = []
    return
  }
  try {
    loading.value = true
    error.value = null
    results.value = (
      await fetchVessels({ q: query.value.trim(), per_page: 20 })
    ).vessels
    activeIndex.value = -1
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal mencari'
  } finally {
    loading.value = false
  }
}

watch(query, () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  if (!hasQuery.value) {
    results.value = []
    isOpen.value = false
    return
  }
  isOpen.value = true
  debounceTimer = setTimeout(performSearch, 300)
})

function selectResult(vessel: VesselSummary): void {
  isOpen.value = false
  query.value = ''
  results.value = []
  router.push(`/kapal/${vessel.id}`)
}

function handleKeydown(e: KeyboardEvent): void {
  if (!isOpen.value) return
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    activeIndex.value = Math.min(
      activeIndex.value + 1,
      results.value.length - 1,
    )
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    activeIndex.value = Math.max(activeIndex.value - 1, 0)
  } else if (e.key === 'Enter') {
    e.preventDefault()
    if (activeIndex.value >= 0 && activeIndex.value < results.value.length) {
      selectResult(results.value[activeIndex.value])
    }
  } else if (e.key === 'Escape') {
    isOpen.value = false
  }
}

function handleBlur(): void {
  setTimeout(() => {
    isOpen.value = false
  }, 200)
}

function handleFocus(): void {
  if (hasQuery.value) isOpen.value = true
}

onUnmounted(() => {
  if (debounceTimer) clearTimeout(debounceTimer)
})

function escapeHtml(text: string): string {
  return text
    .replace(/&/g, '&')
    .replace(/</g, '<')
    .replace(/>/g, '>')
    .replace(/"/g, '"')
    .replace(/'/g, '&#039;')
}

function highlightMatch(text: string, query: string): string {
  const q = query.trim()
  const escaped = escapeHtml(text)
  if (!q) return escaped
  const pattern = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
  return escaped.replace(new RegExp(`(${pattern})`, 'gi'), '<mark>$1</mark>')
}
</script>

<template>
  <div class="relative w-full">
    <label for="vessel-search" class="sr-only"
      >Cari kapal berdasarkan nama, MMSI, atau IMO</label
    >
    <input
      id="vessel-search"
      v-model="query"
      type="search"
      placeholder="Cari kapal (nama, MMSI, IMO)…"
      autocomplete="off"
      data-testid="vessel-search-input"
      class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2.5 text-sm text-[var(--color-text-primary)] placeholder:text-[var(--color-text-secondary)] focus:border-[var(--color-primary)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary)]"
      @keydown="handleKeydown"
      @blur="handleBlur"
      @focus="handleFocus"
    />

    <div
      v-if="isOpen && (loading || hasResults || noResults || error)"
      data-testid="vessel-search-results"
      class="absolute left-0 right-0 top-full z-30 mt-1 max-h-80 overflow-y-auto rounded-lg border border-[var(--color-border)] bg-[var(--color-surface-elevated)] shadow-2xl"
      role="listbox"
      aria-label="Hasil pencarian kapal"
    >
      <div
        v-if="loading"
        role="status"
        class="p-4 text-sm text-[var(--color-text-secondary)]"
      >
        Mencari…
      </div>
      <div
        v-else-if="error"
        role="alert"
        class="p-4 text-sm text-[var(--color-danger)]"
      >
        {{ error }}
      </div>
      <div
        v-else-if="noResults"
        role="status"
        class="p-4 text-sm text-[var(--color-text-secondary)]"
      >
        Tidak ditemukan kapal untuk "{{ query.trim() }}".
      </div>
      <ul v-else-if="hasResults" role="presentation">
        <li
          v-for="(vessel, index) in results"
          :key="vessel.id"
          role="option"
          :aria-selected="index === activeIndex"
          :data-testid="`vessel-search-result-${index}`"
          class="cursor-pointer border-b border-[var(--color-border)] px-4 py-3 last:border-b-0 hover:bg-[var(--color-surface)]"
          :class="{ 'bg-[var(--color-surface)]': index === activeIndex }"
          @click="selectResult(vessel)"
          @mouseenter="activeIndex = index"
        >
          <div class="flex items-center justify-between gap-2">
            <div>
              <p
                class="font-semibold text-[var(--color-text-primary)]"
                v-html="highlightMatch(vessel.name, query)"
              />
              <p
                class="font-mono text-xs tabular-nums text-[var(--color-text-secondary)]"
                v-html="
                  highlightMatch(
                    `MMSI ${vessel.mmsi}${vessel.imo ? ` · IMO ${vessel.imo}` : ''}`,
                    query,
                  )
                "
              />
            </div>
            <span
              class="rounded-full px-2 py-0.5 text-xs font-semibold"
              :style="{
                color: 'var(--color-text-secondary)',
                background: 'var(--color-surface)',
              }"
            >
              {{ vessel.vessel_category }}
            </span>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

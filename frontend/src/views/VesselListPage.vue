<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import VesselSearch from '../components/VesselSearch.vue'
import FreshnessBadge from '../components/FreshnessBadge.vue'
import { fetchVessels, type VesselSummary } from '../api'

const router = useRouter()

const vessels = ref<VesselSummary[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const page = ref(1)
const perPage = ref(20)
const total = ref(0)

async function loadVessels(): Promise<void> {
  try {
    loading.value = true
    error.value = null
    vessels.value = await fetchVessels({
      page: page.value,
      per_page: perPage.value,
    })
    total.value = vessels.value.length
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat daftar kapal'
  } finally {
    loading.value = false
  }
}

onMounted(loadVessels)
watch(page, loadVessels)

function openDetail(vessel: VesselSummary): void {
  router.push(`/kapal/${vessel.id}`)
}

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
  <div data-testid="vessel-list-page" class="site-container py-8">
    <header class="mb-6">
      <h1 class="text-2xl font-extrabold tracking-tight md:text-3xl">
        Daftar Kapal
      </h1>
      <p class="mt-2 text-sm text-[var(--color-text-secondary)]">
        Kapal RoRo/RoPax Indonesia terverifikasi dengan data AIS publik.
      </p>
    </header>

    <div class="mb-6 max-w-2xl">
      <VesselSearch />
    </div>

    <div
      v-if="loading"
      data-testid="vessel-list-loading"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Memuat daftar kapal…
    </div>

    <div
      v-else-if="error"
      data-testid="vessel-list-error"
      role="alert"
      aria-live="assertive"
      class="rounded-lg border border-[var(--color-danger)] bg-[var(--color-surface)] p-4 text-sm text-[var(--color-danger)]"
    >
      {{ error }}
      <button
        data-testid="vessel-list-retry"
        class="ml-2 underline"
        @click="loadVessels"
      >
        Coba lagi
      </button>
    </div>

    <div
      v-else-if="vessels.length === 0"
      data-testid="vessel-list-empty"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Belum ada kapal yang terverifikasi.
    </div>

    <div v-else>
      <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <li
          v-for="vessel in vessels"
          :key="vessel.id"
          :data-testid="`vessel-card-${vessel.id}`"
          class="cursor-pointer rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4 transition hover:border-[var(--color-primary)]"
          role="button"
          tabindex="0"
          @click="openDetail(vessel)"
          @keydown.enter="openDetail(vessel)"
          @keydown.space.prevent="openDetail(vessel)"
        >
          <div class="flex items-start justify-between gap-2">
            <div>
              <h3 class="font-bold tracking-tight">{{ vessel.name }}</h3>
              <p
                class="mt-1 font-mono text-xs tabular-nums text-[var(--color-text-secondary)]"
              >
                MMSI {{ vessel.mmsi }}
              </p>
            </div>
            <FreshnessBadge :freshness="vessel.freshness" />
          </div>
          <dl class="mt-3 space-y-1 text-xs text-[var(--color-text-secondary)]">
            <div class="flex justify-between">
              <dt>Operator</dt>
              <dd>{{ vessel.operator?.name ?? '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt>Kategori</dt>
              <dd>{{ vessel.vessel_category }}</dd>
            </div>
            <div class="flex justify-between">
              <dt>Update</dt>
              <dd class="tabular-nums">
                {{ formatTime(vessel.last_position_at) }}
              </dd>
            </div>
          </dl>
        </li>
      </ul>

      <nav
        v-if="total >= perPage"
        aria-label="Paginasi"
        class="mt-6 flex items-center justify-center gap-4"
      >
        <button
          data-testid="vessel-list-prev"
          class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm disabled:opacity-50"
          :disabled="page <= 1"
          @click="page--"
        >
          Sebelumnya
        </button>
        <span class="text-sm text-[var(--color-text-secondary)]"
          >Halaman {{ page }}</span
        >
        <button
          data-testid="vessel-list-next"
          class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm disabled:opacity-50"
          :disabled="vessels.length < perPage"
          @click="page++"
        >
          Berikutnya
        </button>
      </nav>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import FreshnessBadge from '../components/FreshnessBadge.vue'
import { FRESHNESS_LABELS, type Freshness } from '../freshness'
import { fetchVesselDetail, type VesselDetail } from '../api'

const route = useRoute()
const router = useRouter()

const vessel = ref<VesselDetail | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const vesselId = computed(() => route.params.id as string)

async function loadVessel(): Promise<void> {
  try {
    loading.value = true
    error.value = null
    vessel.value = await fetchVesselDetail(vesselId.value)
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat detail kapal'
  } finally {
    loading.value = false
  }
}

function goBack(): void {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/kapal')
  }
}

onMounted(loadVessel)
watch(vesselId, loadVessel)

function formatTime(ts: string | null): string {
  if (!ts) return '-'
  return new Date(ts).toLocaleString('id-ID', {
    timeZone: 'UTC',
    hour: '2-digit',
    minute: '2-digit',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function relativeTime(ts: string | null): string {
  if (!ts) return 'tidak ada data'
  const diff = Date.now() - new Date(ts).getTime()
  const minutes = Math.floor(diff / 60000)
  if (minutes < 1) return 'baru saja'
  if (minutes < 60) return `${minutes} menit lalu`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours} jam lalu`
  const days = Math.floor(hours / 24)
  return `${days} hari lalu`
}

function evidenceTypeLabel(type: string): string {
  const labels: Record<string, string> = {
    MMSI_MATCH: 'Cocok MMSI',
    IMO_MATCH: 'Cocok IMO',
    OPERATOR_LISTING: 'Daftar Operator',
    PORT_SCHEDULE: 'Jadwal Pelabuhan',
    ROUTE_SCHEDULE: 'Jadwal Lintasan',
    REGISTRY_RECORD: 'Catatan Registri',
    NEWS_ARTICLE: 'Artikel Berita',
    OTHER: 'Lainnya',
  }
  return labels[type] ?? type
}
</script>

<template>
  <div data-testid="vessel-detail-page" class="site-container py-8">
    <button
      data-testid="vessel-detail-back"
      class="mb-4 inline-flex items-center gap-1 text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]"
      @click="goBack"
    >
      ← Kembali
    </button>

    <div
      v-if="loading"
      data-testid="vessel-detail-loading"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Memuat detail kapal…
    </div>

    <div
      v-else-if="error"
      data-testid="vessel-detail-error"
      role="alert"
      aria-live="assertive"
      class="rounded-lg border border-[var(--color-danger)] bg-[var(--color-surface)] p-4 text-sm text-[var(--color-danger)]"
    >
      {{ error }}
      <button
        data-testid="vessel-detail-retry"
        class="ml-2 underline"
        @click="loadVessel"
      >
        Coba lagi
      </button>
    </div>

    <article v-else-if="vessel" class="grid gap-6 lg:grid-cols-[1fr_24rem]">
      <section>
        <header class="mb-4">
          <div class="flex items-start justify-between gap-4">
            <div>
              <h1 class="text-2xl font-extrabold tracking-tight md:text-3xl">
                {{ vessel.name }}
              </h1>
              <p class="mt-1 text-sm text-[var(--color-text-secondary)]">
                {{ vessel.operator?.name ?? 'Operator tidak diketahui' }}
              </p>
            </div>
            <FreshnessBadge :freshness="vessel.freshness" />
          </div>
          <p class="mt-2 text-xs text-[var(--color-text-secondary)]">
            {{ FRESHNESS_LABELS[vessel.freshness as Freshness] }} · diperbarui
            {{ relativeTime(vessel.last_position_at) }}
          </p>
        </header>

        <section v-if="vessel.latest_position" class="mb-6">
          <h2
            class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-text-secondary)]"
          >
            Posisi Terakhir
          </h2>
          <dl class="grid grid-cols-2 gap-3 text-sm">
            <div
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">
                Koordinat
              </dt>
              <dd class="mt-1 font-mono text-xs tabular-nums">
                {{ vessel.latest_position.latitude.toFixed(4) }},
                {{ vessel.latest_position.longitude.toFixed(4) }}
              </dd>
            </div>
            <div
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">
                Kecepatan
              </dt>
              <dd class="mt-1 tabular-nums">
                {{ vessel.latest_position.sog_knots ?? '-' }} kn
              </dd>
            </div>
            <div
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">
                Arah (COG)
              </dt>
              <dd class="mt-1 tabular-nums">
                {{ vessel.latest_position.cog_degrees ?? '-' }}°
              </dd>
            </div>
            <div
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">
                Heading
              </dt>
              <dd class="mt-1 tabular-nums">
                {{ vessel.latest_position.heading_degrees ?? '-' }}°
              </dd>
            </div>
            <div
              v-if="vessel.latest_position.nav_status"
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">
                Status Navigasi
              </dt>
              <dd class="mt-1">{{ vessel.latest_position.nav_status }}</dd>
            </div>
            <div
              v-if="vessel.latest_position.destination_text"
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">Tujuan</dt>
              <dd class="mt-1">
                {{ vessel.latest_position.destination_text }}
              </dd>
            </div>
          </dl>
          <p class="mt-2 text-xs text-[var(--color-text-secondary)]">
            Sumber: {{ formatTime(vessel.latest_position.source_timestamp) }}
          </p>
        </section>

        <section class="mb-6">
          <h2
            class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-text-secondary)]"
          >
            Identitas
          </h2>
          <dl class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
            <div
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">MMSI</dt>
              <dd class="mt-1 font-mono tabular-nums">{{ vessel.mmsi }}</dd>
            </div>
            <div
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">IMO</dt>
              <dd class="mt-1 font-mono tabular-nums">
                {{ vessel.imo ?? '-' }}
              </dd>
            </div>
            <div
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">
                Call Sign
              </dt>
              <dd class="mt-1 font-mono">{{ vessel.call_sign ?? '-' }}</dd>
            </div>
            <div
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <dt class="text-xs text-[var(--color-text-secondary)]">
                Kategori
              </dt>
              <dd class="mt-1">{{ vessel.vessel_category }}</dd>
            </div>
          </dl>
        </section>

        <section class="mb-6">
          <h2
            class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-text-secondary)]"
          >
            Verifikasi
          </h2>
          <div
            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
          >
            <div class="flex items-center gap-2">
              <span
                class="inline-block h-3 w-3 rounded-full"
                :style="{
                  background: vessel.verification.is_verified
                    ? 'var(--color-success)'
                    : 'var(--color-warning)',
                }"
                aria-hidden="true"
              />
              <span class="font-semibold">{{
                vessel.verification.status
              }}</span>
            </div>
            <p class="mt-2 text-xs text-[var(--color-text-secondary)]">
              Confidence score:
              <span class="tabular-nums">{{
                vessel.verification.confidence_score ?? '-'
              }}</span>
            </p>
          </div>
        </section>

        <section v-if="vessel.evidence.length > 0" class="mb-6">
          <h2
            class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-text-secondary)]"
          >
            Sumber dan Bukti
          </h2>
          <ul class="space-y-2">
            <li
              v-for="ev in vessel.evidence"
              :key="ev.id"
              :data-testid="`evidence-${ev.id}`"
              class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3"
            >
              <div class="flex items-start justify-between gap-2">
                <div>
                  <p class="text-sm font-semibold">
                    {{ evidenceTypeLabel(ev.evidence_type) }}
                  </p>
                  <p
                    v-if="ev.data_source"
                    class="mt-0.5 text-xs text-[var(--color-text-secondary)]"
                  >
                    {{ ev.data_source.name }}
                    <span v-if="ev.data_source.source_type">
                      · {{ ev.data_source.source_type }}</span
                    >
                  </p>
                </div>
                <span
                  v-if="ev.confidence_score != null"
                  class="font-mono text-xs tabular-nums text-[var(--color-text-secondary)]"
                >
                  {{ ev.confidence_score }}
                </span>
              </div>
              <a
                v-if="ev.source_reference"
                :href="ev.source_reference"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-2 inline-block text-xs text-[var(--color-info)] hover:underline"
              >
                Lihat sumber ↗
              </a>
            </li>
          </ul>
        </section>

        <p class="mt-6 text-xs text-[var(--color-text-secondary)]">
          {{ vessel.disclaimer }}
        </p>
      </section>

      <aside class="lg:sticky lg:top-20 lg:self-start">
        <div
          class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
        >
          <h2
            class="mb-3 text-sm font-semibold uppercase tracking-[0.18em] text-[var(--color-text-secondary)]"
          >
            Ringkasan
          </h2>
          <dl class="space-y-2 text-sm">
            <div class="flex justify-between">
              <dt class="text-[var(--color-text-secondary)]">Status</dt>
              <dd><FreshnessBadge :freshness="vessel.freshness" /></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-[var(--color-text-secondary)]">Verifikasi</dt>
              <dd>{{ vessel.verification.status }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-[var(--color-text-secondary)]">Sumber bukti</dt>
              <dd class="tabular-nums">{{ vessel.evidence.length }}</dd>
            </div>
          </dl>
        </div>
      </aside>
    </article>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import {
  createAdminEvidence,
  deleteAdminEvidence,
  getAdminVessel,
  listAdminDataSources,
  listAdminEvidence,
  rejectAdminVessel,
  verifyAdminVessel,
  type AdminDataSource,
  type AdminEvidence,
  type AdminVessel,
} from '../../adminApi'
import { ApiError2 } from '../../api'
import AdminModal from '../../components/admin/AdminModal.vue'
import { useAdminAuth } from '../../composables/useAdminAuth'

const route = useRoute()
const router = useRouter()
const { isAdmin, canVerify } = useAdminAuth()

const vesselId = route.params.id as string
const vessel = ref<AdminVessel | null>(null)
const evidence = ref<AdminEvidence[]>([])
const dataSources = ref<AdminDataSource[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const actionError = ref<string | null>(null)

const EVIDENCE_TYPES = [
  'MMSI_MATCH',
  'IMO_MATCH',
  'OPERATOR_LISTING',
  'PORT_SCHEDULE',
  'ROUTE_SCHEDULE',
  'REGISTRY_RECORD',
  'NEWS_ARTICLE',
  'OTHER',
]

async function load() {
  loading.value = true
  error.value = null
  try {
    const [v, ev] = await Promise.all([
      getAdminVessel(vesselId),
      listAdminEvidence(vesselId),
    ])
    vessel.value = v
    evidence.value = ev
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat kapal.'
  } finally {
    loading.value = false
  }
}

// ── Evidence form ────────────────────────────────────────────────

const evFormOpen = ref(false)
const evSaving = ref(false)
const evError = ref<string | null>(null)
const evForm = reactive({
  data_source_id: '',
  evidence_type: 'MMSI_MATCH',
  source_reference: '',
  observed_value_json: '{}',
  confidence_score: 70,
})

async function saveEvidence() {
  evSaving.value = true
  evError.value = null
  let observed: Record<string, unknown>
  try {
    observed = JSON.parse(evForm.observed_value_json || '{}')
    if (
      typeof observed !== 'object' ||
      observed === null ||
      Array.isArray(observed)
    ) {
      throw new Error('not-object')
    }
  } catch {
    evError.value = 'observed_value harus JSON object valid.'
    evSaving.value = false
    return
  }
  try {
    await createAdminEvidence(vesselId, {
      data_source_id: evForm.data_source_id,
      evidence_type: evForm.evidence_type,
      source_reference: evForm.source_reference,
      observed_value: observed,
      confidence_score: evForm.confidence_score,
    })
    evFormOpen.value = false
    evidence.value = await listAdminEvidence(vesselId)
  } catch (e) {
    evError.value =
      e instanceof ApiError2 ? e.message : 'Gagal menyimpan evidence.'
  } finally {
    evSaving.value = false
  }
}

async function removeEvidence(ev: AdminEvidence) {
  if (!window.confirm(`Hapus evidence ${ev.evidence_type}?`)) return
  try {
    await deleteAdminEvidence(vesselId, ev.id)
    evidence.value = await listAdminEvidence(vesselId)
  } catch (e) {
    actionError.value = e instanceof Error ? e.message : 'Hapus evidence gagal.'
  }
}

// ── Verify / reject ──────────────────────────────────────────────

const rejectOpen = ref(false)
const rejectReason = ref('')

async function verify() {
  actionError.value = null
  try {
    vessel.value = await verifyAdminVessel(vesselId)
  } catch (e) {
    actionError.value = e instanceof Error ? e.message : 'Verifikasi gagal.'
  }
}

async function confirmReject() {
  if (!rejectReason.value.trim()) return
  try {
    vessel.value = await rejectAdminVessel(vesselId, rejectReason.value.trim())
    rejectOpen.value = false
  } catch (e) {
    actionError.value = e instanceof Error ? e.message : 'Reject gagal.'
  }
}

function dataSourceName(id: string): string {
  return dataSources.value.find((d) => d.id === id)?.name ?? id.slice(0, 8)
}

onMounted(async () => {
  await load()
  try {
    const res = await listAdminDataSources({ per_page: 100 })
    dataSources.value = res.dataSources
  } catch {
    // non-blocking
  }
})
</script>

<template>
  <div data-testid="admin-vessel-detail">
    <button
      class="mb-4 text-sm text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]"
      @click="router.push('/admin/kapal')"
    >
      ← Kembali ke daftar
    </button>

    <div
      v-if="loading"
      class="py-12 text-sm text-[var(--color-text-secondary)]"
    >
      Memuat…
    </div>
    <div
      v-else-if="error"
      role="alert"
      class="rounded-lg border border-[var(--color-danger)] p-4 text-sm text-[var(--color-danger)]"
    >
      {{ error }}
      <button class="ml-2 underline" @click="load">Coba lagi</button>
    </div>

    <template v-else-if="vessel">
      <div
        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-5"
      >
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h1 class="text-2xl font-extrabold tracking-tight">
              {{ vessel.name }}
            </h1>
            <p
              class="mt-1 font-mono text-sm tabular-nums text-[var(--color-text-secondary)]"
            >
              MMSI {{ vessel.mmsi
              }}<span v-if="vessel.imo"> · IMO {{ vessel.imo }}</span>
            </p>
          </div>
          <div class="flex gap-2">
            <button
              v-if="canVerify && vessel.verification_status !== 'VERIFIED'"
              class="rounded-lg border border-emerald-500/50 px-4 py-2 text-sm font-semibold text-emerald-400 hover:bg-emerald-500/10"
              data-testid="detail-verify"
              @click="verify"
            >
              Verify
            </button>
            <button
              v-if="canVerify && vessel.verification_status !== 'REJECTED'"
              class="rounded-lg border border-amber-500/50 px-4 py-2 text-sm font-semibold text-amber-400 hover:bg-amber-500/10"
              data-testid="detail-reject"
              @click="rejectOpen = true"
            >
              Reject
            </button>
          </div>
        </div>

        <dl
          class="mt-4 grid grid-cols-2 gap-3 text-sm md:grid-cols-4"
          data-testid="vessel-meta"
        >
          <div>
            <dt class="text-xs text-[var(--color-text-secondary)]">Status</dt>
            <dd class="font-semibold">{{ vessel.verification_status }}</dd>
          </div>
          <div>
            <dt class="text-xs text-[var(--color-text-secondary)]">Kategori</dt>
            <dd>{{ vessel.vessel_category }}</dd>
          </div>
          <div>
            <dt class="text-xs text-[var(--color-text-secondary)]">Operator</dt>
            <dd>{{ vessel.operator?.name ?? '-' }}</dd>
          </div>
          <div>
            <dt class="text-xs text-[var(--color-text-secondary)]">
              Confidence
            </dt>
            <dd class="tabular-nums">{{ vessel.confidence_score }}</dd>
          </div>
          <div>
            <dt class="text-xs text-[var(--color-text-secondary)]">Aktif</dt>
            <dd>{{ vessel.active ? 'Ya' : 'Tidak' }}</dd>
          </div>
          <div>
            <dt class="text-xs text-[var(--color-text-secondary)]">Publik</dt>
            <dd>{{ vessel.public_visible ? 'Ya' : 'Tidak' }}</dd>
          </div>
        </dl>
      </div>

      <div
        v-if="actionError"
        role="alert"
        class="mt-3 rounded-lg border border-[var(--color-danger)] px-3 py-2 text-sm text-[var(--color-danger)]"
      >
        {{ actionError }}
      </div>

      <section class="mt-6">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-bold">Evidence ({{ evidence.length }})</h2>
          <button
            v-if="isAdmin"
            class="rounded-lg bg-[var(--color-primary)] px-3 py-1.5 text-sm font-semibold text-white"
            data-testid="evidence-add"
            @click="evFormOpen = true"
          >
            + Tambah Evidence
          </button>
        </div>

        <div
          v-if="evidence.length === 0"
          class="mt-3 text-sm text-[var(--color-text-secondary)]"
          data-testid="evidence-empty"
        >
          Belum ada evidence — tambahkan minimal 2 sumber independen sebelum
          verify.
        </div>

        <ul v-else class="mt-3 space-y-2">
          <li
            v-for="ev in evidence"
            :key="ev.id"
            :data-testid="`admin-evidence-${ev.id}`"
            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-3 text-sm"
          >
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="font-semibold">
                  {{ ev.evidence_type }}
                  <span
                    class="ml-2 font-mono text-xs text-[var(--color-text-secondary)]"
                  >
                    conf {{ ev.confidence_score }}
                  </span>
                </p>
                <p class="mt-1 text-xs text-[var(--color-text-secondary)]">
                  {{ dataSourceName(ev.data_source_id) }}
                </p>
                <p class="mt-1 break-all font-mono text-xs">
                  {{ ev.source_reference }}
                </p>
              </div>
              <button
                v-if="isAdmin"
                class="shrink-0 rounded border border-red-500/50 px-2 py-1 text-xs text-red-400 hover:bg-red-500/10"
                :data-testid="`evidence-delete-${ev.id}`"
                @click="removeEvidence(ev)"
              >
                Hapus
              </button>
            </div>
          </li>
        </ul>
      </section>
    </template>

    <AdminModal
      :open="evFormOpen"
      title="Tambah Evidence"
      @close="evFormOpen = false"
    >
      <form
        class="space-y-3"
        data-testid="evidence-form"
        @submit.prevent="saveEvidence"
      >
        <div>
          <label class="mb-1 block text-xs font-semibold" for="ev-source"
            >Sumber Data *</label
          >
          <select
            id="ev-source"
            v-model="evForm.data_source_id"
            required
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          >
            <option value="" disabled>Pilih sumber…</option>
            <option v-for="ds in dataSources" :key="ds.id" :value="ds.id">
              {{ ds.name }} ({{ ds.source_type }})
            </option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="ev-type"
            >Tipe Evidence *</label
          >
          <select
            id="ev-type"
            v-model="evForm.evidence_type"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          >
            <option v-for="t in EVIDENCE_TYPES" :key="t" :value="t">
              {{ t }}
            </option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="ev-ref"
            >Referensi Sumber *</label
          >
          <textarea
            id="ev-ref"
            v-model="evForm.source_reference"
            required
            maxlength="1000"
            rows="2"
            placeholder="URL atau deskripsi dokumen sumber"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          ></textarea>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="ev-observed"
            >Observed Value (JSON) *</label
          >
          <textarea
            id="ev-observed"
            v-model="evForm.observed_value_json"
            required
            rows="3"
            placeholder='{"mmsi":"525xxxxxx","ship_type":"Ro-Ro"}'
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 font-mono text-xs"
          ></textarea>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="ev-conf"
            >Confidence (0–100) *</label
          >
          <input
            id="ev-conf"
            v-model.number="evForm.confidence_score"
            type="number"
            min="0"
            max="100"
            required
            class="w-32 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          />
        </div>

        <div
          v-if="evError"
          role="alert"
          class="rounded-lg border border-[var(--color-danger)] px-3 py-2 text-sm text-[var(--color-danger)]"
        >
          {{ evError }}
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm"
            @click="evFormOpen = false"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="evSaving"
            class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
            data-testid="evidence-form-submit"
          >
            {{ evSaving ? 'Menyimpan…' : 'Simpan' }}
          </button>
        </div>
      </form>
    </AdminModal>

    <AdminModal
      :open="rejectOpen"
      :title="`Tolak ${vessel?.name ?? ''}`"
      @close="rejectOpen = false"
    >
      <form class="space-y-3" @submit.prevent="confirmReject">
        <div>
          <label class="mb-1 block text-xs font-semibold" for="d-reject-reason"
            >Alasan *</label
          >
          <textarea
            id="d-reject-reason"
            v-model="rejectReason"
            required
            maxlength="500"
            rows="3"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          ></textarea>
        </div>
        <div class="flex justify-end gap-2">
          <button
            type="button"
            class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm"
            @click="rejectOpen = false"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="!rejectReason.trim()"
            class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
          >
            Tolak
          </button>
        </div>
      </form>
    </AdminModal>
  </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { RouterLink } from 'vue-router'

import {
  createAdminVessel,
  deleteAdminVessel,
  listAdminOperators,
  listAdminVessels,
  rejectAdminVessel,
  updateAdminVessel,
  verifyAdminVessel,
  type AdminOperator,
  type AdminVessel,
  type AdminVesselPayload,
  type PaginationMeta,
} from '../../adminApi'
import { ApiError2 } from '../../api'
import AdminModal from '../../components/admin/AdminModal.vue'
import { useAdminAuth } from '../../composables/useAdminAuth'

const { isAdmin, canVerify } = useAdminAuth()

const vessels = ref<AdminVessel[]>([])
const pagination = ref<PaginationMeta | null>(null)
const operators = ref<AdminOperator[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const actionError = ref<string | null>(null)

const filters = reactive({ q: '', status: '', page: 1 })

const STATUS_LABELS: Record<string, string> = {
  DRAFT: 'Draft',
  REVIEW: 'Review',
  VERIFIED: 'Verified',
  REJECTED: 'Rejected',
}
const STATUS_COLORS: Record<string, string> = {
  DRAFT: 'text-[var(--color-text-secondary)]',
  REVIEW: 'text-amber-400',
  VERIFIED: 'text-emerald-400',
  REJECTED: 'text-red-400',
}

async function load() {
  loading.value = true
  error.value = null
  try {
    const res = await listAdminVessels({
      q: filters.q || undefined,
      status: filters.status || undefined,
      page: filters.page,
    })
    vessels.value = res.vessels
    pagination.value = res.pagination
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat kapal.'
  } finally {
    loading.value = false
  }
}

function search() {
  filters.page = 1
  load()
}

function prevPage() {
  if (filters.page > 1) {
    filters.page -= 1
    load()
  }
}

function nextPage() {
  if (pagination.value && filters.page < pagination.value.last_page) {
    filters.page += 1
    load()
  }
}

// ── Create / edit modal ──────────────────────────────────────────

const formOpen = ref(false)
const formError = ref<string | null>(null)
const formSaving = ref(false)
const editingId = ref<string | null>(null)
const form = reactive({
  name: '',
  mmsi: '',
  imo: '',
  call_sign: '',
  vessel_category: 'RORO',
  operator_id: '',
  confidence_score: 70,
  active: true,
  public_visible: false,
  verification_status: 'DRAFT',
})

function openCreate() {
  editingId.value = null
  Object.assign(form, {
    name: '',
    mmsi: '',
    imo: '',
    call_sign: '',
    vessel_category: 'RORO',
    operator_id: '',
    confidence_score: 70,
    active: true,
    public_visible: false,
    verification_status: 'DRAFT',
  })
  formError.value = null
  formOpen.value = true
}

function openEdit(v: AdminVessel) {
  editingId.value = v.id
  Object.assign(form, {
    name: v.name,
    mmsi: v.mmsi,
    imo: v.imo ?? '',
    call_sign: v.call_sign ?? '',
    vessel_category: v.vessel_category,
    operator_id: v.operator?.id ?? '',
    confidence_score: v.confidence_score,
    active: v.active,
    public_visible: v.public_visible,
    verification_status: v.verification_status,
  })
  formError.value = null
  formOpen.value = true
}

async function saveForm() {
  formSaving.value = true
  formError.value = null
  const payload: AdminVesselPayload = {
    name: form.name,
    mmsi: form.mmsi,
    imo: form.imo || null,
    call_sign: form.call_sign || null,
    vessel_category: form.vessel_category,
    operator_id: form.operator_id || null,
    confidence_score: form.confidence_score,
    active: form.active,
    public_visible: form.public_visible,
  }
  try {
    if (editingId.value) {
      await updateAdminVessel(editingId.value, payload)
    } else {
      await createAdminVessel(payload)
    }
    formOpen.value = false
    await load()
  } catch (e) {
    formError.value = e instanceof ApiError2 ? e.message : 'Gagal menyimpan.'
  } finally {
    formSaving.value = false
  }
}

// ── Verify / reject / delete ─────────────────────────────────────

const rejectOpen = ref(false)
const rejectTarget = ref<AdminVessel | null>(null)
const rejectReason = ref('')
const rejectSaving = ref(false)

function openReject(v: AdminVessel) {
  rejectTarget.value = v
  rejectReason.value = ''
  rejectOpen.value = true
}

async function confirmReject() {
  if (!rejectTarget.value || !rejectReason.value.trim()) return
  rejectSaving.value = true
  try {
    await rejectAdminVessel(rejectTarget.value.id, rejectReason.value.trim())
    rejectOpen.value = false
    await load()
  } catch (e) {
    actionError.value = e instanceof Error ? e.message : 'Reject gagal.'
  } finally {
    rejectSaving.value = false
  }
}

async function verify(v: AdminVessel) {
  actionError.value = null
  try {
    await verifyAdminVessel(v.id)
    await load()
  } catch (e) {
    actionError.value = e instanceof Error ? e.message : 'Verifikasi gagal.'
  }
}

async function remove(v: AdminVessel) {
  if (!window.confirm(`Hapus kapal ${v.name} (MMSI ${v.mmsi})?`)) return
  actionError.value = null
  try {
    await deleteAdminVessel(v.id)
    await load()
  } catch (e) {
    actionError.value = e instanceof Error ? e.message : 'Hapus gagal.'
  }
}

onMounted(async () => {
  await load()
  try {
    const res = await listAdminOperators({ per_page: 100 })
    operators.value = res.operators
  } catch {
    // Operator dropdown degrades to empty — non-blocking
  }
})
</script>

<template>
  <div data-testid="admin-vessels-page">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <h1 class="text-2xl font-extrabold tracking-tight">Kapal</h1>
      <button
        v-if="isAdmin"
        class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-90"
        data-testid="vessel-create"
        @click="openCreate"
      >
        + Tambah Kapal
      </button>
    </div>

    <div class="mt-4 flex flex-wrap gap-2">
      <input
        v-model="filters.q"
        type="search"
        placeholder="Cari nama / MMSI / IMO…"
        data-testid="vessels-search"
        class="w-64 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm focus:border-[var(--color-primary)] focus:outline-none"
        @keydown.enter="search"
      />
      <select
        v-model="filters.status"
        data-testid="vessels-status-filter"
        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
        @change="search"
      >
        <option value="">Semua status</option>
        <option value="DRAFT">Draft</option>
        <option value="REVIEW">Review</option>
        <option value="VERIFIED">Verified</option>
        <option value="REJECTED">Rejected</option>
      </select>
      <button
        class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm"
        @click="search"
      >
        Cari
      </button>
    </div>

    <div
      v-if="actionError"
      role="alert"
      class="mt-3 rounded-lg border border-[var(--color-danger)] px-3 py-2 text-sm text-[var(--color-danger)]"
    >
      {{ actionError }}
    </div>

    <div
      v-if="loading"
      class="py-12 text-sm text-[var(--color-text-secondary)]"
      data-testid="vessels-loading"
    >
      Memuat…
    </div>
    <div
      v-else-if="error"
      role="alert"
      class="mt-4 rounded-lg border border-[var(--color-danger)] p-4 text-sm text-[var(--color-danger)]"
    >
      {{ error }}
      <button class="ml-2 underline" data-testid="vessels-retry" @click="load">
        Coba lagi
      </button>
    </div>
    <div
      v-else-if="vessels.length === 0"
      class="py-12 text-sm text-[var(--color-text-secondary)]"
      data-testid="vessels-empty"
    >
      Tidak ada kapal.
    </div>

    <div v-else class="mt-4 overflow-x-auto">
      <table class="w-full text-sm" data-testid="vessels-table">
        <thead>
          <tr
            class="border-b border-[var(--color-border)] text-left text-xs uppercase tracking-wider text-[var(--color-text-secondary)]"
          >
            <th class="py-2 pr-4">Nama</th>
            <th class="py-2 pr-4">MMSI</th>
            <th class="py-2 pr-4">Kategori</th>
            <th class="py-2 pr-4">Operator</th>
            <th class="py-2 pr-4">Status</th>
            <th class="py-2 pr-4">Publik</th>
            <th class="py-2 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="v in vessels"
            :key="v.id"
            :data-testid="`vessel-row-${v.id}`"
            class="border-b border-[var(--color-border)]"
          >
            <td class="py-2 pr-4 font-semibold">
              <RouterLink
                :to="`/admin/kapal/${v.id}`"
                class="hover:text-[var(--color-primary)] hover:underline"
                >{{ v.name }}</RouterLink
              >
            </td>
            <td class="py-2 pr-4 font-mono text-xs tabular-nums">
              {{ v.mmsi }}
            </td>
            <td class="py-2 pr-4">{{ v.vessel_category }}</td>
            <td class="py-2 pr-4">{{ v.operator?.name ?? '-' }}</td>
            <td class="py-2 pr-4">
              <span :class="STATUS_COLORS[v.verification_status] ?? ''">
                {{
                  STATUS_LABELS[v.verification_status] ?? v.verification_status
                }}
              </span>
            </td>
            <td class="py-2 pr-4">{{ v.public_visible ? 'Ya' : 'Tidak' }}</td>
            <td class="py-2 text-right">
              <div class="flex justify-end gap-2 text-xs">
                <button
                  v-if="canVerify && v.verification_status !== 'VERIFIED'"
                  class="rounded border border-emerald-500/50 px-2 py-1 text-emerald-400 hover:bg-emerald-500/10"
                  :data-testid="`vessel-verify-${v.id}`"
                  @click="verify(v)"
                >
                  Verify
                </button>
                <button
                  v-if="canVerify && v.verification_status !== 'REJECTED'"
                  class="rounded border border-amber-500/50 px-2 py-1 text-amber-400 hover:bg-amber-500/10"
                  :data-testid="`vessel-reject-${v.id}`"
                  @click="openReject(v)"
                >
                  Reject
                </button>
                <button
                  v-if="isAdmin"
                  class="rounded border border-[var(--color-border)] px-2 py-1 hover:bg-[var(--color-surface-elevated)]"
                  :data-testid="`vessel-edit-${v.id}`"
                  @click="openEdit(v)"
                >
                  Edit
                </button>
                <button
                  v-if="isAdmin"
                  class="rounded border border-red-500/50 px-2 py-1 text-red-400 hover:bg-red-500/10"
                  :data-testid="`vessel-delete-${v.id}`"
                  @click="remove(v)"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <nav
        v-if="pagination && pagination.last_page > 1"
        class="mt-4 flex items-center justify-center gap-4 text-sm"
        aria-label="Paginasi"
      >
        <button
          :disabled="filters.page <= 1"
          class="rounded-lg border border-[var(--color-border)] px-3 py-1 disabled:opacity-50"
          data-testid="vessels-prev"
          @click="prevPage"
        >
          Sebelumnya
        </button>
        <span class="text-[var(--color-text-secondary)]">
          {{ filters.page }} / {{ pagination.last_page }} ({{
            pagination.total
          }})
        </span>
        <button
          :disabled="filters.page >= pagination.last_page"
          class="rounded-lg border border-[var(--color-border)] px-3 py-1 disabled:opacity-50"
          data-testid="vessels-next"
          @click="nextPage"
        >
          Berikutnya
        </button>
      </nav>
    </div>

    <AdminModal
      :open="formOpen"
      :title="editingId ? 'Edit Kapal' : 'Tambah Kapal'"
      @close="formOpen = false"
    >
      <form
        class="space-y-3"
        data-testid="vessel-form"
        @submit.prevent="saveForm"
      >
        <div>
          <label class="mb-1 block text-xs font-semibold" for="vf-name"
            >Nama *</label
          >
          <input
            id="vf-name"
            v-model="form.name"
            required
            maxlength="180"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="vf-mmsi"
              >MMSI (9 digit) *</label
            >
            <input
              id="vf-mmsi"
              v-model="form.mmsi"
              required
              pattern="[0-9]{9}"
              maxlength="9"
              :disabled="editingId !== null"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 font-mono text-sm disabled:opacity-50"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="vf-imo"
              >IMO</label
            >
            <input
              id="vf-imo"
              v-model="form.imo"
              maxlength="10"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 font-mono text-sm"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="vf-callsign"
              >Call Sign</label
            >
            <input
              id="vf-callsign"
              v-model="form.call_sign"
              maxlength="32"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 font-mono text-sm"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="vf-category"
              >Kategori *</label
            >
            <select
              id="vf-category"
              v-model="form.vessel_category"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            >
              <option value="RORO">RORO</option>
              <option value="ROPAX">ROPAX</option>
              <option value="FERRY_RORO">FERRY_RORO</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="vf-operator"
              >Operator</label
            >
            <select
              id="vf-operator"
              v-model="form.operator_id"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            >
              <option value="">—</option>
              <option v-for="op in operators" :key="op.id" :value="op.id">
                {{ op.name }}
              </option>
            </select>
          </div>
          <div>
            <span class="mb-1 block text-xs font-semibold">
              Status Verifikasi
            </span>
            <p class="px-3 py-2 text-sm">
              {{
                STATUS_LABELS[form.verification_status] ??
                form.verification_status
              }}
            </p>
            <p class="text-xs text-[var(--color-text-secondary)]">
              Gunakan tombol Verify/Reject untuk mengubah status.
            </p>
          </div>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="vf-confidence"
            >Confidence (0–100)</label
          >
          <input
            id="vf-confidence"
            v-model.number="form.confidence_score"
            type="number"
            min="0"
            max="100"
            step="1"
            class="w-32 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          />
        </div>
        <div class="flex gap-6 text-sm">
          <label class="flex items-center gap-2">
            <input v-model="form.active" type="checkbox" /> Aktif
          </label>
          <label class="flex items-center gap-2">
            <input
              v-model="form.public_visible"
              type="checkbox"
              :disabled="form.verification_status !== 'VERIFIED'"
            />
            Tampil publik
          </label>
        </div>

        <div
          v-if="formError"
          role="alert"
          class="rounded-lg border border-[var(--color-danger)] px-3 py-2 text-sm text-[var(--color-danger)]"
          data-testid="vessel-form-error"
        >
          {{ formError }}
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button
            type="button"
            class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm"
            @click="formOpen = false"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="formSaving"
            class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
            data-testid="vessel-form-submit"
          >
            {{ formSaving ? 'Menyimpan…' : 'Simpan' }}
          </button>
        </div>
      </form>
    </AdminModal>

    <AdminModal
      :open="rejectOpen"
      :title="`Tolak ${rejectTarget?.name ?? ''}`"
      @close="rejectOpen = false"
    >
      <form
        class="space-y-3"
        data-testid="vessel-reject-form"
        @submit.prevent="confirmReject"
      >
        <p class="text-sm text-[var(--color-text-secondary)]">
          Status akan menjadi REJECTED dan tidak tampil publik.
        </p>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="reject-reason"
            >Alasan *</label
          >
          <textarea
            id="reject-reason"
            v-model="rejectReason"
            required
            maxlength="500"
            rows="3"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            data-testid="vessel-reject-reason"
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
            :disabled="rejectSaving || !rejectReason.trim()"
            class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
            data-testid="vessel-reject-submit"
          >
            {{ rejectSaving ? 'Memproses…' : 'Tolak' }}
          </button>
        </div>
      </form>
    </AdminModal>
  </div>
</template>

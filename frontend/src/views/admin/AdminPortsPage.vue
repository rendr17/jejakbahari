<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'

import {
  createAdminPort,
  deleteAdminPort,
  listAdminPorts,
  updateAdminPort,
  type AdminPort,
} from '../../adminApi'
import { ApiError2 } from '../../api'
import AdminModal from '../../components/admin/AdminModal.vue'
import { useAdminAuth } from '../../composables/useAdminAuth'

const { isAdmin } = useAdminAuth()

const items = ref<AdminPort[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

async function load() {
  loading.value = true
  error.value = null
  try {
    const res = await listAdminPorts({ per_page: 100 })
    items.value = res.ports
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat pelabuhan.'
  } finally {
    loading.value = false
  }
}

const formOpen = ref(false)
const formSaving = ref(false)
const formError = ref<string | null>(null)
const editingId = ref<string | null>(null)
const form = reactive({
  code: '',
  name: '',
  city_name: '',
  province_name: '',
  latitude: 0,
  longitude: 0,
  geofence_radius_m: 500,
  verification_status: 'DRAFT',
  active: true,
})

function openCreate() {
  editingId.value = null
  Object.assign(form, {
    code: '',
    name: '',
    city_name: '',
    province_name: '',
    latitude: 0,
    longitude: 0,
    geofence_radius_m: 500,
    verification_status: 'DRAFT',
    active: true,
  })
  formError.value = null
  formOpen.value = true
}

function openEdit(p: AdminPort) {
  editingId.value = p.id
  Object.assign(form, {
    code: p.code,
    name: p.name,
    city_name: p.city_name ?? '',
    province_name: p.province_name ?? '',
    latitude: p.latitude ?? 0,
    longitude: p.longitude ?? 0,
    geofence_radius_m: p.geofence_radius_m ?? 500,
    verification_status: p.verification_status,
    active: p.active,
  })
  formError.value = null
  formOpen.value = true
}

async function save() {
  formSaving.value = true
  formError.value = null
  try {
    const payload = {
      code: form.code,
      name: form.name,
      city_name: form.city_name || null,
      province_name: form.province_name || null,
      latitude: form.latitude,
      longitude: form.longitude,
      geofence_radius_m: form.geofence_radius_m,
      verification_status: form.verification_status,
      active: form.active,
    }
    if (editingId.value) {
      await updateAdminPort(editingId.value, payload)
    } else {
      await createAdminPort(payload)
    }
    formOpen.value = false
    await load()
  } catch (e) {
    formError.value = e instanceof ApiError2 ? e.message : 'Gagal menyimpan.'
  } finally {
    formSaving.value = false
  }
}

async function remove(p: AdminPort) {
  if (!window.confirm(`Hapus pelabuhan ${p.name} (${p.code})?`)) return
  try {
    await deleteAdminPort(p.id)
    await load()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Hapus gagal.'
  }
}

onMounted(load)
</script>

<template>
  <div data-testid="admin-ports-page">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-extrabold tracking-tight">Pelabuhan</h1>
      <button
        v-if="isAdmin"
        class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-white"
        data-testid="port-create"
        @click="openCreate"
      >
        + Tambah Pelabuhan
      </button>
    </div>

    <div
      v-if="loading"
      class="py-12 text-sm text-[var(--color-text-secondary)]"
    >
      Memuat…
    </div>
    <div
      v-else-if="error"
      role="alert"
      class="mt-4 rounded-lg border border-[var(--color-danger)] p-4 text-sm text-[var(--color-danger)]"
    >
      {{ error }}
      <button class="ml-2 underline" @click="load">Coba lagi</button>
    </div>
    <div
      v-else-if="items.length === 0"
      class="py-12 text-sm text-[var(--color-text-secondary)]"
    >
      Belum ada pelabuhan.
    </div>

    <table v-else class="mt-4 w-full text-sm" data-testid="ports-table">
      <thead>
        <tr
          class="border-b border-[var(--color-border)] text-left text-xs uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          <th class="py-2 pr-4">Kode</th>
          <th class="py-2 pr-4">Nama</th>
          <th class="py-2 pr-4">Kota</th>
          <th class="py-2 pr-4">Geofence</th>
          <th class="py-2 pr-4">Status</th>
          <th class="py-2 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="p in items"
          :key="p.id"
          :data-testid="`port-row-${p.id}`"
          class="border-b border-[var(--color-border)]"
        >
          <td class="py-2 pr-4 font-mono text-xs font-semibold">
            {{ p.code }}
          </td>
          <td class="py-2 pr-4">{{ p.name }}</td>
          <td class="py-2 pr-4">{{ p.city_name ?? '-' }}</td>
          <td class="py-2 pr-4 text-xs">
            {{ p.geofence_type
            }}<span v-if="p.geofence_radius_m">
              · {{ p.geofence_radius_m }}m</span
            >
          </td>
          <td class="py-2 pr-4">{{ p.verification_status }}</td>
          <td class="py-2 text-right">
            <div class="flex justify-end gap-2 text-xs">
              <button
                v-if="isAdmin"
                class="rounded border border-[var(--color-border)] px-2 py-1"
                :data-testid="`port-edit-${p.id}`"
                @click="openEdit(p)"
              >
                Edit
              </button>
              <button
                v-if="isAdmin"
                class="rounded border border-red-500/50 px-2 py-1 text-red-400"
                :data-testid="`port-delete-${p.id}`"
                @click="remove(p)"
              >
                Hapus
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <AdminModal
      :open="formOpen"
      :title="editingId ? 'Edit Pelabuhan' : 'Tambah Pelabuhan'"
      @close="formOpen = false"
    >
      <form class="space-y-3" @submit.prevent="save">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="pt-code"
              >Kode *</label
            >
            <input
              id="pt-code"
              v-model="form.code"
              required
              maxlength="30"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 font-mono text-sm"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="pt-name"
              >Nama *</label
            >
            <input
              id="pt-name"
              v-model="form.name"
              required
              maxlength="180"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="pt-city"
              >Kota</label
            >
            <input
              id="pt-city"
              v-model="form.city_name"
              maxlength="120"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="pt-prov"
              >Provinsi</label
            >
            <input
              id="pt-prov"
              v-model="form.province_name"
              maxlength="120"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            />
          </div>
        </div>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="pt-lat"
              >Latitude *</label
            >
            <input
              id="pt-lat"
              v-model.number="form.latitude"
              type="number"
              step="0.000001"
              min="-90"
              max="90"
              required
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 font-mono text-sm"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="pt-lon"
              >Longitude *</label
            >
            <input
              id="pt-lon"
              v-model.number="form.longitude"
              type="number"
              step="0.000001"
              min="-180"
              max="180"
              required
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 font-mono text-sm"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="pt-radius"
              >Radius (m)</label
            >
            <input
              id="pt-radius"
              v-model.number="form.geofence_radius_m"
              type="number"
              min="1"
              max="50000"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 font-mono text-sm"
            />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="pt-status"
              >Status</label
            >
            <select
              id="pt-status"
              v-model="form.verification_status"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            >
              <option value="DRAFT">Draft</option>
              <option value="VERIFIED">Verified</option>
            </select>
          </div>
          <label class="flex items-end gap-2 pb-2 text-sm">
            <input v-model="form.active" type="checkbox" /> Aktif
          </label>
        </div>
        <div
          v-if="formError"
          role="alert"
          class="rounded-lg border border-[var(--color-danger)] px-3 py-2 text-sm text-[var(--color-danger)]"
        >
          {{ formError }}
        </div>
        <div class="flex justify-end gap-2">
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
          >
            {{ formSaving ? 'Menyimpan…' : 'Simpan' }}
          </button>
        </div>
      </form>
    </AdminModal>
  </div>
</template>

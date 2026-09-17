<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'

import {
  createAdminRoute,
  deleteAdminRoute,
  listAdminPorts,
  listAdminRoutes,
  updateAdminRoute,
  type AdminPort,
  type AdminRoute,
} from '../../adminApi'
import { ApiError2 } from '../../api'
import AdminModal from '../../components/admin/AdminModal.vue'
import { useAdminAuth } from '../../composables/useAdminAuth'

const { isAdmin } = useAdminAuth()

const items = ref<AdminRoute[]>([])
const ports = ref<AdminPort[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

async function load() {
  loading.value = true
  error.value = null
  try {
    const [r, p] = await Promise.all([
      listAdminRoutes({ per_page: 100 }),
      listAdminPorts({ per_page: 100 }),
    ])
    items.value = r.routes
    ports.value = p.ports
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat lintasan.'
  } finally {
    loading.value = false
  }
}

const formOpen = ref(false)
const formSaving = ref(false)
const formError = ref<string | null>(null)
const editingId = ref<string | null>(null)
const form = reactive({
  name: '',
  route_type: 'RORO',
  origin_port_id: '',
  destination_port_id: '',
  bidirectional: true,
  active: true,
})

function openCreate() {
  editingId.value = null
  Object.assign(form, {
    name: '',
    route_type: 'RORO',
    origin_port_id: '',
    destination_port_id: '',
    bidirectional: true,
    active: true,
  })
  formError.value = null
  formOpen.value = true
}

function openEdit(r: AdminRoute) {
  editingId.value = r.id
  Object.assign(form, {
    name: r.name,
    route_type: r.route_type,
    origin_port_id: r.origin_port?.id ?? '',
    destination_port_id: r.destination_port?.id ?? '',
    bidirectional: r.bidirectional,
    active: r.active,
  })
  formError.value = null
  formOpen.value = true
}

async function save() {
  formSaving.value = true
  formError.value = null
  try {
    const payload = {
      name: form.name,
      route_type: form.route_type,
      origin_port_id: form.origin_port_id,
      destination_port_id: form.destination_port_id,
      bidirectional: form.bidirectional,
      active: form.active,
    }
    if (editingId.value) {
      await updateAdminRoute(editingId.value, payload)
    } else {
      await createAdminRoute(payload)
    }
    formOpen.value = false
    await load()
  } catch (e) {
    formError.value = e instanceof ApiError2 ? e.message : 'Gagal menyimpan.'
  } finally {
    formSaving.value = false
  }
}

async function remove(r: AdminRoute) {
  if (!window.confirm(`Hapus lintasan ${r.name}?`)) return
  try {
    await deleteAdminRoute(r.id)
    await load()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Hapus gagal.'
  }
}

onMounted(load)
</script>

<template>
  <div data-testid="admin-routes-page">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-extrabold tracking-tight">Lintasan</h1>
      <button
        v-if="isAdmin"
        class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-white"
        data-testid="route-create"
        @click="openCreate"
      >
        + Tambah Lintasan
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
      Belum ada lintasan.
    </div>

    <table v-else class="mt-4 w-full text-sm" data-testid="routes-table">
      <thead>
        <tr
          class="border-b border-[var(--color-border)] text-left text-xs uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          <th class="py-2 pr-4">Nama</th>
          <th class="py-2 pr-4">Tipe</th>
          <th class="py-2 pr-4">Origin</th>
          <th class="py-2 pr-4">Destinasi</th>
          <th class="py-2 pr-4">2 Arah</th>
          <th class="py-2 pr-4">Aktif</th>
          <th class="py-2 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="r in items"
          :key="r.id"
          :data-testid="`route-row-${r.id}`"
          class="border-b border-[var(--color-border)]"
        >
          <td class="py-2 pr-4 font-semibold">{{ r.name }}</td>
          <td class="py-2 pr-4">{{ r.route_type }}</td>
          <td class="py-2 pr-4">{{ r.origin_port?.name ?? '-' }}</td>
          <td class="py-2 pr-4">{{ r.destination_port?.name ?? '-' }}</td>
          <td class="py-2 pr-4">{{ r.bidirectional ? 'Ya' : 'Tidak' }}</td>
          <td class="py-2 pr-4">{{ r.active ? 'Ya' : 'Tidak' }}</td>
          <td class="py-2 text-right">
            <div class="flex justify-end gap-2 text-xs">
              <button
                v-if="isAdmin"
                class="rounded border border-[var(--color-border)] px-2 py-1"
                :data-testid="`route-edit-${r.id}`"
                @click="openEdit(r)"
              >
                Edit
              </button>
              <button
                v-if="isAdmin"
                class="rounded border border-red-500/50 px-2 py-1 text-red-400"
                :data-testid="`route-delete-${r.id}`"
                @click="remove(r)"
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
      :title="editingId ? 'Edit Lintasan' : 'Tambah Lintasan'"
      @close="formOpen = false"
    >
      <form class="space-y-3" @submit.prevent="save">
        <div>
          <label class="mb-1 block text-xs font-semibold" for="rt-name"
            >Nama *</label
          >
          <input
            id="rt-name"
            v-model="form.name"
            required
            maxlength="220"
            placeholder="Merak — Bakauheni"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="rt-origin"
              >Pelabuhan Asal *</label
            >
            <select
              id="rt-origin"
              v-model="form.origin_port_id"
              required
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            >
              <option value="" disabled>Pilih…</option>
              <option v-for="p in ports" :key="p.id" :value="p.id">
                {{ p.name }} ({{ p.code }})
              </option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="rt-dest"
              >Pelabuhan Tujuan *</label
            >
            <select
              id="rt-dest"
              v-model="form.destination_port_id"
              required
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            >
              <option value="" disabled>Pilih…</option>
              <option v-for="p in ports" :key="p.id" :value="p.id">
                {{ p.name }} ({{ p.code }})
              </option>
            </select>
          </div>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="rt-type"
            >Tipe *</label
          >
          <select
            id="rt-type"
            v-model="form.route_type"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          >
            <option value="RORO">RORO</option>
            <option value="ROPAX">ROPAX</option>
          </select>
        </div>
        <div class="flex gap-6 text-sm">
          <label class="flex items-center gap-2">
            <input v-model="form.bidirectional" type="checkbox" /> Dua arah
          </label>
          <label class="flex items-center gap-2">
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

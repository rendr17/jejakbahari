<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'

import {
  createAdminDataSource,
  deleteAdminDataSource,
  listAdminDataSources,
  updateAdminDataSource,
  type AdminDataSource,
} from '../../adminApi'
import { ApiError2 } from '../../api'
import AdminModal from '../../components/admin/AdminModal.vue'
import { useAdminAuth } from '../../composables/useAdminAuth'

const { isAdmin } = useAdminAuth()

const items = ref<AdminDataSource[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

async function load() {
  loading.value = true
  error.value = null
  try {
    const res = await listAdminDataSources({ per_page: 100 })
    items.value = res.dataSources
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat sumber data.'
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
  source_type: '',
  access_method: '',
  url: '',
  license_name: '',
  terms_url: '',
  attribution_text: '',
  active: true,
})

function openCreate() {
  editingId.value = null
  Object.assign(form, {
    name: '',
    source_type: '',
    access_method: '',
    url: '',
    license_name: '',
    terms_url: '',
    attribution_text: '',
    active: true,
  })
  formError.value = null
  formOpen.value = true
}

function openEdit(d: AdminDataSource) {
  editingId.value = d.id
  Object.assign(form, {
    name: d.name,
    source_type: d.source_type,
    access_method: d.access_method,
    url: d.url ?? '',
    license_name: d.license_name ?? '',
    terms_url: d.terms_url ?? '',
    attribution_text: d.attribution_text ?? '',
    active: d.active,
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
      source_type: form.source_type,
      access_method: form.access_method,
      url: form.url || null,
      license_name: form.license_name || null,
      terms_url: form.terms_url || null,
      attribution_text: form.attribution_text || null,
      active: form.active,
    }
    if (editingId.value) {
      await updateAdminDataSource(editingId.value, payload)
    } else {
      await createAdminDataSource(payload)
    }
    formOpen.value = false
    await load()
  } catch (e) {
    formError.value = e instanceof ApiError2 ? e.message : 'Gagal menyimpan.'
  } finally {
    formSaving.value = false
  }
}

async function remove(d: AdminDataSource) {
  if (!window.confirm(`Hapus sumber data ${d.name}?`)) return
  try {
    await deleteAdminDataSource(d.id)
    await load()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Hapus gagal.'
  }
}

onMounted(load)
</script>

<template>
  <div data-testid="admin-data-sources-page">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-extrabold tracking-tight">Sumber Data</h1>
      <button
        v-if="isAdmin"
        class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-white"
        data-testid="datasource-create"
        @click="openCreate"
      >
        + Tambah Sumber
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
      Belum ada sumber data.
    </div>

    <table v-else class="mt-4 w-full text-sm" data-testid="datasources-table">
      <thead>
        <tr
          class="border-b border-[var(--color-border)] text-left text-xs uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          <th class="py-2 pr-4">Nama</th>
          <th class="py-2 pr-4">Tipe</th>
          <th class="py-2 pr-4">Akses</th>
          <th class="py-2 pr-4">Lisensi</th>
          <th class="py-2 pr-4">Aktif</th>
          <th class="py-2 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="d in items"
          :key="d.id"
          :data-testid="`datasource-row-${d.id}`"
          class="border-b border-[var(--color-border)]"
        >
          <td class="py-2 pr-4 font-semibold">{{ d.name }}</td>
          <td class="py-2 pr-4">{{ d.source_type }}</td>
          <td class="py-2 pr-4">{{ d.access_method }}</td>
          <td class="py-2 pr-4 text-xs">{{ d.license_name ?? '-' }}</td>
          <td class="py-2 pr-4">{{ d.active ? 'Ya' : 'Tidak' }}</td>
          <td class="py-2 text-right">
            <div class="flex justify-end gap-2 text-xs">
              <button
                v-if="isAdmin"
                class="rounded border border-[var(--color-border)] px-2 py-1"
                :data-testid="`datasource-edit-${d.id}`"
                @click="openEdit(d)"
              >
                Edit
              </button>
              <button
                v-if="isAdmin"
                class="rounded border border-red-500/50 px-2 py-1 text-red-400"
                :data-testid="`datasource-delete-${d.id}`"
                @click="remove(d)"
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
      :title="editingId ? 'Edit Sumber Data' : 'Tambah Sumber Data'"
      @close="formOpen = false"
    >
      <form class="space-y-3" @submit.prevent="save">
        <div>
          <label class="mb-1 block text-xs font-semibold" for="ds-name"
            >Nama *</label
          >
          <input
            id="ds-name"
            v-model="form.name"
            required
            maxlength="180"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="ds-type"
              >Tipe *</label
            >
            <input
              id="ds-type"
              v-model="form.source_type"
              required
              maxlength="40"
              placeholder="REGISTRY / SCHEDULE / NEWS"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="ds-access"
              >Metode Akses *</label
            >
            <input
              id="ds-access"
              v-model="form.access_method"
              required
              maxlength="40"
              placeholder="MANUAL / SCRAPE / API"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            />
          </div>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="ds-url"
            >URL</label
          >
          <input
            id="ds-url"
            v-model="form.url"
            type="url"
            maxlength="500"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="mb-1 block text-xs font-semibold" for="ds-license"
              >Lisensi</label
            >
            <input
              id="ds-license"
              v-model="form.license_name"
              maxlength="120"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label class="mb-1 block text-xs font-semibold" for="ds-terms"
              >Terms URL</label
            >
            <input
              id="ds-terms"
              v-model="form.terms_url"
              type="url"
              maxlength="500"
              class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
            />
          </div>
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="ds-attr"
            >Attribution</label
          >
          <textarea
            id="ds-attr"
            v-model="form.attribution_text"
            rows="2"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          ></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm">
          <input v-model="form.active" type="checkbox" /> Aktif
        </label>
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

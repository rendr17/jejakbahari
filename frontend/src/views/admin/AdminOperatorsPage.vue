<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'

import {
  createAdminOperator,
  deleteAdminOperator,
  listAdminOperators,
  updateAdminOperator,
  type AdminOperator,
} from '../../adminApi'
import { ApiError2 } from '../../api'
import AdminModal from '../../components/admin/AdminModal.vue'
import { useAdminAuth } from '../../composables/useAdminAuth'

const { isAdmin } = useAdminAuth()

const items = ref<AdminOperator[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

async function load() {
  loading.value = true
  error.value = null
  try {
    const res = await listAdminOperators({ per_page: 100 })
    items.value = res.operators
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat operator.'
  } finally {
    loading.value = false
  }
}

const formOpen = ref(false)
const formSaving = ref(false)
const formError = ref<string | null>(null)
const editingId = ref<string | null>(null)
const form = reactive({ name: '', website_url: '', active: true })

function openCreate() {
  editingId.value = null
  Object.assign(form, { name: '', website_url: '', active: true })
  formError.value = null
  formOpen.value = true
}

function openEdit(o: AdminOperator) {
  editingId.value = o.id
  Object.assign(form, {
    name: o.name,
    website_url: o.website_url ?? '',
    active: o.active,
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
      website_url: form.website_url || null,
      active: form.active,
    }
    if (editingId.value) {
      await updateAdminOperator(editingId.value, payload)
    } else {
      await createAdminOperator(payload)
    }
    formOpen.value = false
    await load()
  } catch (e) {
    formError.value = e instanceof ApiError2 ? e.message : 'Gagal menyimpan.'
  } finally {
    formSaving.value = false
  }
}

async function remove(o: AdminOperator) {
  if (!window.confirm(`Hapus operator ${o.name}?`)) return
  try {
    await deleteAdminOperator(o.id)
    await load()
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Hapus gagal.'
  }
}

onMounted(load)
</script>

<template>
  <div data-testid="admin-operators-page">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-extrabold tracking-tight">Operator</h1>
      <button
        v-if="isAdmin"
        class="rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-semibold text-white"
        data-testid="operator-create"
        @click="openCreate"
      >
        + Tambah Operator
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
      Belum ada operator.
    </div>

    <table v-else class="mt-4 w-full text-sm" data-testid="operators-table">
      <thead>
        <tr
          class="border-b border-[var(--color-border)] text-left text-xs uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          <th class="py-2 pr-4">Nama</th>
          <th class="py-2 pr-4">Website</th>
          <th class="py-2 pr-4">Aktif</th>
          <th class="py-2 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="o in items"
          :key="o.id"
          :data-testid="`operator-row-${o.id}`"
          class="border-b border-[var(--color-border)]"
        >
          <td class="py-2 pr-4 font-semibold">{{ o.name }}</td>
          <td class="py-2 pr-4">
            <a
              v-if="o.website_url"
              :href="o.website_url"
              target="_blank"
              rel="noopener noreferrer"
              class="text-xs text-[var(--color-primary)] underline"
              >{{ o.website_url }}</a
            >
            <span v-else>-</span>
          </td>
          <td class="py-2 pr-4">{{ o.active ? 'Ya' : 'Tidak' }}</td>
          <td class="py-2 text-right">
            <div class="flex justify-end gap-2 text-xs">
              <button
                v-if="isAdmin"
                class="rounded border border-[var(--color-border)] px-2 py-1"
                :data-testid="`operator-edit-${o.id}`"
                @click="openEdit(o)"
              >
                Edit
              </button>
              <button
                v-if="isAdmin"
                class="rounded border border-red-500/50 px-2 py-1 text-red-400"
                :data-testid="`operator-delete-${o.id}`"
                @click="remove(o)"
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
      :title="editingId ? 'Edit Operator' : 'Tambah Operator'"
      @close="formOpen = false"
    >
      <form class="space-y-3" @submit.prevent="save">
        <div>
          <label class="mb-1 block text-xs font-semibold" for="op-name"
            >Nama *</label
          >
          <input
            id="op-name"
            v-model="form.name"
            required
            maxlength="160"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          />
        </div>
        <div>
          <label class="mb-1 block text-xs font-semibold" for="op-web"
            >Website</label
          >
          <input
            id="op-web"
            v-model="form.website_url"
            type="url"
            maxlength="500"
            placeholder="https://…"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm"
          />
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

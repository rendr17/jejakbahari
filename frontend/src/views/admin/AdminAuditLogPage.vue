<script setup lang="ts">
import { onMounted, ref } from 'vue'

import {
  listAdminAuditLogs,
  type AdminAuditLog,
  type PaginationMeta,
} from '../../adminApi'

const logs = ref<AdminAuditLog[]>([])
const pagination = ref<PaginationMeta | null>(null)
const page = ref(1)
const loading = ref(true)
const error = ref<string | null>(null)

async function load() {
  loading.value = true
  error.value = null
  try {
    const res = await listAdminAuditLogs({ page: page.value })
    logs.value = res.logs
    pagination.value = res.pagination
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Gagal memuat audit log.'
  } finally {
    loading.value = false
  }
}

function formatTime(iso: string): string {
  return new Date(iso).toLocaleString('id-ID', { hour12: false })
}

function prevPage() {
  if (page.value > 1) {
    page.value -= 1
    load()
  }
}

function nextPage() {
  if (pagination.value && page.value < pagination.value.last_page) {
    page.value += 1
    load()
  }
}

onMounted(load)
</script>

<template>
  <div data-testid="admin-audit-page">
    <h1 class="text-2xl font-extrabold tracking-tight">Audit Log</h1>
    <p class="mt-1 text-sm text-[var(--color-text-secondary)]">
      Jejak perubahan registry oleh admin dan reviewer.
    </p>

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
      v-else-if="logs.length === 0"
      class="py-12 text-sm text-[var(--color-text-secondary)]"
      data-testid="audit-empty"
    >
      Belum ada aktivitas.
    </div>

    <table v-else class="mt-4 w-full text-sm" data-testid="audit-table">
      <thead>
        <tr
          class="border-b border-[var(--color-border)] text-left text-xs uppercase tracking-wider text-[var(--color-text-secondary)]"
        >
          <th class="py-2 pr-4">Waktu</th>
          <th class="py-2 pr-4">User</th>
          <th class="py-2 pr-4">Aksi</th>
          <th class="py-2 pr-4">Entitas</th>
          <th class="py-2 pr-4">IP</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="log in logs"
          :key="log.id"
          :data-testid="`audit-row-${log.id}`"
          class="border-b border-[var(--color-border)]"
        >
          <td class="py-2 pr-4 font-mono text-xs tabular-nums">
            {{ formatTime(log.created_at) }}
          </td>
          <td class="py-2 pr-4">{{ log.user?.name ?? '-' }}</td>
          <td class="py-2 pr-4 font-mono text-xs">{{ log.action }}</td>
          <td class="py-2 pr-4 text-xs">
            {{ log.entity_type }}
            <span class="text-[var(--color-text-secondary)]"
              >({{ log.entity_id.slice(0, 8) }}…)</span
            >
          </td>
          <td class="py-2 pr-4 font-mono text-xs">
            {{ log.ip_address ?? '-' }}
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
        :disabled="page <= 1"
        class="rounded-lg border border-[var(--color-border)] px-3 py-1 disabled:opacity-50"
        @click="prevPage"
      >
        Sebelumnya
      </button>
      <span class="text-[var(--color-text-secondary)]">
        {{ page }} / {{ pagination.last_page }}
      </span>
      <button
        :disabled="page >= pagination.last_page"
        class="rounded-lg border border-[var(--color-border)] px-3 py-1 disabled:opacity-50"
        @click="nextPage"
      >
        Berikutnya
      </button>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { fetchPorts, type Port, type PaginationMeta } from '../api'

const router = useRouter()
const ports = ref<Port[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const page = ref(1)
const pagination = ref<PaginationMeta | null>(null)

async function loadPorts(): Promise<void> {
  try {
    loading.value = true
    error.value = null
    const result = await fetchPorts({ page: page.value, per_page: 50 })
    ports.value = result.ports
    pagination.value = result.pagination
  } catch (e) {
    error.value =
      e instanceof Error ? e.message : 'Gagal memuat daftar pelabuhan'
  } finally {
    loading.value = false
  }
}

function prevPage(): void {
  page.value = page.value - 1
  loadPorts()
}

function nextPage(): void {
  page.value = page.value + 1
  loadPorts()
}

function viewPortEvents(port: Port): void {
  router.push(`/pelabuhan/${port.id}/event`)
}

onMounted(loadPorts)
</script>

<template>
  <div data-testid="port-list-page" class="site-container py-8">
    <header class="mb-6">
      <h1 class="text-2xl font-extrabold tracking-tight md:text-3xl">
        Pelabuhan
      </h1>
      <p class="mt-2 text-sm text-[var(--color-text-secondary)]">
        Pelabuhan RoRo/RoPax Indonesia yang terdaftar.
      </p>
    </header>

    <div
      v-if="loading"
      data-testid="port-list-loading"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Memuat daftar pelabuhan…
    </div>

    <div
      v-else-if="error"
      data-testid="port-list-error"
      role="alert"
      aria-live="assertive"
      class="rounded-lg border border-[var(--color-danger)] bg-[var(--color-surface)] p-4 text-sm text-[var(--color-danger)]"
    >
      {{ error }}
      <button class="ml-2 underline" @click="loadPorts">Coba lagi</button>
    </div>

    <div
      v-else-if="ports.length === 0"
      data-testid="port-list-empty"
      role="status"
      aria-live="polite"
      class="py-12 text-center text-sm text-[var(--color-text-secondary)]"
    >
      Belum ada pelabuhan terdaftar.
    </div>

    <ul v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <li
        v-for="port in ports"
        :key="port.id"
        :data-testid="`port-card-${port.code}`"
        class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-4"
      >
        <h3 class="font-bold tracking-tight">{{ port.name }}</h3>
        <dl class="mt-2 space-y-1 text-xs text-[var(--color-text-secondary)]">
          <div class="flex justify-between">
            <dt>Kode</dt>
            <dd class="font-mono">{{ port.code }}</dd>
          </div>
          <div v-if="port.city_name" class="flex justify-between">
            <dt>Kota</dt>
            <dd>{{ port.city_name }}</dd>
          </div>
          <div v-if="port.province_name" class="flex justify-between">
            <dt>Provinsi</dt>
            <dd>{{ port.province_name }}</dd>
          </div>
          <div v-if="port.latitude != null" class="flex justify-between">
            <dt>Koordinat</dt>
            <dd class="font-mono tabular-nums">
              {{ port.latitude.toFixed(4) }}, {{ port.longitude?.toFixed(4) }}
            </dd>
          </div>
        </dl>
        <button
          class="mt-3 w-full rounded-lg border border-[var(--color-border)] px-3 py-1.5 text-xs text-[var(--color-text-secondary)] transition-colors hover:border-[var(--color-primary)] hover:text-[var(--color-primary)]"
          :aria-label="`Lihat event pelabuhan ${port.name}`"
          @click="viewPortEvents(port)"
        >
          Lihat Event
        </button>
      </li>
    </ul>

    <nav
      v-if="pagination && pagination.last_page > 1"
      aria-label="Paginasi"
      class="mt-6 flex items-center justify-center gap-4"
    >
      <button
        class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm disabled:opacity-50"
        :disabled="page <= 1"
        @click="prevPage"
      >
        Sebelumnya
      </button>
      <span class="text-sm text-[var(--color-text-secondary)]">
        Halaman {{ page }} dari {{ pagination.last_page }}
      </span>
      <button
        class="rounded-lg border border-[var(--color-border)] px-4 py-2 text-sm disabled:opacity-50"
        :disabled="page >= pagination.last_page"
        @click="nextPage"
      >
        Berikutnya
      </button>
    </nav>
  </div>
</template>

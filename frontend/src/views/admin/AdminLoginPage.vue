<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { ApiError2 } from '../../api'
import { useAdminAuth } from '../../composables/useAdminAuth'

const route = useRoute()
const router = useRouter()
const { login } = useAdminAuth()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref<string | null>(null)

async function submit() {
  error.value = null
  loading.value = true
  try {
    await login(email.value, password.value)
    const redirect =
      typeof route.query.redirect === 'string' ? route.query.redirect : '/admin'
    router.push(redirect)
  } catch (e) {
    if (e instanceof ApiError2 && e.status === 422) {
      error.value = 'Email atau password salah.'
    } else if (e instanceof ApiError2 && e.status === 429) {
      error.value = 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.'
    } else {
      error.value = e instanceof Error ? e.message : 'Login gagal.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div
    data-testid="admin-login-page"
    class="site-container flex min-h-[60vh] items-center justify-center py-12"
  >
    <div
      class="w-full max-w-sm rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-6"
    >
      <h1 class="text-xl font-extrabold tracking-tight">Admin JejakBahari</h1>
      <p class="mt-1 text-sm text-[var(--color-text-secondary)]">
        Masuk untuk mengelola registry kapal dan master data.
      </p>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div>
          <label
            for="admin-email"
            class="mb-1 block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]"
            >Email</label
          >
          <input
            id="admin-email"
            v-model="email"
            type="email"
            required
            autocomplete="username"
            data-testid="admin-login-email"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm focus:border-[var(--color-primary)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary)]"
          />
        </div>
        <div>
          <label
            for="admin-password"
            class="mb-1 block text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]"
            >Password</label
          >
          <input
            id="admin-password"
            v-model="password"
            type="password"
            required
            autocomplete="current-password"
            data-testid="admin-login-password"
            class="w-full rounded-lg border border-[var(--color-border)] bg-[var(--color-bg)] px-3 py-2 text-sm focus:border-[var(--color-primary)] focus:outline-none focus:ring-1 focus:ring-[var(--color-primary)]"
          />
        </div>

        <div
          v-if="error"
          data-testid="admin-login-error"
          role="alert"
          class="rounded-lg border border-[var(--color-danger)] px-3 py-2 text-sm text-[var(--color-danger)]"
        >
          {{ error }}
        </div>

        <button
          type="submit"
          :disabled="loading"
          data-testid="admin-login-submit"
          class="w-full rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
        >
          {{ loading ? 'Masuk…' : 'Masuk' }}
        </button>
      </form>
    </div>
  </div>
</template>

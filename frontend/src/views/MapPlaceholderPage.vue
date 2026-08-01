<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <section
    class="site-container flex min-h-[calc(100svh-4rem)] items-center py-16"
  >
    <div
      class="max-w-xl rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-6 sm:p-8"
    >
      <p
        class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-[var(--color-primary)]"
      >
        Peta
      </p>
      <h1 class="mt-4 text-3xl font-extrabold tracking-[-0.04em]">
        Peta kapal segera hadir.
      </h1>
      <p class="mt-4 leading-7 text-[var(--color-text-secondary)]">
        MapLibre dan data posisi belum dimuat pada Phase 1. Halaman ini
        memastikan jalur navigasi siap tanpa membuat integrasi palsu.
      </p>
      <RouterLink class="nav-link mt-6 inline-block" to="/"
        >Kembali ke beranda</RouterLink
      >
    </div>
  </section>
</template>

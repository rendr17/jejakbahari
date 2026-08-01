<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="min-h-svh">
    <header
      class="sticky top-0 z-20 border-b border-[var(--color-border)] bg-[var(--color-bg)]/95 backdrop-blur-sm"
    >
      <div class="site-container flex h-16 items-center justify-between gap-6">
        <RouterLink
          to="/"
          class="rounded-sm text-xl font-extrabold tracking-[-0.025em] text-[var(--color-primary)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--color-focus)]"
        >
          JejakBahari
        </RouterLink>

        <nav
          aria-label="Navigasi utama"
          class="hidden items-center gap-7 text-sm md:flex"
        >
          <RouterLink class="nav-link" to="/">Beranda</RouterLink>
          <RouterLink class="nav-link" to="/peta">Peta</RouterLink>
          <a class="nav-link" href="#tentang-data">Tentang Data</a>
        </nav>

        <details class="relative md:hidden">
          <summary
            class="flex min-h-11 cursor-pointer list-none items-center rounded-md border border-[var(--color-border)] px-4 text-sm font-semibold text-[var(--color-text-primary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-focus)]"
          >
            Menu
          </summary>
          <nav
            aria-label="Navigasi mobile"
            class="absolute right-0 z-10 mt-2 grid min-w-48 gap-1 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface-elevated)] p-2 shadow-2xl"
          >
            <RouterLink class="mobile-nav-link" to="/">Beranda</RouterLink>
            <RouterLink class="mobile-nav-link" to="/peta">Peta</RouterLink>
            <a class="mobile-nav-link" href="#tentang-data">Tentang Data</a>
          </nav>
        </details>
      </div>
    </header>

    <main>
      <RouterView />
    </main>

    <footer id="tentang-data" class="border-t border-[var(--color-border)]">
      <div
        class="site-container grid gap-6 py-10 text-sm text-[var(--color-text-secondary)] md:grid-cols-[1fr_auto] md:items-end"
      >
        <div class="max-w-2xl">
          <p class="font-semibold text-[var(--color-text-primary)]">
            Tentang data
          </p>
          <p class="mt-2 leading-6">
            JejakBahari menampilkan informasi AIS untuk eksplorasi dan
            monitoring non-kritis. Data dapat terlambat, hilang, atau tidak
            akurat.
          </p>
        </div>
        <p class="font-mono text-xs">© 2026 JejakBahari · Open source</p>
      </div>
    </footer>
  </div>
</template>

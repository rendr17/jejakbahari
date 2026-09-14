<script setup lang="ts">
import { useTemplateRef } from 'vue'
import { RouterLink, RouterView } from 'vue-router'

const mobileMenu = useTemplateRef<{ removeAttribute(name: string): void }>(
  'mobile-menu',
)
const closeMobileMenu = () => mobileMenu.value?.removeAttribute('open')
</script>

<template>
  <div class="app-shell min-h-svh">
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

    <svg
      class="ocean-traffic-background"
      viewBox="0 0 1600 900"
      preserveAspectRatio="xMidYMid slice"
      aria-hidden="true"
      focusable="false"
    >
      <path
        class="ocean-route ocean-route-cyan"
        d="M-100 180C260 80 420 340 760 210S1260 120 1700 300"
      />
      <path
        class="ocean-route ocean-route-coral"
        d="M-120 620C220 480 460 780 820 610S1260 400 1700 560"
      />
      <path
        class="ocean-route ocean-route-blue"
        d="M180-80C80 230 360 340 240 620S360 900 520 980"
      />
      <path
        class="ocean-route ocean-route-amber"
        d="M820-80C680 180 980 310 840 500S920 820 760 980"
      />
      <path
        class="ocean-route ocean-route-cyan"
        d="M1320-80C1180 170 1450 300 1260 520S1390 770 1220 980"
      />
      <path
        class="ocean-route ocean-route-coral"
        d="M-100 390C260 330 520 470 860 390S1320 260 1700 430"
      />
      <g class="ocean-ports">
        <circle cx="240" cy="210" r="4" />
        <circle cx="620" cy="260" r="4" />
        <circle cx="880" cy="590" r="4" />
        <circle cx="1260" cy="330" r="4" />
        <circle cx="1320" cy="690" r="4" />
      </g>
    </svg>

    <header
      class="sticky top-0 z-20 border-b border-[var(--color-border)] bg-[var(--color-bg)]/95 backdrop-blur-sm"
    >
      <div class="site-container flex h-16 items-center justify-between gap-6">
        <RouterLink
          to="/"
          class="inline-flex min-h-11 items-center rounded-sm text-xl font-extrabold tracking-[-0.025em] text-[var(--color-primary)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--color-focus)]"
        >
          JejakBahari
        </RouterLink>

        <nav
          aria-label="Navigasi utama"
          class="hidden items-center gap-7 text-sm md:flex"
        >
          <RouterLink class="nav-link" to="/">Beranda</RouterLink>
          <RouterLink class="nav-link" to="/peta">Peta</RouterLink>
          <RouterLink class="nav-link" to="/kapal">Kapal</RouterLink>
          <RouterLink class="nav-link" to="/#transparansi-data"
            >Tentang Data</RouterLink
          >
        </nav>

        <details ref="mobile-menu" class="relative md:hidden">
          <summary
            class="flex min-h-11 cursor-pointer list-none items-center rounded-md border border-[var(--color-border)] px-4 text-sm font-semibold text-[var(--color-text-primary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-focus)]"
          >
            Menu
          </summary>
          <nav
            aria-label="Navigasi mobile"
            class="absolute right-0 z-10 mt-2 grid min-w-48 gap-1 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface-elevated)] p-2 shadow-2xl"
          >
            <RouterLink class="mobile-nav-link" to="/" @click="closeMobileMenu"
              >Beranda</RouterLink
            >
            <RouterLink
              class="mobile-nav-link"
              to="/peta"
              @click="closeMobileMenu"
              >Peta</RouterLink
            >
            <RouterLink
              class="mobile-nav-link"
              to="/kapal"
              @click="closeMobileMenu"
              >Kapal</RouterLink
            >
            <RouterLink
              class="mobile-nav-link"
              to="/#transparansi-data"
              @click="closeMobileMenu"
              >Tentang Data</RouterLink
            >
          </nav>
        </details>
      </div>
    </header>

    <main id="main-content" tabindex="-1">
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

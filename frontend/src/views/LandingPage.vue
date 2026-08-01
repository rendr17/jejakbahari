<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <section
    class="site-container grid min-h-[calc(100svh-4rem)] items-center gap-12 py-14 lg:grid-cols-[1.05fr_0.95fr] lg:gap-8 lg:py-20"
  >
    <div class="max-w-3xl lg:py-12">
      <div
        class="inline-flex min-h-8 items-center gap-2 rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-3 font-mono text-xs text-[var(--color-text-secondary)]"
      >
        <span
          class="size-2 rounded-full bg-[var(--color-info)]"
          aria-hidden="true"
        ></span>
        Usia dan sumber data selalu terlihat
      </div>
      <h1
        class="mt-6 max-w-[13ch] text-5xl leading-[0.98] font-extrabold tracking-[-0.025em] text-balance sm:text-6xl lg:text-[4.5rem]"
      >
        Jejak kapal RoRo Indonesia, terbaca jelas.
      </h1>
      <p
        class="mt-7 max-w-2xl text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg"
      >
        Temukan posisi terakhir kapal, pahami usia datanya, dan lihat konteks
        pelabuhan serta lintasannya dalam satu tempat.
      </p>
      <div class="mt-9 flex flex-col gap-3 sm:flex-row">
        <RouterLink
          to="/peta"
          class="inline-flex min-h-11 items-center justify-center rounded-md bg-[var(--color-primary)] px-5 font-semibold text-[#0d0f14] transition-colors hover:bg-[var(--color-primary-hover)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--color-focus)]"
        >
          Lihat peta kapal <span aria-hidden="true">→</span>
        </RouterLink>
        <a
          href="#tentang-data"
          class="inline-flex min-h-11 items-center justify-center rounded-md border border-[var(--color-border)] bg-[var(--color-surface)] px-5 font-semibold text-[var(--color-text-primary)] transition-colors hover:border-[var(--color-text-secondary)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--color-focus)]"
        >
          Tentang data
        </a>
      </div>
      <p
        class="mt-6 max-w-xl border-l border-[var(--color-primary)] pl-4 text-sm leading-6 text-[var(--color-text-secondary)]"
      >
        Bukan alat navigasi atau keselamatan. Data AIS dapat terlambat, hilang,
        atau tidak akurat.
      </p>
    </div>

    <figure
      class="route-stage"
      role="img"
      aria-labelledby="hero-visual-caption"
    >
      <div aria-hidden="true">
        <span class="route-orbit"></span>
        <span class="route-port route-port-merak"></span>
        <span class="route-port route-port-bakauheni"></span>
        <span class="route-label route-label-merak">Merak</span>
        <span class="route-label route-label-bakauheni">Bakauheni</span>
        <span class="vessel-marker"></span>

        <div class="position-card">
          <div class="flex items-center justify-between gap-3">
            <p class="text-sm font-bold text-[var(--color-text-primary)]">
              KMP Nusa Bahari
            </p>
            <span class="freshness-example">Contoh · Live</span>
          </div>
          <dl class="mt-4 grid grid-cols-2 gap-4 font-mono text-xs">
            <div>
              <dt class="text-[var(--color-text-secondary)]">Diperbarui</dt>
              <dd class="mt-1 text-[var(--color-text-primary)]">
                2 menit lalu
              </dd>
            </div>
            <div>
              <dt class="text-[var(--color-text-secondary)]">Sumber</dt>
              <dd class="mt-1 text-[var(--color-text-primary)]">
                AIS · ilustrasi
              </dd>
            </div>
          </dl>
        </div>
      </div>
      <figcaption
        id="hero-visual-caption"
        class="absolute top-5 left-5 font-mono text-[0.6875rem] tracking-[0.12em] text-[var(--color-text-secondary)] uppercase"
      >
        Ilustrasi lintasan · bukan posisi aktual
      </figcaption>
    </figure>
  </section>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router'

const features = [
  {
    id: 'posisi-kapal',
    label: 'Posisi kapal',
    eyebrow: 'Satu tampilan',
    title: 'Posisi Kapal dalam Satu Peta',
    description:
      'Lihat kapal RoRo yang sudah masuk registry dalam satu tampilan, lalu pilih kapal untuk membaca posisi terakhirnya.',
    detail: 'Peta publik hanya menampilkan kapal yang lolos aturan publikasi.',
  },
  {
    id: 'usia-data',
    label: 'Usia data',
    eyebrow: 'Konteks waktu',
    title: 'Tahu Seberapa Baru Datanya',
    description:
      'Setiap posisi disertai waktu pembaruan dan status kesegaran agar data lama tidak disalahartikan sebagai posisi terkini.',
    detail:
      'Timestamp tetap menjadi acuan saat koneksi atau provider terganggu.',
  },
  {
    id: 'kapal-terverifikasi',
    label: 'Verifikasi',
    eyebrow: 'Registry terkurasi',
    title: 'Hanya Kapal RoRo Terverifikasi',
    description:
      'MMSI dicocokkan dengan registry dan bukti pendukung. Klasifikasi AIS saja tidak cukup untuk menentukan sebuah kapal sebagai RoRo.',
    detail: 'Sumber dan status verifikasi menyertai identitas kapal.',
  },
  {
    id: 'pelabuhan-lintasan',
    label: 'Konteks rute',
    eyebrow: 'Lebih dari koordinat',
    title: 'Pelabuhan dan Lintasan yang Relevan',
    description:
      'Posisi kapal ditempatkan bersama pelabuhan dan lintasan terkait agar perjalanan lebih mudah dipahami.',
    detail:
      'Konteks rute berasal dari master data yang dapat ditinjau sumbernya.',
  },
  {
    id: 'riwayat-perjalanan',
    label: 'Riwayat 24 jam',
    eyebrow: 'Jejak terbatas',
    title: 'Riwayat Perjalanan Hingga 24 Jam',
    description:
      'Jejak posisi terbatas membantu membaca arah perjalanan tanpa menyimpan histori mentah tanpa batas.',
    detail: 'Riwayat dapat disampling dan tidak dimaksudkan untuk navigasi.',
  },
]

const freshnessStates = [
  {
    id: 'live',
    label: 'Live',
    range: 'Kurang dari 5 menit',
    relativeTime: '2 menit lalu',
    absoluteTime: '2 Agustus 2026 · 12.51 WIB',
    datetime: '2026-08-02T12:51:00+07:00',
    description: 'Posisi tergolong baru, tetapi tetap bukan data navigasi.',
  },
  {
    id: 'delayed',
    label: 'Delayed',
    range: '5–30 menit',
    relativeTime: '18 menit lalu',
    absoluteTime: '2 Agustus 2026 · 12.35 WIB',
    datetime: '2026-08-02T12:35:00+07:00',
    description: 'Data masih berguna sebagai konteks, namun sudah terlambat.',
  },
  {
    id: 'stale',
    label: 'Stale',
    range: '30 menit–6 jam',
    relativeTime: '2 jam lalu',
    absoluteTime: '2 Agustus 2026 · 10.53 WIB',
    datetime: '2026-08-02T10:53:00+07:00',
    description:
      'Posisi terakhir dipertahankan dan ditandai jelas sebagai lama.',
  },
  {
    id: 'offline',
    label: 'Offline',
    range: 'Lebih dari 6 jam',
    relativeTime: 'Tidak ada data baru',
    absoluteTime: '1 Agustus 2026 · 23.40 WIB',
    datetime: '2026-08-01T23:40:00+07:00',
    description:
      'Sistem tidak membuat posisi baru ketika sumber tidak tersedia.',
  },
]
</script>

<template>
  <div>
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
          data-testid="hero-title"
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
            data-testid="cta-view-map"
            class="inline-flex min-h-11 items-center justify-center rounded-md bg-[var(--color-primary)] px-5 font-semibold text-[#0d0f14] transition-colors hover:bg-[var(--color-primary-hover)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--color-focus)]"
          >
            Lihat peta kapal <span aria-hidden="true">→</span>
          </RouterLink>
          <a
            href="#fitur"
            class="inline-flex min-h-11 items-center justify-center rounded-md border border-[var(--color-border)] bg-[var(--color-surface)] px-5 font-semibold text-[var(--color-text-primary)] transition-colors hover:border-[var(--color-text-secondary)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--color-focus)]"
          >
            Cara kerja
          </a>
        </div>
        <p
          data-testid="disclaimer"
          class="mt-6 max-w-xl border-l border-[var(--color-primary)] pl-4 text-sm leading-6 text-[var(--color-text-secondary)]"
        >
          Bukan alat navigasi atau keselamatan. Data AIS dapat terlambat,
          hilang, atau tidak akurat.
        </p>
      </div>

      <figure
        class="route-stage hero-route-stage"
        role="img"
        aria-labelledby="hero-visual-caption"
      >
        <div aria-hidden="true">
          <span class="route-orbit"></span>
          <span class="route-lane route-lane-north"></span>
          <span class="route-lane route-lane-south"></span>
          <span class="route-lane route-lane-cross"></span>
          <span class="route-port route-port-merak"></span>
          <span class="route-port route-port-bakauheni"></span>
          <span class="route-port route-port-panjang"></span>
          <span class="route-port route-port-ciwandan"></span>
          <span class="route-label route-label-merak">Merak</span>
          <span class="route-label route-label-bakauheni">Bakauheni</span>
          <span class="route-label route-label-panjang">Panjang</span>
          <span class="route-label route-label-ciwandan">Ciwandan</span>
          <span class="vessel-marker vessel-orbit vessel-primary"></span>
          <span class="vessel-marker vessel-orbit vessel-secondary"></span>
          <span class="vessel-marker vessel-north vessel-traffic-1"></span>
          <span class="vessel-marker vessel-north vessel-traffic-2"></span>
          <span class="vessel-marker vessel-south vessel-traffic-3"></span>
          <span class="vessel-marker vessel-cross vessel-traffic-4"></span>

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
          Simulasi lalu lintas · bukan posisi aktual
        </figcaption>
      </figure>
    </section>

    <section
      id="fitur"
      aria-labelledby="features-heading"
      class="scroll-mt-20 border-t border-[var(--color-border)] py-20 sm:py-24"
    >
      <div class="site-container">
        <div class="max-w-3xl">
          <p
            class="font-mono text-xs font-semibold tracking-[0.18em] text-[var(--color-primary)] uppercase"
          >
            Cara membaca JejakBahari
          </p>
          <h2
            id="features-heading"
            class="mt-4 text-3xl leading-tight font-extrabold tracking-[-0.035em] sm:text-4xl"
          >
            Dari titik di peta menjadi konteks perjalanan.
          </h2>
          <p
            class="mt-5 max-w-2xl leading-7 text-[var(--color-text-secondary)]"
          >
            Lima lapisan informasi membantu pengguna memahami apa yang terlihat,
            seberapa baru datanya, dan dari mana konteksnya berasal.
          </p>
        </div>

        <nav
          aria-label="Bagian cara kerja"
          class="mt-8 flex gap-2 overflow-x-auto pb-2"
        >
          <a
            v-for="feature in features"
            :key="feature.id"
            :href="`#${feature.id}`"
            class="inline-flex min-h-11 flex-none items-center rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-4 text-sm font-semibold text-[var(--color-text-secondary)] transition-colors hover:border-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-focus)]"
          >
            {{ feature.label }}
          </a>
        </nav>

        <div class="mt-12 grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
          <figure
            class="route-stage lg:!sticky lg:top-24 lg:self-start"
            role="img"
            aria-labelledby="story-visual-caption"
          >
            <div aria-hidden="true">
              <span class="route-orbit"></span>
              <span class="route-port route-port-merak"></span>
              <span class="route-port route-port-bakauheni"></span>
              <span class="route-label route-label-merak">Pelabuhan asal</span>
              <span class="route-label route-label-bakauheni"
                >Pelabuhan tujuan</span
              >
              <span class="vessel-marker"></span>

              <div class="position-card">
                <p
                  class="font-mono text-[0.6875rem] tracking-[0.12em] text-[var(--color-text-secondary)] uppercase"
                >
                  Konteks yang menyertai posisi
                </p>
                <ul class="mt-3 grid grid-cols-2 gap-2 text-xs">
                  <li
                    v-for="feature in features"
                    :key="feature.id"
                    class="rounded border border-[var(--color-border)] px-2 py-2 text-[var(--color-text-primary)]"
                  >
                    {{ feature.label }}
                  </li>
                </ul>
              </div>
            </div>
            <figcaption
              id="story-visual-caption"
              class="absolute top-5 left-5 font-mono text-[0.6875rem] tracking-[0.12em] text-[var(--color-text-secondary)] uppercase"
            >
              Alur informasi · animasi ilustratif
            </figcaption>
          </figure>

          <div>
            <article
              v-for="(feature, index) in features"
              :id="feature.id"
              :key="feature.id"
              class="scroll-mt-24 border-t border-[var(--color-border)] py-14 first:border-t-0 first:pt-0 lg:min-h-[22rem] lg:py-20 lg:first:pt-10"
            >
              <p
                class="font-mono text-xs font-semibold tracking-[0.14em] text-[var(--color-info)] uppercase"
              >
                {{ String(index + 1).padStart(2, '0') }} ·
                {{ feature.eyebrow }}
              </p>
              <h3
                class="mt-4 max-w-xl text-2xl leading-tight font-bold tracking-[-0.025em] sm:text-3xl"
              >
                {{ feature.title }}
              </h3>
              <p
                class="mt-5 max-w-xl text-base leading-7 text-[var(--color-text-secondary)] sm:text-lg"
              >
                {{ feature.description }}
              </p>
              <p
                class="mt-6 max-w-xl border-l border-[var(--color-border)] pl-4 font-mono text-xs leading-6 text-[var(--color-text-secondary)]"
              >
                {{ feature.detail }}
              </p>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section
      id="transparansi-data"
      aria-labelledby="transparency-heading"
      class="scroll-mt-20 border-t border-[var(--color-border)] py-20 sm:py-24"
    >
      <div class="site-container">
        <div class="max-w-3xl">
          <p
            class="font-mono text-xs font-semibold tracking-[0.18em] text-[var(--color-primary)] uppercase"
          >
            Transparansi data
          </p>
          <h2
            id="transparency-heading"
            class="mt-4 text-3xl leading-tight font-extrabold tracking-[-0.035em] sm:text-4xl"
          >
            Posisi tanpa konteks bisa menyesatkan.
          </h2>
          <p
            class="mt-5 max-w-2xl leading-7 text-[var(--color-text-secondary)]"
          >
            JejakBahari selalu menyertakan usia data, waktu pembaruan, sumber,
            dan status verifikasi agar pengguna tahu apa yang sedang dilihat.
          </p>
        </div>

        <div class="mt-12 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <article
            v-for="state in freshnessStates"
            :key="state.id"
            class="freshness-card rounded-lg border border-[var(--color-border)] bg-[var(--color-surface-elevated)] p-5"
            :data-state="state.id"
          >
            <div class="flex items-center justify-between gap-4">
              <p class="flex items-center gap-2 font-bold">
                <span class="freshness-mark" aria-hidden="true"></span>
                {{ state.label }}
              </p>
              <span
                class="font-mono text-[0.6875rem] text-[var(--color-text-secondary)]"
              >
                {{ state.range }}
              </span>
            </div>
            <p class="mt-5 font-mono text-sm text-[var(--color-text-primary)]">
              {{ state.relativeTime }}
            </p>
            <time
              :datetime="state.datetime"
              class="mt-1 block font-mono text-[0.6875rem] leading-5 text-[var(--color-text-secondary)]"
            >
              {{ state.absoluteTime }}
            </time>
            <p
              class="mt-4 border-t border-[var(--color-border)] pt-4 text-sm leading-6 text-[var(--color-text-secondary)]"
            >
              {{ state.description }}
            </p>
          </article>
        </div>
        <p class="mt-3 font-mono text-xs text-[var(--color-text-secondary)]">
          Seluruh timestamp di atas adalah contoh tampilan, bukan data AIS
          aktual.
        </p>

        <div class="mt-16 grid gap-6 lg:grid-cols-2">
          <article
            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-6 sm:p-8"
          >
            <p
              class="font-mono text-xs font-semibold tracking-[0.14em] text-[var(--color-info)] uppercase"
            >
              Sebelum kapal tampil
            </p>
            <h3 class="mt-4 text-2xl font-bold tracking-[-0.025em]">
              MMSI masuk whitelist setelah diverifikasi.
            </h3>
            <ol class="mt-7 grid gap-5">
              <li class="grid grid-cols-[2rem_1fr] gap-3">
                <span class="trust-step" aria-hidden="true">01</span>
                <p class="text-sm leading-6 text-[var(--color-text-secondary)]">
                  MMSI sembilan digit dicocokkan dengan identitas kapal.
                </p>
              </li>
              <li class="grid grid-cols-[2rem_1fr] gap-3">
                <span class="trust-step" aria-hidden="true">02</span>
                <p class="text-sm leading-6 text-[var(--color-text-secondary)]">
                  Kategori RoRo diperiksa dari lebih dari satu bukti bila
                  tersedia.
                </p>
              </li>
              <li class="grid grid-cols-[2rem_1fr] gap-3">
                <span class="trust-step" aria-hidden="true">03</span>
                <p class="text-sm leading-6 text-[var(--color-text-secondary)]">
                  Hanya kapal yang lolos tinjauan yang masuk daftar publik.
                </p>
              </li>
            </ol>
          </article>

          <article
            class="rounded-lg border border-[var(--color-border)] bg-[var(--color-surface)] p-6 sm:p-8"
          >
            <p
              class="font-mono text-xs font-semibold tracking-[0.14em] text-[var(--color-info)] uppercase"
            >
              Jejak setiap klaim
            </p>
            <h3 class="mt-4 text-2xl font-bold tracking-[-0.025em]">
              Sumber dan confidence tetap dapat ditinjau.
            </h3>
            <dl class="mt-7 divide-y divide-[var(--color-border)] text-sm">
              <div class="grid grid-cols-[7rem_1fr] gap-4 py-3 first:pt-0">
                <dt class="font-mono text-[var(--color-text-secondary)]">
                  Provenance
                </dt>
                <dd>Sumber, referensi, dan waktu tinjauan.</dd>
              </div>
              <div class="grid grid-cols-[7rem_1fr] gap-4 py-3">
                <dt class="font-mono text-[var(--color-text-secondary)]">
                  Confidence
                </dt>
                <dd>Tingkat keyakinan berdasarkan kekuatan bukti.</dd>
              </div>
              <div class="grid grid-cols-[7rem_1fr] gap-4 py-3 last:pb-0">
                <dt class="font-mono text-[var(--color-text-secondary)]">
                  Status
                </dt>
                <dd>Verified, review, atau belum layak publik.</dd>
              </div>
            </dl>
          </article>
        </div>

        <div
          class="mt-6 rounded-lg border border-[var(--color-border)] bg-[var(--color-surface-elevated)] p-6 sm:p-8"
        >
          <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr]">
            <div>
              <p
                class="font-mono text-xs font-semibold tracking-[0.14em] text-[var(--color-primary)] uppercase"
              >
                Batas data AIS
              </p>
              <h3 class="mt-4 text-2xl font-bold tracking-[-0.025em]">
                Transparan berarti mengakui ketidakpastian.
              </h3>
            </div>
            <ul
              class="grid gap-3 text-sm leading-6 text-[var(--color-text-secondary)]"
            >
              <li class="flex gap-3">
                <span class="text-[var(--color-primary)]" aria-hidden="true"
                  >—</span
                >
                Sinyal, perangkat kapal, jaringan, atau provider dapat terlambat
                dan terputus.
              </li>
              <li class="flex gap-3">
                <span class="text-[var(--color-primary)]" aria-hidden="true"
                  >—</span
                >
                Identitas dan status navigasi yang dikirim kapal dapat salah
                atau tidak lengkap.
              </li>
              <li class="flex gap-3">
                <span class="text-[var(--color-primary)]" aria-hidden="true"
                  >—</span
                >
                Sistem mempertahankan posisi terakhir; tidak menciptakan posisi
                baru saat sumber berhenti.
              </li>
              <li class="flex gap-3">
                <span class="text-[var(--color-primary)]" aria-hidden="true"
                  >—</span
                >
                JejakBahari bukan alat navigasi, keselamatan, SAR, atau sumber
                operasional resmi.
              </li>
            </ul>
          </div>
        </div>

        <div
          class="mt-16 flex flex-col items-start justify-between gap-6 border-t border-[var(--color-border)] pt-10 sm:flex-row sm:items-center"
        >
          <div>
            <p class="text-xl font-bold">
              Lihat posisi dengan konteks lengkap.
            </p>
            <p class="mt-2 text-sm text-[var(--color-text-secondary)]">
              Usia, sumber, dan keterbatasan data selalu ikut ditampilkan.
            </p>
          </div>
          <RouterLink
            to="/peta"
            class="inline-flex min-h-11 items-center justify-center rounded-md bg-[var(--color-primary)] px-5 font-semibold text-[#0d0f14] transition-colors hover:bg-[var(--color-primary-hover)] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[var(--color-focus)]"
          >
            Buka peta kapal <span aria-hidden="true">→</span>
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

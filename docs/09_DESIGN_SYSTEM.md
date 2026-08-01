# 09 — Design System

## 1. Design Direction

**JejakBahari** memakai arah visual dark, maritime, data-centric, dan sederhana. Sistem mengadaptasi atmosfer [Commute Data Platform](https://data.commute.shiorilabs.id/)—latar gelap, grid titik, garis data, aksen coral, heading tegas, dan data mono—ke bahasa visual laut. Ini referensi arah, bukan sumber aset untuk disalin.

Kata kunci: `dark`, `clear`, `maritime`, `trace`, `trustworthy`, `non-critical`.

## 2. Color Tokens

Gunakan token semantik. Nilai baseline dark theme:

```css
:root {
  --color-bg: #0d0f14;
  --color-surface: #0a0c11;
  --color-surface-elevated: #151821;
  --color-text-primary: #f7f7f8;
  --color-text-secondary: #a7abb5;
  --color-border: #232733;
  --color-primary: #f55875;
  --color-primary-hover: #ff718b;
  --color-focus: #7dd3fc;
  --color-success: #86efac;
  --color-warning: #fde68a;
  --color-attention: #fdba74;
  --color-danger: #fca5a5;
  --color-info: #7dd3fc;
  --color-map-water: #080b12;
  --color-map-land: #171b24;
  --color-map-route: #38bdf8;
  --color-map-port: #fbbf24;
}
```

Freshness:

- `LIVE`: success.
- `DELAYED`: warning.
- `STALE`: attention.
- `OFFLINE`: neutral dengan icon offline; danger hanya untuk kegagalan.

Coral adalah identitas dan CTA, bukan warna status. Semua kombinasi teks harus lolos WCAG AA dan status tetap terbaca tanpa warna.

## 3. Typography

- Sans: `Plus Jakarta Sans`, fallback `ui-sans-serif, system-ui, sans-serif`.
- Mono: `JetBrains Mono`, fallback `ui-monospace, "SFMono-Regular", Consolas, monospace`.
- Muat hanya weight 400, 500, 600, 700, dan 800 yang benar-benar dipakai; gunakan `font-display: swap`.
- Implementasi frontend memakai variable font yang di-host bersama aplikasi melalui Fontsource dan hanya memuat subset Latin.
- Body minimum 16 px publik dan 14 px untuk tabel admin.
- Data numerik menggunakan `font-variant-numeric: tabular-nums`.

Skala:

```text
display: clamp(2.5rem, 7vw, 4.5rem) / 0.98 / 800
h1:      clamp(2rem, 5vw, 3rem) / 1.05 / 800
h2:      clamp(1.5rem, 3vw, 2.25rem) / 1.15 / 700
h3:      1.25rem / 1.3 / 700
body:    1rem / 1.625 / 400
small:   0.875rem / 1.5 / 400
label:   0.75rem / 1.25 / 600 / 0.05em
```

## 4. Spacing dan Layout

Skala 4 px:

```text
4, 8, 12, 16, 24, 32, 48, 64, 96
```

- Container konten maksimum 72rem.
- Teks naratif maksimum 42rem.
- Gutter: 16 px mobile, 24 px tablet, 32 px desktop.
- Section gap: 64 px mobile dan 96 px desktop.
- Gunakan CSS Grid/Flexbox; tidak perlu utility atau layout abstraction baru di luar Tailwind.

## 5. Radius, Border, dan Shadow

- Input/button: 6 px.
- Card: 8 px.
- Bottom sheet: 20 px pada sudut atas.
- Border: 1 px `--color-border`.
- Shadow hanya untuk overlay: `0 16px 48px rgb(0 0 0 / 0.35)`.
- Hindari glassmorphism berat; transparansi hanya bila teks dan kontrol tetap jelas di atas peta.

## 6. Motif Jejak

- Gunakan grid titik 4–8 px dan garis putus/titik sebagai metafora jejak kapal.
- Opacity dekorasi maksimal 12% di belakang teks dan 20% di area kosong.
- Warna boleh menggabungkan coral, cyan, dan biru rute.
- Motif tidak interaktif, tidak membawa arti status, dan tidak boleh menyerupai peta rute referensi.

## 7. Components

Komponen minimum MVP:

- Button, IconButton
- Input, SearchInput, Select, Checkbox
- Badge, FreshnessBadge
- Card, VesselCard, PortCard
- Drawer/BottomSheet, Dialog, Toast
- Skeleton, EmptyState, ErrorState
- Pagination, DataTable
- MapLegend

Jangan membuat komponen generik baru sebelum ada penggunaan nyata. Utamakan elemen HTML native, Tailwind, dan komponen yang sudah tersedia.

## 8. Button dan Focus

Varian: Primary, Secondary, Ghost, dan Danger.

- Primary memakai coral dengan teks yang memenuhi kontras.
- Secondary memakai surface dan border.
- Ghost tidak memiliki background saat idle.
- Danger hanya untuk tindakan destructive.
- Tinggi minimum 44 px pada layar sentuh.
- Semua varian memiliki default, hover, active, focus-visible, disabled, dan loading.
- Focus ring: 2 px `--color-focus` dengan offset 2 px.

## 9. Icons

- Gunakan satu library icon open-source yang sudah dipilih saat implementasi; jangan menambah library hanya untuk satu icon.
- Ukuran umum 16, 20, dan 24 px.
- Hindari emoji sebagai icon utama.
- Icon-only button wajib memiliki accessible name.

## 10. Map Styling

- Basemap gelap dengan air `--color-map-water` dan daratan `--color-map-land`.
- Label peta redup tetapi tetap terbaca; kapal menjadi fokus.
- Vessel marker memiliki outline dan arah yang jelas.
- Port marker berbeda bentuk dari vessel marker.
- Route/history memakai garis berbeda dan legenda tekstual.
- Attribution provider tidak boleh disembunyikan.
- Jangan memakai coral untuk semua layer; simpan coral untuk selection dan identitas.

## 11. Motion

- Durasi 120–240 ms dengan easing `cubic-bezier(.4, 0, .2, 1)`.
- Hormati `prefers-reduced-motion`.
- Marker boleh diinterpolasi halus hanya antar data valid; jangan menciptakan kesan data lebih baru.
- Loading dekoratif tidak boleh menghalangi pembacaan timestamp terakhir.

## 12. Responsive Breakpoints

Ikuti breakpoint Tailwind default:

- Mobile: single column, bottom sheet, kontrol sentuh 44 px.
- Tablet: map dengan drawer.
- Desktop: sidebar maksimal 24rem dengan map fleksibel.

Breakpoint dipilih karena konten membutuhkan ruang, bukan berdasarkan model perangkat tertentu.

## 13. Logo dan Penamaan

- Wordmark resmi ditulis `JejakBahari`.
- Baseline wordmark berupa teks Plus Jakarta Sans 800 dengan aksen coral; icon terpisah belum wajib untuk MVP.
- Jangan memakai, meniru, atau memodifikasi logo Commute Data Platform.
- Nama package, metadata aplikasi, title dokumen, PWA manifest, dan footer harus memakai `JejakBahari` saat implementasi dimulai.

# JejakBahari Frontend

Frontend publik JejakBahari menggunakan Vue 3, Vite, TypeScript, Tailwind CSS, dan Vue Router.

## Menjalankan lokal

```bash
pnpm install
pnpm dev
```

## Validasi

```bash
pnpm format:check
pnpm lint
pnpm typecheck
pnpm test
pnpm build
```

## Route awal

- `/`: fondasi landing page.
- `/peta`: placeholder peta sampai fase MapLibre dimulai.

Data AIS tidak boleh diakses langsung dari frontend. Semua data publik nantinya berasal dari Laravel REST API.

# JejakBahari Backend

Fondasi REST API Laravel 13 untuk JejakBahari. Sprint 0 menyediakan konfigurasi PostgreSQL/PostGIS, migration inti, formatter, dan test schema; endpoint bisnis dimulai pada Sprint 1.

## Menjalankan lokal

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

PostgreSQL/PostGIS lokal tersedia melalui `compose.yml` di root repository.

## Validasi

```bash
vendor/bin/pint --test
php artisan test
```

`CoreSchemaTest` berjalan penuh saat test menggunakan PostgreSQL/PostGIS dan dilewati pada SQLite.

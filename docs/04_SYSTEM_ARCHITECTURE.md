# 04 — System Architecture

## 1. Arsitektur Tingkat Tinggi

```mermaid
flowchart LR
    AIS[AIS Provider] -->|WebSocket| W[Node AIS Worker]
    W -->|Authenticated Internal API| API[Laravel API]
    API --> DB[(PostgreSQL + PostGIS)]
    API --> RV[Laravel Reverb]
    API -->|REST| WEB[Vue Web App]
    RV -->|WebSocket| WEB
    ADMIN[Admin User] --> WEB
    PUBLIC[Public User] --> WEB
```

## 2. Komponen

### Frontend

- Public map.
- Search dan vessel detail.
- Admin dashboard.
- Realtime client.

### Backend

- Public API.
- Admin API.
- Internal worker API.
- Vessel registry service.
- Position service.
- Geofence service.
- Event broadcasting.

### Worker

- Provider connection.
- Subscription.
- Validation.
- Normalization.
- Whitelist cache.
- Deduplication.
- Delivery retry.

### Database

- Master data.
- Latest position.
- History.
- Geofence events.
- Sources dan evidence.
- Audit logs.

## 3. Data Flow Posisi

```mermaid
sequenceDiagram
    participant P as AIS Provider
    participant W as AIS Worker
    participant A as Laravel API
    participant D as PostgreSQL
    participant R as Reverb
    participant U as Web App

    P->>W: AIS message
    W->>W: Parse, validate, normalize
    W->>W: Check whitelist and deduplicate
    W->>A: POST internal vessel position
    A->>A: Validate token and payload
    A->>D: Upsert latest position
    A->>D: Insert sampled history
    A->>A: Evaluate geofence
    A->>R: Broadcast update
    R-->>U: VesselPositionUpdated
```

## 4. Trust Boundaries

- Provider AIS adalah external untrusted input.
- Worker adalah semi-trusted service.
- Internal API tetap harus memvalidasi semua payload.
- Browser tidak pernah menerima secret provider.
- Admin operations membutuhkan authentication dan authorization.

## 5. Deployment Awal

```mermaid
flowchart TB
    CF[Cloudflare Pages] --> BROWSER[Browser]
    BROWSER --> NGINX[Nginx on VPS]
    NGINX --> PHP[Laravel/PHP-FPM]
    NGINX --> REVERB[Reverb]
    PHP --> PG[(PostgreSQL/PostGIS)]
    WORKER[Node AIS Worker] --> PHP
    QUEUE[Laravel Queue Worker] --> PG
```

## 6. Scaling Path

### Fase 1

Semua backend pada satu VPS.

### Fase 2

- Database managed atau VPS terpisah.
- Redis untuk queue/cache.
- Worker terpisah.
- Object storage untuk evidence.

### Fase 3

- Multi-provider ingestion.
- Partitioning history.
- Read replica atau analytics store bila diperlukan.

## 7. Architectural Rules

- Latest position dan history harus dipisah.
- Worker tidak menulis langsung ke database pada baseline; gunakan internal API.
- Frontend tidak terhubung ke provider AIS.
- Business rule berada di backend.
- Realtime adalah enhancement; REST tetap sumber pemulihan state.
- Seluruh interpretasi status harus dapat dilacak ke rule dan source data.

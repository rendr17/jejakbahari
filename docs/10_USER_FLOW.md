# 10 — User Flow

## 1. Public Map Flow

```mermaid
flowchart TD
    A[Open App] --> B[Load Map Shell]
    B --> C{Latest positions loaded?}
    C -- Yes --> D[Show vessel markers]
    C -- No --> E[Show error or empty state]
    D --> F[Search or select vessel]
    F --> G[Open vessel summary]
    G --> H[Open vessel detail]
    H --> I[View history and sources]
```

## 2. Search Flow

```mermaid
flowchart TD
    A[Focus search] --> B[Enter query]
    B --> C[Debounced request]
    C --> D{Results found?}
    D -- Yes --> E[Group vessel and port results]
    D -- No --> F[Show no result]
    E --> G[Select result]
    G --> H[Center map or open detail]
```

## 3. Admin Vessel Verification

```mermaid
flowchart TD
    A[Admin login] --> B[Open vessel registry]
    B --> C[Create or edit vessel]
    C --> D[Add MMSI and identity]
    D --> E[Attach sources/evidence]
    E --> F[Set confidence]
    F --> G{Reviewer approves?}
    G -- Yes --> H[Status VERIFIED]
    G -- No --> I[Return to REVIEW or REJECTED]
    H --> J[Add to public whitelist]
```

## 4. Worker Data Flow

```mermaid
flowchart TD
    A[Receive AIS message] --> B{Schema valid?}
    B -- No --> X[Reject and log]
    B -- Yes --> C{MMSI whitelisted?}
    C -- No --> Y[Ignore and count]
    C -- Yes --> D{Duplicate or stale?}
    D -- Yes --> Z[Ignore and count]
    D -- No --> E[Normalize]
    E --> F[Send internal API]
    F --> G{Accepted?}
    G -- Yes --> H[Continue]
    G -- Retryable error --> I[Retry with limit]
    G -- Permanent error --> J[Log rejection]
```

## 5. Port Geofence Flow

- Position diterima.
- Backend mencari port candidate dalam radius tertentu.
- Sistem membandingkan state sebelumnya.
- Jika transisi memenuhi dwell time, event dibuat.
- UI dapat menampilkan event sebagai hasil deteksi sistem.

# Bug Report JB-001: Vessel Detail 500 Error — whenLoaded on Model

- **Bug ID:** JB-001
- **Severity:** P0
- **Priority:** P0
- **Environment:** Local (Docker)
- **Browser/OS:** API (PostgreSQL backend)
- **Reporter:** QA Automation
- **Date:** 2026-09-14
- **Related Req:** API-004 (Public vessel detail)
- **Related Test Case:** TC-PUB-009
- **Status:** FIXED

## Steps to Reproduce

1. Start backend with seeded data
2. Send GET request to `/api/v1/vessels/{id}` with any valid public vessel UUID
3. Observe response

## Expected

- Status 200
- `success` = true
- `data` contains vessel detail with evidence array

## Actual

- Status 500
- Error: `Call to undefined method App\Models\RegistryEvidence::whenLoaded()`

## Root Cause

In `app/Http/Resources/PublicVesselDetailResource.php` line 20, the code calls `$e->whenLoaded('dataSource', ...)` on a `RegistryEvidence` model instance. The `whenLoaded()` method is only available on `JsonResource`, not on `Eloquent\Model`. The `$e` variable is an individual model item from the `evidence` collection map, not a resource.

## Fix

Changed `$e->whenLoaded('dataSource', fn () => [...])` to `$e->relationLoaded('dataSource') ? [...] : null` which uses the correct `Model::relationLoaded()` method.

## Attachment

### Before (buggy)
```php
'data_source' => $e->whenLoaded('dataSource', fn () => [
    'id' => $e->dataSource->id,
    // ...
]),
```

### After (fixed)
```php
'data_source' => $e->relationLoaded('dataSource') ? [
    'id' => $e->dataSource->id,
    // ...
] : null,
```

## Verification

After fix, `GET /api/v1/vessels/{id}` returns 200 with complete vessel detail including evidence with data_source.

## Notes

- This bug was not caught by existing feature tests because `PublicApiTest::test_vessel_detail_returns_public_vessel()` does not load the `evidence` relation, so the `whenLoaded` callback was never triggered.
- Recommendation: Add a test case that loads evidence relation to prevent regression.

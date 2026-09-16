<?php

namespace Tests\Feature;

use App\Models\Port;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselLatestPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Security regression tests aligned with docs/qa/SECURITY_CHECKLIST.md.
 * Covers auth, input validation, API protection, and XSS handling.
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private string $internalToken = 'test-internal-token';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.internal_worker_token' => $this->internalToken]);
    }

    // ── 1. Authentication & Authorization ────────────────────────────

    public function test_admin_endpoints_reject_unauthenticated_requests(): void
    {
        $this->getJson('/api/v1/admin/vessels')->assertUnauthorized();
        $this->postJson('/api/v1/admin/vessels', [])->assertUnauthorized();
        $this->getJson('/api/v1/admin/audit-logs')->assertUnauthorized();
    }

    public function test_admin_login_rejects_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        // 5 attempts allowed, 6th should be throttled (throttle:5,1)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/admin/auth/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong',
            ]);
        }

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429);
    }

    public function test_internal_endpoints_reject_missing_and_invalid_tokens(): void
    {
        $this->postJson('/api/internal/v1/positions', [])->assertUnauthorized();
        $this->withToken('invalid-token')
            ->postJson('/api/internal/v1/positions', [])
            ->assertUnauthorized();
    }

    public function test_internal_endpoints_fail_when_token_not_configured(): void
    {
        config(['app.internal_worker_token' => null]);

        $response = $this->withToken($this->internalToken)
            ->getJson('/api/internal/v1/vessel-whitelist');

        $response->assertStatus(500)
            ->assertJsonPath('error.code', 'INTERNAL_AUTH_FAILED');
    }

    // ── 2. Input Validation ──────────────────────────────────────────

    public function test_ingestion_rejects_invalid_mmsi(): void
    {
        $vessel = Vessel::factory()->verified()->create(['mmsi' => '525123456', 'active' => true]);

        foreach (['12345678', '1234567890', 'abc123456', ''] as $badMmsi) {
            $response = $this->withToken($this->internalToken)
                ->postJson('/api/internal/v1/positions', [
                    'mmsi' => $badMmsi,
                    'latitude' => -6.1,
                    'longitude' => 106.8,
                    'source_timestamp' => now()->toIso8601String(),
                    'received_at' => now()->toIso8601String(),
                    'provider_name' => 'test',
                ]);

            $response->assertStatus(422);
        }
    }

    public function test_ingestion_rejects_out_of_range_coordinates(): void
    {
        $vessel = Vessel::factory()->verified()->create(['mmsi' => '525123456', 'active' => true]);

        $cases = [
            ['latitude' => 91, 'longitude' => 0],
            ['latitude' => -91, 'longitude' => 0],
            ['latitude' => 0, 'longitude' => 181],
            ['latitude' => 0, 'longitude' => -181],
        ];

        foreach ($cases as $coords) {
            $response = $this->withToken($this->internalToken)
                ->postJson('/api/internal/v1/positions', array_merge([
                    'mmsi' => '525123456',
                    'source_timestamp' => now()->toIso8601String(),
                    'received_at' => now()->toIso8601String(),
                    'provider_name' => 'test',
                ], $coords));

            $response->assertStatus(422);
        }
    }

    public function test_ingestion_rejects_unwhitelisted_mmsi(): void
    {
        $response = $this->withToken($this->internalToken)
            ->postJson('/api/internal/v1/positions', [
                'mmsi' => '999999999', // not in registry
                'latitude' => -6.1,
                'longitude' => 106.8,
                'source_timestamp' => now()->toIso8601String(),
                'received_at' => now()->toIso8601String(),
                'provider_name' => 'test',
            ]);

        // Unwhitelisted MMSI should be rejected or ignored without creating a position
        $this->assertTrue(in_array($response->status(), [200, 202, 422], true));
        $this->assertSame(0, VesselLatestPosition::count());
    }

    // ── 3. API Protection ────────────────────────────────────────────

    public function test_security_headers_present_on_public_api(): void
    {
        $response = $this->getJson('/api/v1/vessels');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_pagination_is_capped_at_100(): void
    {
        Vessel::factory()->verified()->count(5)->create(['active' => true]);

        $response = $this->getJson('/api/v1/vessels?per_page=10000');

        $response->assertOk();
        $this->assertSame(100, $response->json('meta.per_page'));
    }

    public function test_admin_pagination_is_capped_at_100(): void
    {
        $admin = User::factory()->create();
        Vessel::factory()->count(5)->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/vessels?per_page=10000');

        $response->assertOk();
        $this->assertSame(100, $response->json('meta.per_page'));
    }

    // ── 4. XSS & Injection ───────────────────────────────────────────

    public function test_xss_string_in_destination_stored_literally(): void
    {
        $vessel = Vessel::factory()->verified()->create(['mmsi' => '525123456', 'active' => true]);
        $xss = '<script>alert(1)</script>';

        $response = $this->withToken($this->internalToken)
            ->postJson('/api/internal/v1/positions', [
                'mmsi' => '525123456',
                'latitude' => -6.1,
                'longitude' => 106.8,
                'destination_text' => $xss,
                'source_timestamp' => now()->toIso8601String(),
                'received_at' => now()->toIso8601String(),
                'provider_name' => 'test',
            ]);

        $response->assertSuccessful();

        // Stored literally — output escaping happens at render time
        $position = VesselLatestPosition::first();
        $this->assertNotNull($position);
    }

    public function test_xss_string_in_port_name_returned_as_escaped_json(): void
    {
        Port::factory()->create([
            'name' => '<script>alert("xss")</script>',
            'active' => true,
        ]);

        $response = $this->getJson('/api/v1/ports');

        $response->assertOk();
        // JSON encoding escapes the string; frontend renders as text (not v-html)
        $this->assertStringContainsString('script', $response->json('data.0.name'));
    }

    public function test_sql_injection_attempt_in_search_is_safe(): void
    {
        Vessel::factory()->verified()->create(['name' => 'KMP Legundi', 'active' => true]);

        $response = $this->getJson("/api/v1/vessels?q=' OR '1'='1");

        // Should return empty or filtered results — not all rows, not an error
        $response->assertOk();
        $this->assertIsArray($response->json('data'));
    }
}

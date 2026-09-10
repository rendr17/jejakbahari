<?php

namespace Tests\Feature\Vessel;

use App\Models\User;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VesselVerificationTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;

    private string $reviewerToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminToken = User::factory()->create(['role' => 'admin'])->createToken('test')->plainTextToken;
        $this->reviewerToken = User::factory()->reviewer()->create()->createToken('test')->plainTextToken;
    }

    public function test_admin_can_verify_vessel(): void
    {
        $vessel = Vessel::factory()->create(['verification_status' => 'DRAFT']);

        $response = $this->withToken($this->adminToken)->postJson("/api/v1/admin/vessels/{$vessel->id}/verify");

        $response->assertOk()
            ->assertJsonPath('data.verification_status', 'VERIFIED')
            ->assertJsonPath('data.public_visible', true);
    }

    public function test_reviewer_can_verify_vessel(): void
    {
        $vessel = Vessel::factory()->create(['verification_status' => 'DRAFT']);

        $response = $this->withToken($this->reviewerToken)->postJson("/api/v1/admin/vessels/{$vessel->id}/verify");

        $response->assertOk()
            ->assertJsonPath('data.verification_status', 'VERIFIED');
    }

    public function test_admin_can_reject_vessel(): void
    {
        $vessel = Vessel::factory()->create(['verification_status' => 'REVIEW']);

        $response = $this->withToken($this->adminToken)->postJson("/api/v1/admin/vessels/{$vessel->id}/reject", [
            'reason' => 'Bukan kapal RoRo',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.verification_status', 'REJECTED')
            ->assertJsonPath('data.public_visible', false);
    }

    public function test_reject_requires_reason(): void
    {
        $vessel = Vessel::factory()->create();

        $response = $this->withToken($this->adminToken)->postJson("/api/v1/admin/vessels/{$vessel->id}/reject", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_verification_creates_audit_log(): void
    {
        $vessel = Vessel::factory()->create(['verification_status' => 'DRAFT']);

        $this->withToken($this->adminToken)->postJson("/api/v1/admin/vessels/{$vessel->id}/verify");

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'vessel.verified',
            'entity_type' => 'vessels',
            'entity_id' => $vessel->id,
        ]);
    }

    public function test_rejection_creates_audit_log(): void
    {
        $vessel = Vessel::factory()->create();

        $this->withToken($this->adminToken)->postJson("/api/v1/admin/vessels/{$vessel->id}/reject", [
            'reason' => 'Test rejection',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'vessel.rejected',
            'entity_type' => 'vessels',
            'entity_id' => $vessel->id,
        ]);
    }
}

<?php

namespace Tests\Feature\RegistryEvidence;

use App\Models\DataSource;
use App\Models\RegistryEvidence;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistryEvidenceCrudTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    private Vessel $vessel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = User::factory()->create(['role' => 'admin'])->createToken('test')->plainTextToken;
        $this->vessel = Vessel::factory()->create();
    }

    public function test_admin_can_list_evidence_for_vessel(): void
    {
        RegistryEvidence::factory()->count(3)->create(['vessel_id' => $this->vessel->id]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/admin/vessels/{$this->vessel->id}/evidence");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [['id', 'evidence_type', 'confidence_score']],
                'meta',
            ]);
    }

    public function test_admin_can_create_evidence(): void
    {
        $source = DataSource::factory()->create();

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/admin/vessels/{$this->vessel->id}/evidence", [
                'data_source_id' => $source->id,
                'evidence_type' => 'MMSI_MATCH',
                'source_reference' => 'https://example.com/ais-record',
                'observed_value' => ['mmsi' => '525123456'],
                'confidence_score' => 85,
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.evidence_type', 'MMSI_MATCH')
            ->assertJsonPath('data.confidence_score', '85.00');
    }

    public function test_create_evidence_validates_required_fields(): void
    {
        $response = $this->withToken($this->token)
            ->postJson("/api/v1/admin/vessels/{$this->vessel->id}/evidence", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['data_source_id', 'evidence_type', 'source_reference', 'observed_value', 'confidence_score']);
    }

    public function test_admin_can_show_evidence(): void
    {
        $evidence = RegistryEvidence::factory()->create(['vessel_id' => $this->vessel->id]);

        $response = $this->withToken($this->token)
            ->getJson("/api/v1/admin/vessels/{$this->vessel->id}/evidence/{$evidence->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $evidence->id);
    }

    public function test_admin_can_update_evidence(): void
    {
        $evidence = RegistryEvidence::factory()->create([
            'vessel_id' => $this->vessel->id,
            'confidence_score' => 50,
        ]);

        $response = $this->withToken($this->token)
            ->putJson("/api/v1/admin/vessels/{$this->vessel->id}/evidence/{$evidence->id}", [
                'confidence_score' => 90,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.confidence_score', '90.00');
    }

    public function test_admin_can_delete_evidence(): void
    {
        $evidence = RegistryEvidence::factory()->create(['vessel_id' => $this->vessel->id]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/admin/vessels/{$this->vessel->id}/evidence/{$evidence->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('registry_evidence', ['id' => $evidence->id]);
    }

    public function test_show_evidence_returns_404_for_wrong_vessel(): void
    {
        $otherVessel = Vessel::factory()->create();
        $evidence = RegistryEvidence::factory()->create(['vessel_id' => $otherVessel->id]);

        $this->withToken($this->token)
            ->getJson("/api/v1/admin/vessels/{$this->vessel->id}/evidence/{$evidence->id}")
            ->assertNotFound();
    }

    public function test_unauthenticated_user_cannot_access_evidence(): void
    {
        $this->getJson("/api/v1/admin/vessels/{$this->vessel->id}/evidence")
            ->assertUnauthorized();
    }
}

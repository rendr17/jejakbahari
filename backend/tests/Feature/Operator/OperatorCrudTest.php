<?php

namespace Tests\Feature\Operator;

use App\Models\Operator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorCrudTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = User::factory()->create(['role' => 'admin'])->createToken('test')->plainTextToken;
    }

    public function test_admin_can_list_operators(): void
    {
        Operator::factory()->count(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/v1/admin/operators');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [['id', 'name', 'slug', 'active']],
                'meta' => ['current_page', 'per_page', 'total'],
            ]);
    }

    public function test_admin_can_create_operator(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/admin/operators', [
            'name' => 'ASDP Indonesia Ferry',
            'website_url' => 'https://example.com',
            'active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'ASDP Indonesia Ferry')
            ->assertJsonPath('data.slug', 'asdp-indonesia-ferry');
    }

    public function test_create_operator_validates_name(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/v1/admin/operators', [
            'website_url' => 'https://example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_admin_can_show_operator(): void
    {
        $operator = Operator::factory()->create();

        $response = $this->withToken($this->token)->getJson("/api/v1/admin/operators/{$operator->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $operator->id);
    }

    public function test_admin_can_update_operator(): void
    {
        $operator = Operator::factory()->create(['name' => 'Old Name']);

        $response = $this->withToken($this->token)->putJson("/api/v1/admin/operators/{$operator->id}", [
            'name' => 'New Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    public function test_admin_can_delete_operator(): void
    {
        $operator = Operator::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("/api/v1/admin/operators/{$operator->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('operators', ['id' => $operator->id]);
    }

    public function test_reviewer_cannot_create_operator(): void
    {
        $reviewerToken = User::factory()->reviewer()->create()->createToken('test')->plainTextToken;

        $response = $this->withToken($reviewerToken)->postJson('/api/v1/admin/operators', [
            'name' => 'Test Operator',
        ]);

        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_list_operators(): void
    {
        $this->getJson('/api/v1/admin/operators')->assertUnauthorized();
    }
}

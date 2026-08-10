<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserPreference;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLocalePriorityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LanguageSeeder::class);
    }

    public function test_saved_user_locale_has_priority_over_header_and_accept_language(): void
    {
        $user = User::factory()->create();
        UserPreference::query()->create([
            'user_id' => $user->getKey(),
            'namespace' => 'admin',
            'key' => 'locale',
            'value' => 'zh',
        ]);

        $this->actingAs($user)
            ->withHeaders([
                'X-Locale' => 'en',
                'Accept-Language' => 'vi-VN,vi;q=0.9',
            ])
            ->getJson('/api/v1/system/ping')
            ->assertOk()
            ->assertHeader('Content-Language', 'zh');
    }
}

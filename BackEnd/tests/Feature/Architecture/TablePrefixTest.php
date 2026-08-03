<?php

namespace Tests\Feature\Architecture;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class TablePrefixTest extends TestCase
{
    use RefreshDatabase;

    public function test_mysql_schema_contains_only_prefixed_tables(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());

        $database = DB::connection()->getDatabaseName();
        $rows = DB::select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME',
            [$database],
        );
        $tables = array_map(
            static fn (object $row): string => (string) $row->TABLE_NAME,
            $rows,
        );

        $expectedTables = [
            'hongvan_cache',
            'hongvan_cache_locks',
            'hongvan_branches',
            'hongvan_business_hours',
            'hongvan_contact_channels',
            'hongvan_crop_categories',
            'hongvan_crop_category_translations',
            'hongvan_crop_solution_products',
            'hongvan_crop_solution_translations',
            'hongvan_crop_solutions',
            'hongvan_crop_stage_translations',
            'hongvan_crop_stages',
            'hongvan_crop_translations',
            'hongvan_crops',
            'hongvan_failed_jobs',
            'hongvan_job_batches',
            'hongvan_jobs',
            'hongvan_languages',
            'hongvan_localized_slugs',
            'hongvan_audit_logs',
            'hongvan_media_folders',
            'hongvan_media',
            'hongvan_media_variants',
            'hongvan_media_tags',
            'hongvan_media_tag_links',
            'hongvan_media_usages',
            'hongvan_media_operations',
            'hongvan_migrations',
            'hongvan_notifications',
            'hongvan_password_reset_tokens',
            'hongvan_permission_role',
            'hongvan_permissions',
            'hongvan_personal_access_tokens',
            'hongvan_brand_translations',
            'hongvan_brands',
            'hongvan_product_attribute_definitions',
            'hongvan_product_attribute_values',
            'hongvan_product_categories',
            'hongvan_product_category_translations',
            'hongvan_product_media',
            'hongvan_product_related',
            'hongvan_product_specifications',
            'hongvan_product_tag_links',
            'hongvan_product_tags',
            'hongvan_product_translations',
            'hongvan_products',
            'hongvan_role_user',
            'hongvan_roles',
            'hongvan_service_categories',
            'hongvan_service_category_translations',
            'hongvan_service_media',
            'hongvan_service_translations',
            'hongvan_services',
            'hongvan_sessions',
            'hongvan_setting_groups',
            'hongvan_settings',
            'hongvan_social_links',
            'hongvan_translation_keys',
            'hongvan_translation_values',
            'hongvan_transport_request_status_histories',
            'hongvan_transport_requests',
            'hongvan_transport_route_translations',
            'hongvan_transport_routes',
            'hongvan_transport_service_area_translations',
            'hongvan_transport_service_areas',
            'hongvan_users',
            'hongvan_user_permission_overrides',
            'hongvan_user_preferences',
            'hongvan_vehicle_media',
            'hongvan_vehicle_translations',
            'hongvan_vehicle_type_translations',
            'hongvan_vehicle_types',
            'hongvan_vehicles',
        ];

        $this->assertEqualsCanonicalizing($expectedTables, $tables);

        foreach ($tables as $table) {
            $this->assertStringStartsWith('hongvan_', $table);
        }
    }

    public function test_framework_table_configuration_uses_explicit_prefixed_names(): void
    {
        $this->assertSame('', config('database.connections.mysql.prefix'));
        $this->assertSame('hongvan_migrations', config('database.migrations.table'));
        $this->assertSame('hongvan_password_reset_tokens', config('auth.passwords.users.table'));
        $this->assertSame('hongvan_sessions', config('session.table'));
        $this->assertSame('hongvan_cache', config('cache.stores.database.table'));
        $this->assertSame('hongvan_cache_locks', config('cache.stores.database.lock_table'));
        $this->assertSame('hongvan_jobs', config('queue.connections.database.table'));
        $this->assertSame('hongvan_job_batches', config('queue.batching.table'));
        $this->assertSame('hongvan_failed_jobs', config('queue.failed.table'));
    }

    public function test_public_id_is_a_ulid_while_the_primary_key_remains_internal(): void
    {
        $user = User::factory()->create();

        $this->assertIsInt($user->getKey());
        $this->assertTrue(Str::isUlid($user->public_id));
        $this->assertNotSame((string) $user->getKey(), $user->public_id);
        $this->assertSame('hongvan_notifications', $user->notifications()->getRelated()->getTable());
    }

    public function test_prefix_checker_accepts_repository_and_rejects_unprefixed_fixture(): void
    {
        $checker = base_path('../scripts/check-table-prefix.php');
        $repositoryProcess = new Process([PHP_BINARY, $checker], base_path('..'));
        $repositoryProcess->mustRun();

        $this->assertStringContainsString('Table prefix check passed', $repositoryProcess->getOutput());

        $fixtureDirectory = storage_path('framework/testing/table-prefix-'.bin2hex(random_bytes(8)));
        File::ensureDirectoryExists($fixtureDirectory);
        File::put(
            $fixtureDirectory.'/bad_tables.php',
            <<<'PHP'
<?php

Schema::create('users', function (): void {});
Schema::create('hongvan_hongvan_logs', function (): void {});

class BadModel
{
    protected $table = 'orders';
}
PHP,
        );

        try {
            $fixtureProcess = new Process([PHP_BINARY, $checker, '--path='.$fixtureDirectory], base_path('..'));
            $fixtureProcess->run();

            $this->assertFalse($fixtureProcess->isSuccessful());
            $this->assertStringContainsString("table 'users' without prefix hongvan_", $fixtureProcess->getErrorOutput());
            $this->assertStringContainsString("double-prefixed table 'hongvan_hongvan_logs'", $fixtureProcess->getErrorOutput());
            $this->assertStringContainsString("table 'orders' without prefix hongvan_", $fixtureProcess->getErrorOutput());
        } finally {
            File::deleteDirectory($fixtureDirectory);
        }
    }
}

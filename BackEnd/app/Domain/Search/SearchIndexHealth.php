<?php

namespace App\Domain\Search;

use Illuminate\Support\Facades\DB;

final class SearchIndexHealth
{
    /** @return array{driver: string, collation: string, healthy: bool, indexes: array<string, bool>, plans: array<string, array{access: string, key: string}>} */
    public function inspect(): array
    {
        $driver = DB::connection()->getDriverName();
        $database = DB::connection()->getDatabaseName();
        $collation = (string) DB::scalar('SELECT DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$database]);
        $expected = [
            'hongvan_product_translations' => 'hv_p41_product_search_ft',
            'hongvan_crop_solution_translations' => 'hv_p41_crop_solution_search_ft',
            'hongvan_service_translations' => 'hv_p41_service_search_ft',
            'hongvan_post_translations' => 'hv_p41_post_search_ft',
            'hongvan_project_translations' => 'hv_p41_project_search_ft',
        ];
        $indexes = [];
        $plans = [];

        foreach ($expected as $table => $index) {
            $indexes[$table] = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', $database)
                ->where('TABLE_NAME', $table)
                ->where('INDEX_NAME', $index)
                ->where('INDEX_TYPE', 'FULLTEXT')
                ->exists();
            $plan = DB::selectOne("EXPLAIN SELECT id FROM {$table} WHERE MATCH (search_text) AGAINST (? IN BOOLEAN MODE) LIMIT 10", ['+hongvan*']);
            $plans[$table] = [
                'access' => is_object($plan) ? (string) ($plan->type ?? '') : '',
                'key' => is_object($plan) ? (string) ($plan->key ?? '') : '',
            ];
        }

        return [
            'driver' => $driver,
            'collation' => $collation,
            'healthy' => $driver === 'mysql'
                && str_contains($collation, '_ai_ci')
                && ! in_array(false, $indexes, true)
                && ! in_array(false, array_map(static fn (array $plan): bool => $plan['access'] === 'fulltext', $plans), true),
            'indexes' => $indexes,
            'plans' => $plans,
        ];
    }
}

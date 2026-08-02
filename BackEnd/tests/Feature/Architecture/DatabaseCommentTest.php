<?php

namespace Tests\Feature\Architecture;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_table_and_column_has_a_clear_database_comment(): void
    {
        $this->assertSame('mysql', DB::connection()->getDriverName());

        $database = DB::connection()->getDatabaseName();
        $tables = DB::select(
            <<<'SQL'
                SELECT TABLE_NAME AS table_name
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_TYPE = 'BASE TABLE'
                  AND COALESCE(TABLE_COMMENT, '') = ''
                ORDER BY TABLE_NAME
                SQL,
            [$database],
        );
        $columns = DB::select(
            <<<'SQL'
                SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ?
                  AND COALESCE(COLUMN_COMMENT, '') = ''
                ORDER BY TABLE_NAME, ORDINAL_POSITION
                SQL,
            [$database],
        );

        $uncommentedTables = array_map(
            static fn (object $row): string => (string) $row->table_name,
            $tables,
        );
        $uncommentedColumns = array_map(
            static fn (object $row): string => $row->table_name.'.'.$row->column_name,
            $columns,
        );

        $this->assertSame([], $uncommentedTables, 'Tables without comments: '.implode(', ', $uncommentedTables));
        $this->assertSame([], $uncommentedColumns, 'Columns without comments: '.implode(', ', $uncommentedColumns));
    }
}

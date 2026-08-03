<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use RuntimeException;

final class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Demo data cannot be seeded in production.');
        }

        $this->call([
            DatabaseSeeder::class,
            DemoMediaSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}

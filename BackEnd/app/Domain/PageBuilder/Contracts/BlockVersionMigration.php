<?php

namespace App\Domain\PageBuilder\Contracts;

interface BlockVersionMigration
{
    public function fromVersion(): int;

    public function toVersion(): int;

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public function migrate(array $block): array;
}

<?php

namespace App\Domain\PageBuilder\Contracts;

interface BlockRenderer
{
    public function view(): string;
}

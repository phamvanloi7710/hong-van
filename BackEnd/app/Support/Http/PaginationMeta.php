<?php

namespace App\Support\Http;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;

/** @implements Arrayable<string, int> */
final readonly class PaginationMeta implements Arrayable
{
    public function __construct(
        public int $page,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}

    /**
     * @param  LengthAwarePaginator<array-key, mixed>  $paginator
     */
    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            page: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            total: $paginator->total(),
            lastPage: $paginator->lastPage(),
        );
    }

    /**
     * @return array{page: int, per_page: int, total: int, last_page: int}
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'last_page' => $this->lastPage,
        ];
    }
}

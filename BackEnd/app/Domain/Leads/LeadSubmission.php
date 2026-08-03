<?php

namespace App\Domain\Leads;

use App\Models\Lead;
use App\Models\TransportRequest;
use App\Models\WarehouseRequest;

final readonly class LeadSubmission
{
    public function __construct(
        public Lead $lead,
        public TransportRequest|WarehouseRequest|null $request,
        public bool $created,
    ) {}
}

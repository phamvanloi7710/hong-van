<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\Leads\LeadIntakeService;
use App\Domain\Leads\LeadSubmission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Leads\StoreContactRequest;
use App\Http\Requests\Api\V1\Leads\StoreQuoteRequest;
use App\Http\Requests\Api\V1\Transportation\StoreTransportRequest;
use App\Http\Requests\Api\V1\Warehouses\StoreWarehouseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PublicFormController extends Controller
{
    public function contact(StoreContactRequest $request, LeadIntakeService $intake): RedirectResponse
    {
        return $this->complete($request, $intake->contact($request->validated(), $request), 'contact');
    }

    public function quote(StoreQuoteRequest $request, LeadIntakeService $intake): RedirectResponse
    {
        return $this->complete($request, $intake->quote($request->validated(), $request), 'product_quote');
    }

    public function transport(StoreTransportRequest $request, LeadIntakeService $intake): RedirectResponse
    {
        return $this->complete($request, $intake->transport($request->validated(), $request), 'transport');
    }

    public function warehouse(StoreWarehouseRequest $request, LeadIntakeService $intake): RedirectResponse
    {
        return $this->complete($request, $intake->warehouse($request->validated(), $request), 'warehouse');
    }

    private function complete(Request $request, LeadSubmission $submission, string $formType): RedirectResponse
    {
        return redirect()->back(303)->with('page_builder_form_status', [
            'form_type' => $formType,
            'block_id' => (string) $request->input('_block_id', ''),
            'created' => $submission->created,
            'message' => __('leads.received'),
        ]);
    }
}

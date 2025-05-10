<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Request as RequestModel;
use Illuminate\Http\Request as HttpRequest;


class CaptainRequestController extends Controller
{
    public function index()
    {
        $query = Request::query()
            ->where('status', '!=', 'draft');
    
        if (request()->has('search') && trim(request('search')) !== '') {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $requests = $query
            ->with([
                'creator:id,name',
                'collaborators',
                'files',
                'timelines',
                'quotation',
                'purchaseRequest',
                'purchaseOrder'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return Inertia::render('captain/CaptainViewAll', [
            'requests' => RequestResource::collection($requests),
            'search' => request('search', ''),
        ]);
    }

    public function show($id)
    {
        $request = RequestModel::with([
            'creator',
            'collaborators',
            'files',
            'processor',
            'approver',
            'timelines.approver',
            'quotation.processor',
            'quotation.details.company',
            'quotation.details.items',
            'purchaseRequest',
        ])->findOrFail($id);

        return Inertia::render('captain/CaptainRequestView', [
            'request' => new RequestResource($request)
        ]);
    }

    public function approve(HttpRequest $httpRequest, $id)
    {
        $request = RequestModel::findOrFail($id);
        $currentProgress = $request->progress ?? 'Request Form';

        switch ($currentProgress) {
            case 'Request Form':
                $request->timelines()->create([
                    'approver_id' => $httpRequest->user()->id,
                    'approved_date' => now(),
                    'approved_progress' => $currentProgress,
                    'approved_status' => 'approved',
                    'remarks' => $httpRequest->remarks
                ]);
                $request->update(['progress' => 'Quotation']);
                Quotation::create([
                    'request_id' => $request->id,
                    'status' => 'pending',
                    'created_at' => now()
                ]);
                return redirect()->back()->with('success', 'Request approved and progressed.');
            
            case 'Quotation':
                $quotation = $request->quotation;
                $companyId = $httpRequest->input('company_id');
                if (!$companyId) {
                    return redirect()->back()->with('error', 'No company selected.');
                }
                QuotationDetail::where('request_quotation_id', $quotation->id)
                    ->update(['is_selected' => false]);
                QuotationDetail::where('request_quotation_id', $quotation->id)
                    ->where('company_id', $companyId)
                    ->update(['is_selected' => true]);
                    
                $quotation->request->timelines()->create([
                    'approver_id' => $httpRequest->user()->id,
                    'approved_date' => now(),
                    'approved_progress' => $currentProgress,
                    'approved_status' => 'approved',
                    'remarks' => $httpRequest->remarks
                ]);
                $request->update(['progress' => 'Purchase Request']);

                PurchaseRequest::create([
                    'request_id' => $request->id,
                    'status' => 'pending',
                    'processed_by' => null,
                    'processed_at' => null,
                ]);

                return redirect()->back()->with('success', 'Quotation approved and company selected.');

            default:
                return redirect()->back()->with('error', 'Invalid progress step.');
        }
    }

    public function decline(HttpRequest $httpRequest, $id)
    {
        $request = RequestModel::findOrFail($id);

        // Get current progress
        $currentProgress = $request->progress ?? 'Request Form';

        // Create timeline entry for decline
        $request->timelines()->create([
            'approver_id' => $httpRequest->user()->id,
            'approved_date' => now(),
            'approved_progress' => $currentProgress,
            'approved_status' => 'declined',
            'remarks' => $httpRequest->remarks
        ]);

        $request->update([
            'status' => 'declined',
            'progress' => $currentProgress
        ]);

        return redirect()->back()->with('success', 'Request has been declined.');
    }

    public function return(HttpRequest $httpRequest, $id)
    {
        $request = RequestModel::findOrFail($id);

        // Get current progress
        $currentProgress = $request->progress ?? 'Request Form';

        // Create timeline entry for return
        $request->timelines()->create([
            'approver_id' => $httpRequest->user()->id,
            'approved_date' => now(),
            'approved_progress' => $currentProgress,
            'approved_status' => 'returned',
            'remarks' => $httpRequest->remarks
        ]);

        $request->update([
            'status' => 'returned',
            'progress' => $currentProgress
        ]);

        return redirect()->back()->with('success', 'Request has been returned for revision.');
    }
}

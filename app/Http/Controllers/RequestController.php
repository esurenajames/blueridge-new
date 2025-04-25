<?php

namespace App\Http\Controllers;

use App\Models\RequestFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as RequestModel;
use Inertia\Inertia;

class RequestController extends Controller
{

    public function index($tab = 'all')
    {
        $baseQuery = RequestModel::query()
            ->with(['creator', 'collaborators', 'quotation', 'purchaseRequest', 'purchaseOrder'])
            ->where(function ($query) {
                $query->where('created_by', auth()->id())
                    ->orWhereHas('collaborators', function ($q) {
                        $q->where('users.id', auth()->id());
                    });
            });
    
        // Get all active requests including drafts for the 'all' tab
        $allRequests = (clone $baseQuery)
            ->whereIn('status', ['pending', 'draft'])
            ->latest()
            ->get();
    
        // Get requests that are in form stage (pending only, no entries in other tables)
        $formRequests = (clone $baseQuery)
            ->where('status', 'pending')
            ->whereDoesntHave('quotation')
            ->whereDoesntHave('purchaseRequest')
            ->whereDoesntHave('purchaseOrder')
            ->latest()
            ->get();
    
        // Get requests that are in quotation stage
        $quotationRequests = (clone $baseQuery)
            ->whereHas('quotation', function($query) {
                $query->where('status', 'pending');
            })
            ->latest()
            ->get();
    
        // Get requests that are in purchase request stage
        $purchaseRequestRequests = (clone $baseQuery)
            ->whereHas('purchaseRequest', function($query) {
                $query->where('status', 'pending');
            })
            ->latest()
            ->get();
    
        // Get requests that are in purchase order stage
        $purchaseOrderRequests = (clone $baseQuery)
            ->whereHas('purchaseOrder', function($query) {
                $query->where('status', 'pending');
            })
            ->latest()
            ->get();
    
        // Get completed or voided requests
        $historicalRequests = (clone $baseQuery)
            ->whereIn('status', ['completed', 'voided'])
            ->latest()
            ->get();
    
        $transformedRequests = collect([
            'all' => $allRequests,
            'form' => $formRequests,
            'quotation' => $quotationRequests,
            'purchase-request' => $purchaseRequestRequests,
            'purchase-order' => $purchaseOrderRequests,
            'history' => $historicalRequests
        ])->map(function ($requests) {  
            return $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'title' => $request->name,
                    'category' => $request->category,
                    'status' => $request->status,
                    'description' => $request->description,
                    'created_at' => $request->created_at->format('Y-m-d'),
                    'created_by' => $request->creator ? $request->creator->name : null,
                    'collaborators' => $request->collaborators->map(fn($collaborator) => [
                        'id' => $collaborator->id,
                        'name' => $collaborator->name,
                        'permission' => $collaborator->pivot->permission
                    ]),
                    'quotation' => $request->quotation ? [
                        'remarks' => $request->quotation->remarks,
                        'status' => $request->quotation->status
                    ] : null,
                    'purchase_request' => $request->purchaseRequest ? [
                        'remarks' => $request->purchaseRequest->remarks,
                        'status' => $request->purchaseRequest->status
                    ] : null,
                    'purchase_order' => $request->purchaseOrder ? [
                        'remarks' => $request->purchaseOrder->remarks,
                        'status' => $request->purchaseOrder->status
                    ] : null
                ];
            });
        });

        return Inertia::render('RequestViewAll', [
            'requests' => $transformedRequests,
            'initialTab' => $tab,
        ]);
    }

    public function downloadFile($id, $filename)
    {
        // Find the file record for this request and filename
        $file = RequestFile::where('request_id', $id)
            ->where('name', $filename)
            ->first();
    
        if (!$file || !Storage::disk('private')->exists($file->path)) {
            abort(404, 'File not found.');
        }
    
        return Storage::disk('private')->download($file->path, $file->name);
    }

    public function process($id)
    {
        $request = RequestModel::findOrFail($id);
        $request->status = 'pending';
        $request->save();
    
        return back()->with([
            'success' => 'Request processed successfully',
            'request' => $request
        ]);
    }
    
    public function void($id)
    {
        $request = RequestModel::findOrFail($id);
        $request->status = 'voided';
        $request->save();
    
        return back()->with([
            'success' => 'Request voided successfully',
            'request' => $request
        ]);
    }
}

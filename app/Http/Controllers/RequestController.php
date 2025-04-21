<?php

namespace App\Http\Controllers;

use App\Models\RequestFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as RequestModel;
use Inertia\Inertia;

class RequestController extends Controller
{

    public function index()
    {
        $baseQuery = RequestModel::query()
            ->with(['creator', 'collaborators'])
            ->where(function ($query) {
                $query->where('created_by', auth()->id())
                    ->orWhereHas('collaborators', function ($q) {
                        $q->where('users.id', auth()->id());
                    });
            });
    
        $activeRequests = (clone $baseQuery)
            ->whereNotIn('status', ['completed', 'voided'])
            ->latest()
            ->get();
    
        $historicalRequests = (clone $baseQuery)
            ->whereIn('status', ['completed', 'voided'])
            ->latest()
            ->get();
    
        $transformedRequests = collect([
            'all' => $activeRequests,
            'form' => $activeRequests->where('category', 'form'),
            'quotation' => $activeRequests->where('category', 'quotation'),
            'purchase-request' => $activeRequests->where('category', 'purchase-request'),
            'purchase-order' => $activeRequests->where('category', 'purchase-order'),
            'history' => $historicalRequests
        ])->map(function ($requests) {
            return $requests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'name' => $request->name,
                    'category' => $request->category,
                    'status' => $request->status,
                    'description' => $request->description,
                    'created_at' => $request->created_at->format('Y-m-d'),
                    'created_by' => $request->creator ? $request->creator->name : null,
                    'collaborators' => $request->collaborators->map(fn($collaborator) => [
                        'id' => $collaborator->id,
                        'name' => $collaborator->name,
                        'permission' => $collaborator->pivot->permission
                    ])
                ];
            });
        });
    
        return Inertia::render('RequestViewAll', [
            'requests' => $transformedRequests
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

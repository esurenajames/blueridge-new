<?php

namespace App\Http\Controllers\Officials;

use App\Http\Requests\RequestFormRequest;
use App\Models\RequestFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as RequestModel;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class OfficialRequestController extends Controller
{

    public function index($tab = 'all')
    {
        $activeUsers = User::query()
            ->where('status', 'active')
            ->where('role', 'official')
            ->select('id', 'name', 'role')
            ->get();

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

        return Inertia::render('officials/RequestViewAll', [
            'requests' => $transformedRequests,
            'initialTab' => $tab,
            'auth' => [
                'user' => auth()->user()->only(['id', 'name', 'email', 'role']),
                'users' => $activeUsers
            ]
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

    public function show($id)
    {
        $request = RequestModel::with(['creator', 'collaborators', 'files'])
            ->findOrFail($id);

        $userPermission = 'view';
        
        if ($request->created_by === auth()->id()) {
            $userPermission = 'owner';
        } else {
            $collaborator = $request->collaborators->where('id', auth()->id())->first();
            if ($collaborator) {
                $userPermission = $collaborator->pivot->permission;
            }
        }

        // Filter out users who are already collaborators
        $existingCollaboratorIds = $request->collaborators->pluck('id')->toArray();
        $activeUsers = User::query()
            ->where('status', 'active')
            ->where('role', 'official')
            ->whereNotIn('id', $existingCollaboratorIds)  // Exclude existing collaborators
            ->select('id', 'name', 'role')
            ->get();

        return Inertia::render('officials/RequestView', [
            'request' => [
                'id' => $request->id,
                'title' => $request->name,
                'category' => $request->category,
                'description' => $request->description,
                'status' => $request->status,
                'created_at' => $request->created_at->format('Y-m-d'),
                'created_by' => $request->creator->name,
                'collaborators' => $request->collaborators->map(fn($c) => [
                    'id' => $c->id,  // Include the ID in collaborator data
                    'name' => $c->name,
                    'role' => $c->role,
                    'permission' => $c->pivot->permission
                ]),
                'files' => $request->files->map(fn($f) => [
                    'name' => $f->name,
                    'size' => $f->size,
                    'uploaded_at' => $f->created_at->format('Y-m-d'),
                ]),
            ],
            'userPermission' => $userPermission,
            'activeUsers' => $activeUsers,
        ]);
    }

    public function update(RequestFormRequest $request, $id)
    {
        $requestModel = RequestModel::findOrFail($id);
        $validated = $request->validated();

        // Update basic info
        $requestModel->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'],
        ]);

        // Update collaborators
        if (isset($validated['collaborators'])) {
            $collaborators = collect($validated['collaborators'])->mapWithKeys(function ($collaborator) {
                return [$collaborator['id'] => ['permission' => $collaborator['permission']]];
            })->all();
            
            $requestModel->collaborators()->sync($collaborators);
        }

        // Handle removed files
        if ($request->has('removedFiles')) {
            $filesToDelete = $requestModel->files()
                ->whereIn('name', $request->removedFiles)
                ->get();

            foreach ($filesToDelete as $file) {
                Storage::disk('private')->delete($file->path);
                $file->delete();
            }
        }

        // Handle new file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('requests/' . $requestModel->id, 'private');
                
                $requestModel->files()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Reload the request with relationships
        $requestModel->load(['creator', 'collaborators', 'files']);

        return back()->with([
            'success' => 'Request updated successfully',
            'request' => [
                'id' => $requestModel->id,
                'title' => $requestModel->name,
                'category' => $requestModel->category,
                'description' => $requestModel->description,
                'status' => $requestModel->status,
                'created_at' => $requestModel->created_at->format('Y-m-d'),
                'created_by' => $requestModel->creator->name,
                'collaborators' => $requestModel->collaborators->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'role' => $c->role,
                    'permission' => $c->pivot->permission
                ]),
                'files' => $requestModel->files->map(fn($f) => [
                    'name' => $f->name,
                    'size' => $f->size,
                    'uploaded_at' => $f->created_at->format('Y-m-d'),
                ]),
            ]
        ]);
    }
}

<?php

namespace App\Http\Controllers\Officials;

use App\Http\Requests\QuotationFormRequest;
use App\Http\Requests\RequestFormRequest;
use App\Models\Company;
use App\Models\RequestFile;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as RequestModel;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use Illuminate\Http\Request as HttpRequest;

class OfficialRequestController extends Controller
{

    public function index($tab = 'all')
    {
        // Get active users for collaborator selection
        $activeUsers = User::query()
            ->where('status', 'active')
            ->where('role', 'official')
            ->select('id', 'name', 'role')
            ->get();
    
        // Base query to get requests user has access to
        $baseQuery = RequestModel::query()
            ->with([
                'creator', 
                'collaborators', 
                'files',
                'timelines.approver',
                'processor'
            ])
            ->where(function ($query) {
                $query->where('created_by', auth()->id())
                    ->orWhereHas('collaborators', function ($q) {
                        $q->where('users.id', auth()->id());
                    });
            });
    
        return Inertia::render('officials/RequestViewAll', [
            'requests' => [
                // All requests (pending + draft)
                'all' => RequestResource::collection(
                    (clone $baseQuery)
                        ->whereNotIn('status', ['voided', 'declined'])
                        ->latest()
                        ->get()
                ),
                
                // Request Form stage
                'form' => RequestResource::collection(
                    (clone $baseQuery)
                        ->whereIn('status', ['pending', 'returned'])
                        ->where('progress', 'Request Form')
                        ->latest()
                        ->get()
                ),
                
                // Quotation stage
                'quotation' => RequestResource::collection(
                    (clone $baseQuery)
                        ->whereIn('status', ['pending', 'returned'])
                        ->where('progress', 'Quotation')
                        ->latest()
                        ->get()
                ),
                
                // Purchase Request stage
                'purchase-request' => RequestResource::collection(
                    (clone $baseQuery)
                        ->whereIn('status', ['pending', 'returned'])
                        ->where('progress', 'Purchase Request')
                        ->latest()
                        ->get()
                ),
                
                // Purchase Order stage
                'purchase-order' => RequestResource::collection(
                    (clone $baseQuery)
                        ->whereIn('status', ['pending', 'returned'])
                        ->where('progress', 'Purchase Order')
                        ->latest()
                        ->get()
                ),
                
                // History (completed, voided, declined)
                'history' => RequestResource::collection(
                    (clone $baseQuery)
                        ->whereIn('status', ['completed', 'voided', 'declined'])
                        ->latest()
                        ->get()
                ),
            ],
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

    public function process(HttpRequest $httpRequest, $id)
    {
        $request = RequestModel::findOrFail($id);
        $request->status = 'pending';
        $request->processed_by = auth()->id();
        $request->processed_at = now();
        $request->save();

        $request->timelines()->create([
            'request_id' => $request->id,
            'processor_id' => auth()->id(),
            'processed_date' => now(),
            'processed_progress' => $request->progress,
            'processed_status' => 'processed',
            'remarks' => $httpRequest->remarks ?? null
        ]);
    
        return back()->with([
            'success' => 'Request processed successfully',
            'request' => $request
        ]);
    }

    public function processPurchaseRequest(HttpRequest $httpRequest, $id)
    {
        $request = RequestModel::findOrFail($id);
        
        // Update purchase request approval status
        $purchaseRequest = $request->purchaseRequest;
        $purchaseRequest->have_supplier_approval = true;
        $purchaseRequest->save();
    
        // Update request status
        $request->status = 'pending';
        $request->processed_by = auth()->id();
        $request->processed_at = now();
        $request->save();
    
        // Create timeline entry
        $request->timelines()->create([
            'request_id' => $request->id,
            'processor_id' => auth()->id(),
            'processed_date' => now(),
            'processed_progress' => 'Purchase Request',
            'processed_status' => 'processed',
            'remarks' => $httpRequest->remarks ?? null
        ]);
    
        return back()->with([
            'success' => 'Purchase Request processed successfully',
            'request' => new RequestResource($request->load(['purchaseRequest']))
        ]);
    }
    
    public function void(HttpRequest $httpRequest, $id)
    {
        $request = RequestModel::findOrFail($id);
        $request->status = 'voided';
        $request->save();

        $request->timelines()->create([
            'request_id' => $request->id,
            'processor_id' => auth()->id(),
            'processed_date' => now(),
            'processed_progress' => $request->progress,
            'processed_status' => 'voided',
            'remarks' => $httpRequest->remarks ?? null
        ]);
    
        return back()->with([
            'success' => 'Request voided successfully',
            'request' => $request
        ]);
    }

    public function show($id)
    {
        $request = RequestModel::with([
            'creator', 
            'collaborators', 
            'files', 
            'processor',
            'timelines.approver',
            'quotation.processor',
            'quotation.details.company',
            'quotation.details.items',
            'purchaseRequest',
        ])->findOrFail($id);
    
        $userPermission = 'view';
        
        if ($request->created_by === auth()->id()) {
            $userPermission = 'owner';
        } else {
            $collaborator = $request->collaborators->where('id', auth()->id())->first();
            if ($collaborator) {
                $userPermission = $collaborator->pivot->permission;
            }
        }
    
        $activeUsers = User::query()
            ->where('status', 'active')
            ->where('role', 'official')
            ->whereNotIn('id', [
                $request->created_by,  
                auth()->id()         
            ])
            ->whereNotIn('id', $request->collaborators->pluck('id'))
            ->select('id', 'name', 'role')
            ->get();
    
        return Inertia::render('officials/RequestView', [
            'request' => new RequestResource($request),
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
        $requestModel->load(['creator', 'collaborators', 'files', 'timelines.approver', 'processor']);
    
        return back()->with([
            'success' => 'Request updated successfully',
            'request' => new RequestResource($requestModel),
        ]);
    }

    public function resubmit(RequestFormRequest $request, $id)
    {
        $requestModel = RequestModel::findOrFail($id);
        $validated = $request->validated();
    
        // Always update status to pending first
        $requestModel->status = 'pending';
        $requestModel->save();

        $requestModel->timelines()->create([
            'request_id' => $requestModel->id,
            'processor_id' => auth()->id(),
            'processed_date' => now(),
            'processed_progress' => $requestModel->progress,
            'processed_status' => 'resubmitted',
            'remarks' => $request->remarks ?? null
        ]);
    
        // Update basic info if request form stage
        if ($requestModel->progress === 'Request Form') {
            // Update request details
            $requestModel->update([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'description' => $validated['description']
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
        }
    
        $requestModel->load(['creator', 'collaborators', 'files', 'timelines.approver', 'processor']);
    
        return back()->with([
            'success' => 'Request resubmitted successfully',
            'request' => new RequestResource($requestModel),
        ]);
    }

    public function resubmitQuotation(QuotationFormRequest $request, $id)
    {
        $requestModel = RequestModel::findOrFail($id);
    
        $requestModel->status = 'pending';
        $requestModel->save();

        // Update quotation status
        $quotation = $requestModel->quotation;
        if ($quotation) {
            $quotation->status = 'pending';
            $quotation->processed_by = auth()->id();
            $quotation->processed_at = now();
            $quotation->save();
    
            // Remove old details and items
            foreach ($quotation->details as $detail) {
                $detail->items()->delete();
                $detail->delete();
            }
        }
    
        // Process each company
        foreach ($request->validated()['companies'] as $companyData) {
            $company = Company::updateOrCreate(
                ['email' => $companyData['email']],
                [
                    'company_name' => $companyData['companyName'],
                    'contact_person' => $companyData['contactPerson'],
                    'address' => $companyData['address'],
                    'contact_number' => $companyData['contactNumber'],
                ]
            );
    
            // Create new quotation detail
            $quotationDetail = $quotation->details()->create([
                'company_id' => $company->id,
                'is_selected' => false,
            ]);
    
            // Create items for this quotation detail
            foreach ($companyData['items'] as $itemData) {
                $quotationDetail->items()->create([
                    'item_name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                ]);
            }
        }
    
        $requestModel->timelines()->create([
            'request_id' => $requestModel->id,
            'processor_id' => auth()->id(),
            'processed_date' => now(),
            'processed_progress' => $requestModel->progress,
            'processed_status' => 'resubmitted',
            'remarks' => $request->remarks ?? null
        ]);
    
        return back()->with([
            'success' => 'Quotation resubmitted successfully',
            'request' => new RequestResource($requestModel->load(['quotation.details.company', 'quotation.details.items']))
        ]);
    }

    public function submitQuotation(QuotationFormRequest $request, $id)
    {
        $requestModel = RequestModel::findOrFail($id);
        
        // Create or update quotation
        $quotation = $requestModel->quotation()->updateOrCreate(
            ['request_id' => $requestModel->id],
            [
                'status' => 'pending',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]
        );

        $quotation->have_quotation = 'true';   // To indicate "has quotation"
        $quotation->save();

        // Process each company
        foreach ($request->validated()['companies'] as $companyData) {
            // Create or update company
            $company = Company::updateOrCreate(
                ['email' => $companyData['email']],
                [
                    'company_name' => $companyData['companyName'],
                    'contact_person' => $companyData['contactPerson'],
                    'address' => $companyData['address'],
                    'contact_number' => $companyData['contactNumber'],
                ]
            );

            // Create quotation detail
            $quotationDetail = $quotation->details()->create([
                'company_id' => $company->id,
                'is_selected' => false,
            ]);

            // Create items for this quotation detail
            foreach ($companyData['items'] as $itemData) {
                $quotationDetail->items()->create([
                    'item_name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                ]);
            }
        }

        // Update request progress
        $requestModel->update([
            'status' => 'pending'
        ]);

        $requestModel->timelines()->create([
            'request_id' => $requestModel->id,
            'processor_id' => auth()->id(),
            'processed_date' => now(),
            'processed_progress' => $requestModel->progress,
            'processed_status' => 'submitted',
            'remarks' => $request->remarks ?? null
        ]);

        return back()->with([
            'success' => 'Quotation submitted successfully',
            'request' => new RequestResource($requestModel->load(['quotation.details.company', 'quotation.details.items']))
        ]);
    }

    public function generatePurchaseRequestPDF($id)
    {
        $request = RequestModel::with([
            'quotation.details.company',
            'quotation.details.items',
            'timelines.approver'
        ])->findOrFail($id);
    
        // Get selected quotation details
        $selectedQuotation = $request->quotation->details()
            ->where('is_selected', true)
            ->with(['company', 'items'])
            ->first();
    
        if (!$selectedQuotation) {
            abort(404, 'No selected quotation found');
        }
    
        // Get PR number from created_at and id
        $createdAt = $request->created_at;
        $prNumber = sprintf(
            '%s-%s-%s',
            $createdAt->format('Y'),
            $createdAt->format('m'),
            $request->id
        );
    
        // Find the timeline with approved status and progress = Purchase Request
        $approvedTimeline = $request->timelines
            ->where('approved_status', 'approved')
            ->where('approved_progress', 'Purchase Request')
            ->sortByDesc('approved_date')
            ->first();
    
        $date = $approvedTimeline
            ? Carbon::parse($approvedTimeline->approved_date)->format('Y-m-d')
            : null;
    
        // Get treasurer and captain
        $treasurer = User::where('role', 'treasurer')->first();
        $captain = User::where('role', 'captain')->first();
    
        $data = [
            'request' => $request,
            'date' => $date,
            'selectedQuotation' => $selectedQuotation,
            'prNumber' => $prNumber,
            'treasurer' => $treasurer,
            'captain' => $captain
        ];
    
        $pdf = Pdf::loadView('purchase-request', $data);
    
        return $pdf->download("purchase-request-{$prNumber}.pdf");
    }
    public function generateAbstractOfCanvassPDF($id)
    {
        $request = RequestModel::with([
            'quotation.details.company',
            'quotation.details.items',
            'timelines.approver'
        ])->findOrFail($id);
        
        if (!$request->quotation || !$request->quotation->details->count()) {
            abort(404, 'Quotation details not found');
        }

        // Get the selected quotation details (same as in Purchase Request)
        $selectedQuotation = $request->quotation->details()
            ->where('is_selected', true)
            ->with(['company', 'items'])
            ->first();

        // If no selected quotation exists yet, use lowest total price company as default
        $usePriceBased = !$selectedQuotation;

        // Group items by their names to compare prices across companies
        $items = [];
        $companyTotals = [];
        $companies = collect();

        foreach ($request->quotation->details as $detail) {
            // Add company to collection if not already present
            if (!$companies->contains('id', $detail->company->id)) {
                $companies->push($detail->company);
                $companyTotals[$detail->company->id] = 0;
            }
            
            // Group items by name for comparison across companies
            foreach ($detail->items as $item) {
                $itemKey = $item->item_name . '-' . $item->description;
                
                if (!isset($items[$itemKey])) {
                    $items[$itemKey] = [
                        'name' => $item->item_name,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'companies' => []
                    ];
                }
                
                $itemTotal = $item->price * $item->quantity;
                $items[$itemKey]['companies'][$detail->company->id] = [
                    'price' => $item->price,
                    'total' => $itemTotal
                ];
                
                // Add to company's total
                $companyTotals[$detail->company->id] += $itemTotal;
            }
        }

        // Find company with lowest total price
        $minTotal = min($companyTotals);
        $awardedCompanyId = array_keys($companyTotals, $minTotal)[0];
        $awardedCompany = $companies->firstWhere('id', $awardedCompanyId);

        // If no selection was made, create a temporary selectedQuotation object using the awarded company
        if ($usePriceBased) {
            $tempDetail = new \stdClass();
            $tempDetail->company = $awardedCompany;
            $selectedQuotation = $tempDetail;
        }

        // Get barangay officials
        $secretary = User::where('role', 'secretary')->first();
        $treasurer = User::where('role', 'treasurer')->first();
        $captain = User::where('role', 'captain')->first();

        $data = [
            'items' => $items,
            'companies' => $companies,
            'companyTotals' => $companyTotals,
            'awardedCompany' => $awardedCompany,
            'selectedQuotation' => $selectedQuotation, // Add this line
            'location' => 'BARANGAY BLUE RIDGE B',
            'district' => 'QUEZON CITY, DISTRICT III',
            'secretary' => $secretary ? $secretary->name : 'Barangay Secretary',
            'treasurer' => $treasurer ? $treasurer->name : 'Barangay Treasurer',
            'captain' => $captain ? $captain->name : 'Punong Barangay',
        ];

        $pdf = Pdf::loadView('abstract-of-canvass', $data);
        
        return $pdf->download('abstract-of-canvass.pdf');
    }
}
   
<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Models\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Request as RequestModel;

class CaptainRequestController extends Controller
{
    public function index()
    {
        $requests = Request::query()
            ->where('status', '!=', 'draft')
            ->with(['creator:id,name', 'collaborators', 'files', 'quotation', 'purchaseRequest', 'purchaseOrder'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                $progress = 0; // Start with 0 progress for pending requests
                
                // Only add progress if request is not pending and has related records
                if ($request->status !== 'pending') {
                    // Form completion
                    $progress += 25;
                    
                    // Check for actual quotation record
                    if ($request->quotation()->exists()) {
                        $progress += 25;
                    }
                    
                    if ($request->purchaseRequest()->exists()) {
                        $progress += 25;
                    }
                    
                    if ($request->purchaseOrder()->exists()) {
                        $progress += 25;
                    }
                }
    
                return [
                    'id' => $request->id,
                    'title' => $request->name,
                    'category' => $request->category,
                    'status' => $request->status,
                    'description' => $request->description,
                    'created_at' => $request->created_at->format('Y-m-d'),
                    'created_by' => $request->creator ? $request->creator->name : 'Unknown User',
                    'progress' => $progress,
                    'stages' => [
                        'form' => $request->status !== 'pending', // Only true if not pending
                        'quotation' => $request->quotation()->exists(),
                        'purchaseRequest' => $request->purchaseRequest()->exists(),
                        'purchaseOrder' => $request->purchaseOrder()->exists(),
                    ],
                    'collaborators' => $request->collaborators->map(fn($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'permission' => $c->pivot->permission
                    ]),
                    'files' => $request->files->map(fn($f) => [
                        'name' => $f->name,
                        'size' => $f->size,
                        'uploaded_at' => $f->created_at->format('Y-m-d'),
                    ]),
                ];
            });

        return Inertia::render('captain/CaptainViewAll', [
            'requests' => $requests
        ]);
    }

    public function show($id)
    {
        $request = RequestModel::with(['creator', 'collaborators', 'files', 'processor'])
        ->findOrFail($id);

        return Inertia::render('captain/CaptainRequestView', [
            'request' => [
                'id' => $request->id,
                'title' => $request->name, 
                'category' => $request->category,
                'description' => $request->description,
                'status' => $request->status,
                'created_at' => $request->created_at->format('Y-m-d'),
                'created_by' => $request->creator ? $request->creator->name : 'Unknown User',
                'processed_by' => $request->processor ? $request->processor->name : null,
                'processed_at' => $request->processed_at ? Carbon::parse($request->processed_at)->format('Y-m-d') : null,
                'files' => $request->files->map(fn($f) => [
                    'name' => $f->name,
                    'size' => $f->size,
                    'uploaded_at' => $f->created_at->format('Y-m-d'),
                ]),
                'collaborators' => $request->collaborators->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'role' => $c->role,
                    'permission' => $c->pivot->permission
                ]),
            ],
        ]);
    }
}
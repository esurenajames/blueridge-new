<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Models\Request;
use Inertia\Inertia;

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
                $progress = 25; // Start with form completion (25%)
                
                if ($request->quotation) {
                    $progress += 25;
                }
                if ($request->purchaseRequest) {
                    $progress += 25;
                }
                if ($request->purchaseOrder) {
                    $progress += 25;
                }
    
                return [
                    'id' => $request->id,
                    'title' => $request->name,
                    'category' => $request->category,
                    'status' => $request->status,
                    'description' => $request->description,
                    'created_at' => $request->created_at->format('M d, Y'),
                    'created_by' => $request->creator ? $request->creator->name : 'Unknown User',
                    'progress' => $progress,
                    'stages' => [
                        'form' => true, // Always true since it exists
                        'quotation' => (bool)$request->quotation,
                        'purchaseRequest' => (bool)$request->purchaseRequest,
                        'purchaseOrder' => (bool)$request->purchaseOrder,
                    ],
                    'collaborators' => $request->collaborators->map(fn($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'permission' => $c->pivot->permission
                    ]),
                    'files' => $request->files->map(fn($f) => [
                        'name' => $f->name,
                        'size' => $f->size,
                        'uploaded_at' => $f->created_at->format('M d, Y'),
                    ]),
                ];
            });

        return Inertia::render('captain/CaptainViewAll', [
            'requests' => $requests
        ]);
    }

    public function show($id)
    {
        $request = Request::with(['creator:id,name', 'files', 'collaborators.user:id,name,role'])
            ->findOrFail($id);

        return Inertia::render('captain/RequestView', [
            'request' => [
                'id' => $request->id,
                'title' => $request->name, // Changed from title to name
                'category' => $request->category,
                'description' => $request->description,
                'status' => $request->status,
                'created_at' => $request->created_at->format('M d, Y'),
                'created_by' => $request->creator ? $request->creator->name : 'Unknown User',
                'files' => $request->files->map(fn($f) => [
                    'name' => $f->name,
                    'size' => $f->size,
                    'uploaded_at' => $f->created_at->format('M d, Y'),
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
<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequestResource;
use App\Models\Request;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Request as RequestModel;

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
            ->with(['creator:id,name', 'collaborators', 'files', 'quotation', 'purchaseRequest', 'purchaseOrder'])
            ->orderBy('created_at', 'desc')
            ->paginate(9);
    
        return Inertia::render('captain/CaptainViewAll', [
            'requests' => RequestResource::collection($requests),
            'search' => request('search', ''),
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
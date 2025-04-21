<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestFormRequest;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->where('status', 'active')  
            ->get();
    
        return Inertia::render('Dashboard', [
            'activeUsers' => $users
        ]);
    }
    
    public function storeRequest(RequestFormRequest $request)
    {
        $validatedData = $request->validated();
        $newRequest = RequestModel::create([
            'name' => $validatedData['name'],
            'category' => $validatedData['category'],
            'description' => $validatedData['description'],
            'status' => 'draft',
            'created_by' => auth()->id()
        ]);

        if (!empty($validatedData['collaborators'])) {
            foreach ($validatedData['collaborators'] as $collaborator) {
                $newRequest->collaborators()->attach($collaborator['id'], [
                    'permission' => $collaborator['permission']
                ]);
            }
        }

        if (!empty($validatedData['files'])) {
            foreach ($validatedData['files'] as $file) {
                $path = $file->store('request-files');
                
                $newRequest->files()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'document_type' => 'request' 
                ]);
            }
        }

        return to_route('requests.view', [
            'id' => $newRequest->id
        ])->with('success', 'Request created successfully');
    }

    public function show($id)
    {
        $request = RequestModel::with(['collaborators', 'files', 'creator'])
            ->findOrFail($id);

        return Inertia::render('RequestView', [
            'request' => [
                'id' => $request->id,
                'title' => $request->name,
                'category' => $request->category,
                'description' => $request->description,
                'status' => $request->status,
                'created_at' => $request->created_at->format('Y-m-d'),
                'collaborators' => $request->collaborators->map(fn($c) => [
                    'name' => $c->name,
                    'role' => $c->role
                ]),
                'files' => $request->files->map(fn($f) => [
                    'name' => $f->name,
                    'size' => $f->size,
                    'uploaded_at' => $f->created_at->format('Y-m-d')
                ]),
                'created_by' => $request->creator->name
            ]
        ]);
    }
}
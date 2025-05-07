<?php

namespace App\Http\Controllers\Officials;

use App\Http\Requests\RequestFormRequest;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class OfficialDashboardController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->where('status', 'active')
            ->where('role', 'official')
            ->where('id', '!=', auth()->id())  
            ->get();
    
        $myRequests = RequestModel::where('created_by', auth()->id())
            ->whereNotIn('status', ['draft', 'voided', 'declined', 'completed'])
            ->withCount(['files'])
            ->get();
    
        $collaborativeRequests = RequestModel::whereHas('collaborators', function($query) {
            $query->where('user_id', auth()->id());
        })
            ->whereNotIn('status', ['draft', 'voided', 'declined', 'completed'])
            ->withCount(['files'])
            ->get();
    
        return Inertia::render('officials/Dashboard', [
            'activeUsers' => $users,
            'requestStats' => [
                'single' => [
                    'requestForms' => $myRequests->count(),
                    'quotations' => $myRequests->where('category', 'quotation')->count(),
                    'purchaseRequests' => $myRequests->where('category', 'purchase_request')->count(),
                    'purchaseOrders' => $myRequests->where('category', 'purchase_order')->count(),
                ],
                'collaborative' => [
                    'requestForms' => $collaborativeRequests->count(),
                    'quotations' => $collaborativeRequests->where('category', 'quotation')->count(),
                    'purchaseRequests' => $collaborativeRequests->where('category', 'purchase_request')->count(),
                    'purchaseOrders' => $collaborativeRequests->where('category', 'purchase_order')->count(),
                ]
            ]
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

        return redirect()->route('requests.view', ['id' => $newRequest->id])->with([
            'success' => 'Request created successfully'
        ]);
    }
}
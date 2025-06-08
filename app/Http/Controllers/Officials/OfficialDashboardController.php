<?php

namespace App\Http\Controllers\Officials;

use App\Http\Requests\RequestFormRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
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
            ->whereNotIn('status', [ 'voided', 'declined', 'completed'])
            ->withCount(['files'])
            ->get();

        return Inertia::render('officials/Dashboard', [
            'activeUsers' => $users,
            'requestStats' => [
                'single' => [
                    'requestForms' => $myRequests->where('progress', 'Request Form')->count(),
                    'quotations' => $myRequests->where('progress', 'Quotation')->count(),
                    'purchaseRequests' => $myRequests->where('progress', 'Purchase Request')->count(),
                    'purchaseOrders' => $myRequests->where('progress', 'Purchase Order')->count(),
                    'total' => $myRequests->count()
                ],
                'collaborative' => [
                    'requestForms' => $collaborativeRequests->where('progress', 'Request Form')->count(),
                    'quotations' => $collaborativeRequests->where('progress', 'Quotation')->count(),
                    'purchaseRequests' => $collaborativeRequests->where('progress', 'Purchase Request')->count(),
                    'purchaseOrders' => $collaborativeRequests->where('progress', 'Purchase Order')->count(),
                    'total' => $collaborativeRequests->count()
                ]
            ]
        ]);
    }

    public function getCategories()
    {
        $categories = CategoryResource::collection(
            Category::where('status', 'active')
                ->where('group_name', '!=', 'Beginning Cash Balance')
                ->get()
        );
        return response()->json($categories);
    }
    
    public function storeRequest(RequestFormRequest $request)
    {
        $validatedData = $request->validated();
        $newRequest = RequestModel::create([
            'name' => $validatedData['name'],
            'category_id' => $validatedData['category'], // store as category_id
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
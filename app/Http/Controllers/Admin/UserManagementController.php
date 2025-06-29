<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserManagementResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        $query = $this->filter($query, $request);

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('admin/UserManagement', [
            'users' => [
                'data' => UserManagementResource::collection($users)->resolve(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'search' => $request->input('search'),
        ]);
    }

    private function filter($query, Request $request)
    {
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $type = $request->input('type');
        if ($type) {
            $query->where('role', $type);
        }

        $status = $request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();
    
        $adminCount = User::where('role', 'admin')->count();
    
        if ($user->role === 'admin' && $adminCount === 1 && $validated['role'] !== 'admin') {
            return redirect()->back()->withErrors(['error' => 'You cannot change the role of the last remaining admin.']);
        }
        
        if ($user->id === auth()->id() && $validated['status'] !== $user->status) {
            return redirect()->back()->withErrors(['error' => 'You cannot change your own status.']);
        }
    
        $user->update($validated);
    
        return redirect()->back()->with([
            'success' => 'User updated successfully',
            'user' => new UserManagementResource($user),
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->back()->withErrors(['error' => 'Admin accounts cannot be deleted.']);
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete your own account.']);
        }
    
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    public function create(UserRequest $request)
    {
        $validated = $request->validated();

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }
        
        $user = User::create($validated);
    
        return redirect()->back()->with([
            'success' => 'User created successfully',
            'user' => new UserManagementResource($user)
        ]);
    }

    public function updatePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors([
                'error' => 'You cannot change your own password through this interface.'
            ]);
        }

        $user->update([
            'password' => bcrypt($validated['password'])
        ]);

        return redirect()->back()->with([
            'success' => 'Password updated successfully',
            'user' => new UserManagementResource($user)
        ]);
    }
}
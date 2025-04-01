<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserManagementResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
    
        return Inertia::render('UserManagement', [
            'users' => UserManagementResource::collection($users)->toArray(request()),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => ['required', 'string', Rule::in(['captain', 'admin', 'official', 'secretary', 'treasurer'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);
    
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
}
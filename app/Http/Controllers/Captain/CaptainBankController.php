<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccountRequest;
use App\Http\Resources\BankResource;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaptainBankController extends Controller
{
    public function index(Request $request)
    {
        $query = BankAccount::query();
    
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bank', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }
    
        $accounts = $query->orderBy('created_at', 'desc')->paginate(10);
    
        return Inertia::render('captain/CaptainBankAccount', [
            'accounts' => [
                'data' => BankResource::collection($accounts)->resolve(),
                'current_page' => $accounts->currentPage(),
                'last_page' => $accounts->lastPage(),
                'per_page' => $accounts->perPage(),
                'total' => $accounts->total(),
            ],
        ]);
    }

    public function create(BankAccountRequest $request)
    {
        $validated = $request->validated();
        
        $bankAccount = BankAccount::create($validated);
    
        return redirect()->back()->with([
            'success' => 'Bank account created successfully',
            'account' => new BankResource($bankAccount)
        ]);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount)
    {
        $validated = $request->validated();

        $bankAccount->update($validated);

        return redirect()->back()->with([
            'success' => 'Bank account updated successfully',
            'account' => new BankResource($bankAccount)
        ]);
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();

        return redirect()->back()->with([
            'success' => 'Bank account deleted successfully',
            'account' => new BankResource($bankAccount)
        ]);
    }

    public function getBankAccounts()
    {
        $accounts = BankAccount::where('status', 'active')->get();
        return response()->json([
            'data' => BankResource::collection($accounts)
        ]);
    }
}
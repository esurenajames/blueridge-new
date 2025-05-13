<?php


namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionHistoryResource;
use App\Models\FundTransactionHistory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaptainTransactionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = FundTransactionHistory::query();

        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")
                ->orWhereHas('processedBy', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Type filter
        $type = $request->input('type');
        if ($type) {
            $query->where('type', $type);
        }

        // Date range filter
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        if ($dateFrom && $dateTo) {
            $query->whereBetween('transaction_date', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->where('transaction_date', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->where('transaction_date', '<=', $dateTo);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(10);

        return Inertia::render('captain/CaptainTransactionHistory', [
            'transactions' => [
                'data' => TransactionHistoryResource::collection($transactions)->resolve(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
            // Optionally, return the filters to the frontend for state
            'search' => $search,
            'type' => $type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }
}
<?php

namespace App\Http\Controllers\Captain;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionHistoryResource;
use App\Models\FundTransactionHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CaptainTransactionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = FundTransactionHistory::query();
        $query = $this->filter($query, $request);

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(10);

        return Inertia::render('captain/CaptainTransactionHistory', [
            'transactions' => [
                'data' => TransactionHistoryResource::collection($transactions)->resolve(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
            'search' => $request->input('search'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);
    }

    private function filter($query, Request $request)
    {
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

        return $query;
    }
}
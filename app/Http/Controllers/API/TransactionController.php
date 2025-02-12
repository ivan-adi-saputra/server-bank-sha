<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {

        $limit = $request->query('limit') !== null ? $request->query('limit') : 10;

        $relations = [
            'product',
            'paymentMethod:id,name,code,thumbnail',
            'transactionType:id,name,code,action,thumbnail'
        ];

        $user = Auth::user();

        $transactions = Transaction::with($relations)
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $transactions->getCollection()->transform(function ($item) {
            $paymentMethodThumbnail = $item->paymentMethod->thumbnail ?
                url('banks/' . $item->paymentMethod->thumbnail) : '';
            $item->paymentMethod = clone $item->paymentMethod;
            $item->paymentMethod->thumbnail = $paymentMethodThumbnail;

            $transactionType = $item->transactionType;
            $item->transactionType->thumbnail = $transactionType->thumbnail ?
                url('transaction-type/' . $transactionType->thumbnail) : '';

            return $item;
        });

        return response()->json($transactions);
    }
}

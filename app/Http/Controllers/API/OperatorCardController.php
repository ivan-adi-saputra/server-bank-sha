<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OperatorCard;
use Illuminate\Http\Request;

class OperatorCardController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('limit') !== null ? $request->query('limit') : 10;

        $operatorCards = OperatorCard::with('dataPlans')
            ->where('status', 'active')
            ->get()
            ->paginate($limit);

        $operatorCards->getCollection()->transform(function ($item) {
            $item->thumbnail = $item->thumbnail ? url($item->thumbnail) : "";
            return $item;
        });

        return response()->json($operatorCards);
    }
}

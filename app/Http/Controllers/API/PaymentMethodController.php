<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $banks = PaymentMethod::where('status', 'active')
            ->whereNotIn('code', ['bwa'])
            ->get()
            ->map(function ($item) {
                $item->thumbnail = $item->thumbnail ? url('storage/' . $item->thumbnail) : "";
                $item->time = '50 Min';
                return $item;
            });

        return response()->json($banks);
    }
}

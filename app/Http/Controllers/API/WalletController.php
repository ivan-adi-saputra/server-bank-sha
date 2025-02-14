<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'previous_pin' => 'required|digits:6',
            'new_pin' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        if (!pinChecker($request->previous_pin)) {
            return response()->json(['message' => 'Your Old Pin is Wrong'], 400);
        }

        $user = Auth::user();

        Wallet::where('user_id', $user->id)
            ->update(['pin' => $request->new_pin]);

        return response()->json(['message' => 'Pin updated']);
    }

    public function show()
    {
        $user = Auth::user();

        $wallet = Wallet::select('pin', 'balance', 'card_number')->where('user_id', $user->id)->first();

        return response()->json($wallet);
    }
}

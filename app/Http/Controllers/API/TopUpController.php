<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TopUpController extends Controller
{
    public function store(Request $request) 
    {
        $data = $request->only('amount', 'pin', 'payment_method_code');

        $validator = Validator::make($data, [
            'amount' => 'required|integer|min:10000',
            'pin' => 'required|digits:6',
            'payment_method_code' => 'required|in:bno_va,bca_va,bri_va'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        $pinChecker = pinChecker($request->pin);

        if (!$pinChecker) {
            return response()->json(['message' => 'Invalid PIN'], 401);
        }

        $transactionType = TransactionType::where('code', 'top_up')->first();
        $paymentMethod = PaymentMethod::where('code', $request->payment_method_code)->first();

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'user_id' => Auth::user()->id,
                'payment_method_id' => $paymentMethod->id,
                'transaction_type_id' => $transactionType->id,
                'amount' => $request->amount,
                'transaction_code' => strtoupper(Str::random(10)),
                'description' => 'Top Up via ' . $paymentMethod->name,
                'status' => 'pending'
            ]);

            $params = $this->buildMidtransParameters([
                'transaction_code' => $transaction->transaction_code,
                'amount' => $request->amount,
                'payment_method' => $paymentMethod->code,
            ]);

            $midtrans = $this->callMidtrans($params);

            DB::commit();
            return response()->json($midtrans);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function callMidtrans(array $params) 
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$isSanitized = (bool) env('MIDTRANS_IS_SANITIZED');
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_IS_3DS');

        $createTransaction = \Midtrans\Snap::createTransaction($params);

        return [
            'redirect_url' => $createTransaction->redirect_url,
            'token' => $createTransaction->token
        ];
    }

    private function buildMidtransParameters(array $params)
    {
        $transactionDetails = [
            'order_id' => $params['transaction_code'],
            'gross_amount' => $params['amount'],
        ];

        $user = Auth::user();
        $splitName = $this->splitName($user->name);
        $customerDetails = [
            'first_name' => $splitName['first_name'],
            'last_name' => $splitName['last_name'],
            'email' => $user->email,
        ];

        $enabledPayment = [
            $params['payment_method'],
        ];

        return [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'enabled_payment' => $enabledPayment
        ];
    }

    private function splitName($fullname) 
    {
        $name = explode(' ', $fullname);

        $lastName = count($name) > 1 ? array_pop($name) : $fullname;
        $firstName = implode(' ', $name);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
    }
}

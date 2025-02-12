<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TransferHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransferHistoryController extends Controller
{
    public function index(Request $request)
    {

        $limit = $request->query('limit') !== null ? $request->query('limit') : 10;

        $sender = Auth::user();

        $transferHistories = TransferHistory::with('receiverUser:id,name,username,verified,profile_picture')
            ->select('receiver_id')
            ->where('sender_id', $sender->id)
            ->groupBy('receiver_id')
            ->paginate($limit);

        $transferHistories->getCollection()->transform(function ($item) {
            $receiverUser = $item->receiverUser;
            $receiverUser->profile_picture =  $receiverUser->profile_picture ?
                url('storage/' . $receiverUser->profile_picture) : "";
            return $receiverUser;
        });

        return response()->json($transferHistories);
    }
}

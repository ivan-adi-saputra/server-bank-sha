<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferHistory extends Model
{
    protected $table = 'transfer_histories';

    protected $guarded = ['id'];

    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }
}

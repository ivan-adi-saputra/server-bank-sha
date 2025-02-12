<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $guarded = ['id'];

    public function transactionType()
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

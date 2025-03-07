<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorCard extends Model
{
    protected $table = 'operator_cards';

    protected $guarded = ['id'];

    public function dataPlans()
    {
        return $this->hasMany(DataPlan::class);
    }
}

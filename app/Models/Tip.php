<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tip extends Model
{
    use SoftDeletes;

    protected $table = 'tips';

    protected $guarded = ['id'];
}

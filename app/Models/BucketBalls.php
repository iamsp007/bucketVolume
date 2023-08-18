<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BucketBalls extends Model
{
    use HasFactory;
    protected $fillable = [
        'bucket_id',
        'ball_id',
        'ball_quantity',
    ];
}

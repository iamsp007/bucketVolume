<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacedBalls extends Model
{
    use HasFactory;
    protected $fillable = [
        'ball_id',
        'total_balls',
        'used_balls',
        'pending_balls',
    ];
    
    public function ballDetails()
    {
        return $this->hasOne(Balls::class, 'id', 'ball_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buckets extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'volume',
        'used_volume',
        'remaining',
    ];
    
    public function usedVolumeDetails()
    {
        return $this->hasMany(BucketBalls::class, 'bucket_id', 'id');
    }
}

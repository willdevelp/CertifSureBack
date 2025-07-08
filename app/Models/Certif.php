<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certif extends Model
{
    protected $fillable = [
        'original_name',
        'reference_number',
        'processed_path',
        'image_path',
        'public_url',
        'image_url',
    ];

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }
}

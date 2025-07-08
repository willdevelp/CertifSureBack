<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use League\CommonMark\Reference\Reference;

class Scan extends Model
{
    protected $fillable = [
        'reference_number',
        'status',
        'certif_name',
        'scanned_at',
    ];

    public function certif()
    {
        return $this->belongsTo(Certif::class, 'certif_id', 'id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotions extends Model
{
    
    protected $fillable = [
        'nom', 'referentiel', 'date_start', 'date_end', 'date_exp',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournaments extends Model
{
    use HasFactory;
    protected $fillable = [
        'tournament_name',
        'tournament_location',
        'venue_address',
        'pincode',
        'state_id',
        'entry_last_date',
        'entry_last_date_with_fine',
        'status',
        'created_at',
        'updated_at',
    ];
}

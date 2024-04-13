<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MState extends Model
{
    use HasFactory;
    protected $table = 'mstate';
    protected $fillable = [
        'countryUID', 'stateName','stateCode','isActive'
    ];
}

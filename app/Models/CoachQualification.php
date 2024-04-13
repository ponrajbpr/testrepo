<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoachQualification extends Model
{

    use HasFactory;
    protected $table = 'coach_qualification';
    protected $fillable = [
        'user_id',
        'qualification',
        'qualification_year',
        'qualification_file',
        'status',
        'created_by',
        'updated_by',
        ];
}

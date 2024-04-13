<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MAssociation extends Model
{
    use HasFactory;

    protected $table = 'massociations';
    protected $fillable = ['id','name','state','statecode','status'];
}

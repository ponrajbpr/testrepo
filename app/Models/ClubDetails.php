<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubDetails extends Model
{
    use HasFactory;
    protected $table = 'club_details';
    protected $fillable = [
        'user_id',
        'acadmey_name',
        'short_code',
        'registered_certificate',
        'pan_no',
        'practice_place',
        'number_of_players',
        'caddress1',
        'caddress2',
        'ccity',
        'cpincode',
        'cstate',
        'mobile',
        'email',
        'club_director',
        'club_mobile',
        'club_email',
        'club_aadhar',
        'photo',
        'aadhar',
        'academey_photo',
        'academey_logo',
    ];

}

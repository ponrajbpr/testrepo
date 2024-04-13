<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use Notifiable,HasFactory;
    protected $table = 'users_details';
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'email',
        'mobile',
        'aadhar',
        'blood_group',
        'father_name',
        'father_mobile',
        'address',
        'address2',
        'pincode',
        'city',
        'district',
        'state',
        'country',
        'association',
        'height',
        'weight',
        'photo',
        'bank_name',
        'bank_branch',
        'bank_account',
        'bank_ifsc',
        'dob_proof',
        'address_proof',
        'created_at',
        'updated_at',
        'caddress',
        'cpincode',
        'pincode',
        'isactive'
    ];
}

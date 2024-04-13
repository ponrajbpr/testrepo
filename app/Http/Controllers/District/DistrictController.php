<?php

namespace App\Http\Controllers\District;

use App\Http\Controllers\Controller;
use App\Models\DistrictDetails;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function dashboard(){
        $user = auth()->user();
        $details = DistrictDetails::where('user_id', $user->id)->first();
        return view('district.dashboard', compact('user', 'details'));

    }
}

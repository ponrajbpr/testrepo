<?php

namespace App\Http\Controllers\Club;

use App\Models\ClubDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClubController extends Controller
{
    public function dashboard(){
        $user = auth()->user();
        $details = ClubDetails::where('user_id', $user->id)->first();
        return view('club.dashboard', compact('user', 'details'));

    }
}

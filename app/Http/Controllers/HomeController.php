<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(auth()->user()->role == 1) {
            return redirect()->route('admin/dashboard');
        }elseif(auth()->user()->role == 2) {
            return redirect()->route('player/dashboard');
        }elseif(auth()->user()->role == 3) {
            return redirect()->route('coach/dashboard');
        }elseif(auth()->user()->role == 4) {
            return redirect()->route('state/dashboard');
        }elseif(auth()->user()->role == 7) {
            return redirect()->route('club/dashboard');
        }elseif(auth()->user()->role == 8) {
            return redirect()->route('district/dashboard');
        }else{
            return redirect()->route('login');
        }
    }
}

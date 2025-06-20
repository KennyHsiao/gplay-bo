<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TonWalletController extends Controller
{
    //
    public function home() {
        return view('front.ton.home');
    }
}

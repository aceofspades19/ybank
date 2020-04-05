<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
*/

Route::get('accounts/{id}', function ($id) {
    if($id < 1) return array("error"=>"Error: Account does not exist");
    $account = DB::table('accounts')
             ->whereRaw("id=$id")
             ->get();
    if(count($account) == 0)  return array("error"=>"Error: Account does not exist");
    return $account;
});

Route::get('accounts/{id}/transactions', function ($id) {
    if($id < 1) return "";
    $account = DB::table('transactions')
             ->whereRaw("`from`=$id OR `to`=$id")
             ->get();

    return $account;
});

Route::post('accounts/{id}/transactions', function (Request $request, $id) {
    if($id < 1) return false;
    $to = $request->input('to');
    $amount = $request->input('amount');
    $details = $request->input('details');
    if($to < 1) return false;
    if($amount < 1) return false;

    $account = DB::table('accounts')
             ->whereRaw("id=$id")
             ->update(['balance' => DB::raw('balance-' . $amount)]);

    $account = DB::table('accounts')
             ->whereRaw("id=$to")
             ->update(['balance' => DB::raw('balance+' . $amount)]);

    DB::table('transactions')->insert(
        [
            'from' => $id,
            'to' => $to,
            'amount' => $amount,
            'details' => $details
        ]
    );
});

Route::get('currencies', function () {
    $account = DB::table('currencies')
              ->get();

    return $account;
});

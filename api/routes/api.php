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
    if(!check_account_exists($id)) return array("error"=>"Error: Account does not exist");
    $account = DB::table('accounts')
             ->whereRaw("id=$id")
             ->get();
    if(count($account) == 0)  return array("error"=>"Error: Account does not exist");
    return $account;
});

Route::get('accounts/{id}/transactions', function ($id) {
    if($id < 1)   return array("error"=>"Error: Account does not exist");
    if(!check_account_exists($id)) return array("error"=>"Error: Account does not exist");
    $account = DB::table('transactions')
             ->whereRaw("`from`=$id OR `to`=$id")
             ->get();
    if(count($account) == 0)  return array("error"=>"Error: Account does not exist");
    return $account;
});

Route::post('accounts/{id}/transactions', function (Request $request, $id) {

    //set variables
    $to = $request->input('to');
    $amount = $request->input('amount');
    $details = $request->input('details');

    //make sure accounts exist before doing transactions
    if($id < 1) return array("error"=>"Error: Account does not exist");
    if($to < 1) return array("error"=>"Error: Account does not exist");
    $id_exists = check_account_exists($id);
    $to_exists = check_account_exists($to);
    if(!$id_exists) return array("error"=>"Error: Account does not exist");
    if(!$to_exists) return array("error"=>"Error: Account does not exist");

    //make sure amount is correct, we don't want any overspending
    if($amount < 1) return array("error"=>"Error: Incorrect amount");
    $balance = check_balance($id);
    if($amount > $balance) return array("error"=>"Error: Amount is more than account balance");

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


function check_balance($id){
    $account = DB::table('accounts')
        ->whereRaw("id=$id")
        ->get()->first();
    return $account->balance;

}

function check_account_exists($id){
    $account = DB::table('accounts')
        ->whereRaw("id=$id")
        ->get();
    if(count($account) == 0)  return false;
    else return true;
}

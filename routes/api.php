<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('vue/{delay}', function ($delay) {

    sleep(intval($delay));
    $client = new Client;
    $res = $client->request('GET', 'https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js');
    return response($res->getBody(), 200)->header('Content-Type', "application/javascript");
});

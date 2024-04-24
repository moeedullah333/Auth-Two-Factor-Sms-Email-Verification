<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::POST('login-user', [AuthController::class, 'login']);
Route::POST('user-register', [AuthController::class, 'register']);
Route::post('login-with-google', [AuthController::class, 'loginwithgoogle']);
route::get('/get-logo',[UserController::class,'logo']);
Route::post('forgot_password', [AuthController::class, 'forgot_password']);
Route::post('otp_verification', [AuthController::class, 'otp_verification']);
Route::post('reset_password', [AuthController::class, 'reset_password']);
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::middleware(['admin'])->group(function () {
    });
    Route::middleware(['user'])->group(function () {

        Route::controller(UserController::class)->group(function () {
            Route::post('device-otp-verification',[AuthController::class,'device_otp_verification']);
            route::post('/user/profile-edit', 'profile_edit');
            route::get('/user/profile-view', 'profile_view');
            
        });
    });
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::any(
    '/login',
    function () {
        return Response()->json(["status" => false, 'msg' => 'Token is Wrong OR Did not Exist!']);
    }
)->name('login');

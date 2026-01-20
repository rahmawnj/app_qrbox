<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\API\CashierController;
use App\Http\Controllers\API\DeviceController as APIDeviceController;
use App\Http\Controllers\API\MemberQrController;
use App\Http\Controllers\API\QrisController;
use App\Http\Controllers\Partner\TopupController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ambil menu/konfigurasi tombol berdasarkan device_code
Route::get('device-menu/{device_code}', [APIDeviceController::class, 'getDeviceMenu'])->name('api.device.menu');

Route::get('topup/member', [TopupController::class, 'fetchMemberByRFID'])->name('topup.member');

// bypass
Route::post('devices/{device}/update-status', [APIDeviceController::class, 'toggleStatus'])->name('api.devices.update-device');
Route::get('check-device', [APIDeviceController::class, 'checkDeviceStatus']);

// member payment
Route::post('qr-member-request', [MemberQrController::class, 'qr_request']);

// qris
Route::post('qr-request', [QrisController::class, 'qr_request']);
Route::get('payment-check', [QrisController::class, 'checkPaymentStatus']);
Route::get('payment-check-2', [QrisController::class, 'checkPaymentStatus2']);
Route::post('payment-status-update',  [QrisController::class, 'updateTransactionStatus'])->name('xendit.payment-callback');

Route::get('device-price/{device}/{serviceType}', [CashierController::class, 'getPrice'])->name('api.device.price');

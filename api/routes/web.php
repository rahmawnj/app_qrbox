<?php

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\ExportController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\OwnerController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\MemberController;

use App\Http\Controllers\Admin\OutletController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CashierController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Partner\TopupController;
use App\Http\Controllers\CustomerServiceController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Partner\WithdrawalController;
use App\Http\Controllers\Partner\ReceiptConfigController;
use App\Http\Controllers\Partner\CashierPaymentController;
use App\Http\Controllers\Partner\DashboardOwnerController;
use App\Http\Controllers\Partner\PartnerCashierController;
use App\Http\Controllers\Partner\PartnerPaymentController;
use App\Http\Controllers\Landing\MemberDashboardController;
use App\Http\Controllers\Member\MemberTransactionController;
use App\Http\Controllers\Member\MemberNotificationController;
use App\Http\Controllers\Admin\TopupController as AdminTopupController;
use App\Http\Controllers\Landing\HomeController as LandingHomeController;
use App\Http\Controllers\Partner\AddonController as PartnerAddonController;
use App\Http\Controllers\Partner\BrandController as PartnerBrandController;
use App\Http\Controllers\Partner\DeviceController as PartnerDeviceController;
use App\Http\Controllers\Partner\MemberController as PartnerMemberController;
use App\Http\Controllers\Partner\OutletController as PartnerOutletController;
use App\Http\Controllers\Admin\BypassLogController as AdminBypassLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Partner\BypassLogController as PartnerBypassLogController;
use App\Http\Controllers\Partner\TransactionController as PartnerTransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


 Route::get('/api-docs', function () {
            return view('api_docs');
        });

// Route::get('/home/transaction/{order_id}', [LandingController::class, 'transaction'])->name('home.transaction');


// Auth::routes();
// Route::get('/storage/{filename}', function ($filename) {
//     $path = storage_path('app/public/' . $filename);

//     if (!File::exists($path)) {
//         abort(404);
//     }

//     $file = File::get($path);
//     $type = File::mimeType($path);

//     $response = Response::make($file, 200);
//     $response->header("Content-Type", $type);

//     return $response;
// })->where('filename', '.*');

// Route::get('/', function () {
//     return redirect()->route('dashboard');
// });

// // Route::get('/', [LandingHomeController::class, 'index'])->name('home');
// // Route::get('home/brand/{brand:code}', [LandingHomeController::class, 'brand'])->name('home.brand');
// // Route::get('home/brands', [LandingHomeController::class, 'brands'])->name('home.brands');
// // Route::get('home/outlets', [LandingHomeController::class, 'outlets'])->name('home.outlets');

// Route::get('print', function(){
// return view('print');
// });


// Route::get('cust-service', [CustomerServiceController::class,'index']);

// Route::get('dashboard', function () {

//     if (auth()->guard('admin_config')->check()) {
//         return redirect()->route('admin.dashboard');
//     }

//     if (auth()->guard('web')->check()) {
//         return redirect()->route('partner.dashboard');
//     }

//     return redirect()->route('login');
// })->name('dashboard');

// Route::get('export/admin-transactions', [ExportController::class, 'adminTransactions'])->name('export.admin-transactions');
// Route::get('export/partner-transactions', [ExportController::class, 'partnerTransactions'])->name('export.partner-transactions');



// // Route::middleware(['auth'])->group(function () {
//     Route::get('profile', [ProfileController::class, 'form'])->name('profile.form');
//     Route::patch('profile', [ProfileController::class, 'submit'])->name('profile.submit');
//     Route::patch('notifications/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
//     Route::get('notifications', [NotificationController::class, 'list'])->name('notifications.list');

//     Route::prefix('admin')->name('admin.')->middleware('auth:admin_config')->group(function () {



//         Route::get('dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
//         Route::get('bypass/logs', [AdminBypassLogController::class, 'index'])->name('bypass.logs');
//         Route::get('accounts', [AccountController::class, 'allAccounts'])->name('accounts.all');

//         Route::get('withdrawal/list', [AdminWithdrawalController::class, 'listWithdrawals'])->name('withdrawal.list');
//         Route::get('withdrawal/histories', [AdminWithdrawalController::class, 'histories'])->name('withdrawal.histories');
//         Route::get('topup/histories', [AdminTopupController::class, 'topupHistories'])->name('topup.histories');

//         Route::get('withdrawal/{transaction}', [AdminWithdrawalController::class, 'withdrawal_request'])->name('withdrawal.request');
//         Route::post('withdrawal', [AdminWithdrawalController::class, 'withdrawal_store'])->name('withdrawal.store');

//         Route::resource('service_types', ServiceTypeController::class);
//         Route::resource('users', UserController::class);
//         Route::resource('outlets', OutletController::class);
//         Route::resource('cashiers', CashierController::class);
//         Route::resource('owners', OwnerController::class);
//         Route::resource('devices', DeviceController::class);
//         Route::resource('members', MemberController::class);
//         Route::resource('addons', AddonController::class);
//         Route::resource('services', ServiceController::class);

//         Route::get('setting', [SettingController::class, 'form'])->name('setting.form');
//         Route::patch('setting', [SettingController::class, 'submit'])->name('setting.submit');

//         Route::get('payments/history', [AdminPaymentController::class, 'payments_history'])->name('payments.history');
//         // Route::get('payments/qris', [AdminPaymentController::class, 'qris_history'])->name('qris.history');

//         Route::get('transactions/all', [AdminTransactionController::class, 'index'])->name('transactions.index');
//         Route::get('transactions/self-service/member', [AdminTransactionController::class, 'self_service_member'])->name('transactions.self-service.member');
//         Route::get('transactions/self-service/non-member', [AdminTransactionController::class, 'self_service_non_member'])->name('transactions.self-service.non-member');
//         Route::get('transactions/drop-off/member', [AdminTransactionController::class, 'drop_off_member'])->name('transactions.drop-off.member');
//         Route::get('transactions/drop-off/non-member', [AdminTransactionController::class, 'drop_off_non_member'])->name('transactions.drop-off.non-member');
//         Route::delete('transactions/{transaction}', [AdminTransactionController::class, 'destroy'])->name('transactions.destroy');
//     });

//     Route::prefix('partner')->name('partner.')->middleware('auth:web')->group(function () {
//         Route::get('device/list', [DashboardOwnerController::class, 'device_list'])->name('device.list');
//         Route::get('device/{device}', [DashboardOwnerController::class, 'device_edit'])->name('device.edit');
//         Route::patch('device/{device}', [DashboardOwnerController::class, 'device_update'])->name('device.update');
//         Route::post('devices', [DashboardOwnerController::class, 'storeDevice'])->name('device.store');
//         // Route::patch('devices/{device}', [DashboardOwnerController::class, 'updateDeviceDetails'])->name('device.update');
//         Route::patch('devices/{device}/status', [DashboardOwnerController::class, 'updateDeviceStatus'])->name('device.update_status');
//         Route::patch('devices/{device}/service-types', [DashboardOwnerController::class, 'updateDeviceServicePrices'])->name('device.service_types.update');
//         Route::delete('devices/{device}', [DashboardOwnerController::class, 'destroyDevice'])->name('device.destroy');

//         Route::get('outlets/list', [PartnerOutletController::class, 'list'])->name('outlets.list');
//         Route::get('outlets/{outlet}/detail', [PartnerOutletController::class, 'detail'])->name('outlets.detail');
//         Route::post('outlets', [PartnerOutletController::class, 'store'])->name('outlets.store'); // Rute BARU untuk menyimpan outlet
//         Route::patch('outlets/{outlet}/service-list', [PartnerOutletController::class, 'serviceType'])->name('outlets.services.update');
//         Route::put('outlets/{outlet}', [PartnerOutletController::class, 'update'])->name('outlets.update');
//         Route::patch('outlets/{outlet}', [PartnerOutletController::class, 'update'])->name('outlets.patch_update'); // Often good to have both PUT and PATCH for updates
//         Route::delete('outlets/{outlet}', [PartnerOutletController::class, 'destroy'])->name('outlets.destroy');
//         Route::patch('outlets/{outlet}/update-status', [PartnerOutletController::class, 'updateStatus'])->name('outlets.update-status');

//         // Route::get('addons', [PartnerAddonController::class, 'index'])->name('addons.index');
//         // Route::get('addons/create', [PartnerAddonController::class, 'create'])->name('addons.create');
//         // Route::post('addons', [PartnerAddonController::class, 'store'])->name('addons.store');
//         // Route::get('addons/{addon}/edit', [PartnerAddonController::class, 'edit'])->name('addons.edit');
//         // Route::patch('addons/{addon}', [PartnerAddonController::class, 'update'])->name('addons.update');
//         // Route::delete('addons/{addon}', [PartnerAddonController::class, 'destroy'])->name('addons.destroy');

//         // Route::get('cashier-payment', [CashierPaymentController::class, 'create'])->name('cashier.payment.create');
//         // Route::post('cashier-payment', [CashierPaymentController::class, 'store'])->name('cashier.payment.store');

//         Route::get('service-order', [PartnerDeviceController::class, 'serviceOrder'])->name('service-orders.list');
//         Route::get('service-order/{id}', [PartnerDeviceController::class, 'serviceOrderDetail'])->name('service-orders.detail');
//         Route::post('service-orders/activate-device/{deviceTransaction}', [PartnerDeviceController::class, 'activateDeviceService'])->name('service-orders.activate-device');
//         Route::post('service-orders/update-progress/{dropOffTransaction}', [PartnerDeviceController::class, 'updateServiceProgress'])->name('service-orders.update-progress');

//         Route::get('brand/profile', [PartnerBrandController::class, 'form'])->name('brand.profile.edit');
//         Route::put('brand/profile', [PartnerBrandController::class, 'submit'])->name('brand.profile.update');

//         // Route::get('dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

//         Route::get('withdrawal', [WithdrawalController::class, 'withdrawal_request'])->name('withdrawal.request');
//         Route::post('withdrawal', [WithdrawalController::class, 'withdrawal_store'])->name('withdrawal.store');
//         Route::get('withdrawal/histories', [AdminWithdrawalController::class, 'histories'])->name('withdrawal.histories');

//         // Route::get('members/verified', [PartnerMemberController::class, 'verified'])->name('members.verified');
//         // Route::get('members/unverified', [PartnerMemberController::class, 'unverified'])->name('members.unverified');
//         // Route::post('members/{member}/verify', [PartnerMemberController::class, 'verify'])->name('members.verify');
//         // Route::delete('members/{member}/subscription', [PartnerMemberController::class, 'destroySubscription'])->name('members.subscription.destroy');

//         // Route::get('topup', [TopupController::class, 'showTopupForm'])->name('topup');
//         // Route::get('topup/histories', [TopupController::class, 'topupHistories'])->name('topup.histories');
//         // Route::post('topup', [TopupController::class, 'processTopup'])->name('topup.store');

//         Route::get('bypass/logs', [AdminBypassLogController::class, 'index'])->name('bypass.logs');

//         Route::get('transactions/all', [AdminTransactionController::class, 'index'])->name('transactions.index');
//         Route::get('transactions/self-service/member', [PartnerTransactionController::class, 'self_service_member'])->name('transactions.self-service.member');
//         Route::get('transactions/self-service/non-member', [PartnerTransactionController::class, 'self_service_non_member'])->name('transactions.self-service.non-member');
//         Route::get('transactions/drop-off/member', [PartnerTransactionController::class, 'drop_off_member'])->name('transactions.drop-off.member');
//         Route::get('transactions/drop-off/non-member', [PartnerTransactionController::class, 'drop_off_non_member'])->name('transactions.drop-off.non-member');
//         Route::get('dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

//         Route::get('payments/history', [AdminPaymentController::class, 'payments_history'])->name('payments.history');
//         // Route::get('payments/qris', [PartnerPaymentController::class, 'qris_history'])->name('qris.history');

//         // Route::get('cashiers', [PartnerCashierController::class, 'index'])->name('cashiers.list');
//         // Route::post('cashiers', [PartnerCashierController::class, 'store'])->name('cashiers.store');
//         // Route::patch('cashiers/{cashier}', [PartnerCashierController::class, 'update'])->name('cashiers.update');
//         // Route::delete('cashiers/{cashier}', [PartnerCashierController::class, 'destroy'])->name('cashiers.destroy');

//         Route::get('receipt-config', [ReceiptConfigController::class, 'edit'])->name('receipt.config.edit');
//         Route::put('receipt-config', [ReceiptConfigController::class, 'update'])->name('receipt.config.update');
//     });


// // });

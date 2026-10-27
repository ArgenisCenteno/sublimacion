<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BankDepositController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CashShiftController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImpersonateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReconciliationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Rutas publicas
Route::inertia('/', 'Landing')->name('home');
Route::inertia('signin', 'Auth/Signin')->name('signin');
Route::inertia('404', 'Errors/FourZeroFour')->name('404');
Route::inertia('privacidad', 'Legal/Privacidad')->name('legal.privacidad');
Route::inertia('cookies', 'Legal/Cookies')->name('legal.cookies');
Route::inertia('terminos', 'Legal/Terminos')->name('legal.terminos');

Route::get('catalogo', [CatalogController::class, 'index'])->name('catalogo.index');
Route::get('catalogo/{product:slug}', [CatalogController::class, 'show'])->name('catalogo.show');

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::post('webhooks/paypal', [PayPalController::class, 'webhook'])->name('paypal.webhook');

// Rutas protegidas
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/images', [ProductController::class, 'storeImage'])->name('products.images.store');
    Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');

    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart/items', [CartController::class, 'store'])->name('cart.items.store');
    Route::patch('cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
    Route::delete('cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

    Route::get('checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('pedidos/{order}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::post('pedidos/{order}/paypal/create', [PayPalController::class, 'createOrder'])->name('paypal.create');
    Route::post('pedidos/{order}/paypal/capture', [PayPalController::class, 'captureOrder'])->name('paypal.capture');

    Route::get('orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::get('orders/{order}', [OrderController::class, 'adminShow'])->name('admin.orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    Route::resource('bank-deposits', BankDepositController::class)->except(['show']);

    Route::get('receipts', [ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('receipts/{receipt}/pdf', [ReceiptController::class, 'pdf'])->name('receipts.pdf');
    Route::post('receipts/{receipt}/void', [ReceiptController::class, 'void'])->name('receipts.void');

    Route::get('conciliacion', [ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::post('conciliacion/{payment}/link', [ReconciliationController::class, 'link'])->name('reconciliation.link');

    Route::get('settings/exchange-rate', [SettingController::class, 'edit'])->name('settings.exchange-rate.edit');
    Route::put('settings/exchange-rate', [SettingController::class, 'update'])->name('settings.exchange-rate.update');

    Route::get('settings/tienda', [SettingController::class, 'store'])->name('settings.store.edit');
    Route::post('settings/tienda', [SettingController::class, 'updateStore'])->name('settings.store.update');

    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('pos/customers/search', [PosController::class, 'searchCustomers'])->name('pos.customers.search');
    Route::post('pos/shifts', [PosController::class, 'storeShift'])->name('pos.shifts.store');
    Route::post('pos/shifts/{shift}/close', [PosController::class, 'closeShift'])->name('pos.shifts.close');
    Route::post('pos/sale', [PosController::class, 'storeSale'])->name('pos.sale');

    Route::get('cash-shifts', [CashShiftController::class, 'index'])->name('cash-shifts.index');
    Route::get('cash-shifts/{shift}', [CashShiftController::class, 'show'])->name('cash-shifts.show');
    Route::post('cash-shifts/{shift}/reopen', [CashShiftController::class, 'reopen'])->name('cash-shifts.reopen');

    Route::get('reportes', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('reportes/ventas', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reportes/financieros', [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('reportes/clientes', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('reportes/inventario', [ReportController::class, 'inventory'])->name('reports.inventory');
    Route::get('reportes/envios', [ReportController::class, 'shipping'])->name('reports.shipping');

    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    Route::post('impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
    Route::post('impersonate/{user}', [ImpersonateController::class, 'take'])->name('impersonate.take');

    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('profile', [UserController::class, 'profile'])->name('users.profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('users.profile.update');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('users.profile.update-password');
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [UserController::class, 'destroyAvatar'])->name('profile.avatar.destroy');

    // Others
    Route::inertia('calendar', 'Others/Calendar')->name('calendar');
    Route::inertia('user-profile', 'Others/UserProfile')->name('user-profile');

    // Forms
    Route::inertia('form-elements', 'Forms/FormElements')->name('form-elements');

    // Tables
    Route::inertia('basic-tables', 'Tables/BasicTables')->name('basic-tables');

    // Charts
    Route::inertia('bar-chart', 'Chart/BarChart/BarChart')->name('bar-chart');
    Route::inertia('line-chart', 'Chart/LineChart/LineChart')->name('line-chart');

    // UI Elements
    Route::inertia('avatars', 'UiElements/Avatars')->name('avatars');
    Route::inertia('badges', 'UiElements/Badges')->name('badges');
    Route::inertia('buttons', 'UiElements/Buttons')->name('buttons');
    Route::inertia('ui-images', 'UiElements/Images')->name('ui-images');
    Route::inertia('videos', 'UiElements/Videos')->name('videos');

    // Pages
    Route::inertia('blank-page', 'Pages/BlankPage')->name('blank-page');
});

require __DIR__ . '/settings.php';

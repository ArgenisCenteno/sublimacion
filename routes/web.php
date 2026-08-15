<?php

use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\MuestraController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\OperationsAccessController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrdenTrabajoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ResultadoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TasaCambioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkshopOrderController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rutas publicas
Route::inertia('/', 'Landing')->name('home');
Route::inertia('signin', 'Auth/Signin')->name('signin');
Route::inertia('404', 'Errors/FourZeroFour')->name('404');
Route::inertia('privacidad', 'Legal/Privacidad')->name('legal.privacidad');
Route::inertia('cookies', 'Legal/Cookies')->name('legal.cookies');
Route::inertia('terminos', 'Legal/Terminos')->name('legal.terminos');
Route::prefix('operaciones')->controller(OperationsAccessController::class)->group(function () {
    Route::get('/', 'index')->name('ops.index');
    Route::post('access', 'authenticate')->name('ops.authenticate');

    Route::get('chapa', 'chapa')->name('ops.chapa');
    Route::post('chapa/analyze-plate', 'analyzeChapaPlate')->name('ops.chapa.analyze-plate');
    Route::post('chapa/confirm-reception', 'confirmChapaReception')->name('ops.chapa.confirm-reception');
    Route::post('chapa/report-mapped-damage', 'reportChapaMappedDamage')->name('ops.chapa.report-mapped-damage');
    Route::post('chapa/report-incident', 'reportChapaIncident')->name('ops.chapa.report-incident');
    Route::post('chapa/finish-repair', 'finishChapaRepair')->name('ops.chapa.finish-repair');

    Route::get('dab', 'dab')->name('ops.dab');
    Route::post('dab/analyze-plate', 'analyzeDabPlate')->name('ops.dab.analyze-plate');
    Route::post('dab/confirm-reception', 'confirmDabReception')->name('ops.dab.confirm-reception');
    Route::post('dab/report-mapped-damage', 'reportDabMappedDamage')->name('ops.dab.report-mapped-damage');
    Route::post('dab/checklist-item', 'updateDabChecklistItem')->name('ops.dab.checklist-item');
    Route::post('dab/finish-review', 'finishDabReview')->name('ops.dab.finish-review');

    Route::get('limpieza', 'limpieza')->name('ops.limpieza');
    Route::post('limpieza/analyze-plate', 'analyzeLimpiezaPlate')->name('ops.limpieza.analyze-plate');
    Route::post('limpieza/confirm-reception', 'confirmLimpiezaReception')->name('ops.limpieza.confirm-reception');
    Route::post('limpieza/report-mapped-damage', 'reportLimpiezaMappedDamage')->name('ops.limpieza.report-mapped-damage');
    Route::post('limpieza/finish-cleaning', 'finishLimpiezaCleaning')->name('ops.limpieza.finish-cleaning');

    Route::get('recepcion', 'recepcion')->name('ops.recepcion');
    Route::post('recepcion/analyze-plate', 'analyzeRecepcionPlate')->name('ops.recepcion.analyze-plate');
    Route::post('recepcion/create-vehicle', 'createRecepcionVehicle')->name('ops.recepcion.create-vehicle');
    Route::post('recepcion/report-mapped-damage', 'reportRecepcionMappedDamage')->name('ops.recepcion.report-mapped-damage');
    Route::post('recepcion/release-vehicle', 'releaseRecepcionVehicle')->name('ops.recepcion.release-vehicle');
});

Route::prefix('recepcion/ordenes/{orden}')->name('recepcion.ordenes.')->group(function () {
    Route::get('cobrar', [PagoController::class, 'create'])->name('cobrar');
    Route::post('pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::delete('pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');
    Route::post('pagos/{pago}/anular', [PagoController::class, 'anular'])->name('pagos.anular');
});
Route::get('recepcion/pagos', [PagoController::class, 'index'])->name('recepcion.pagos.index');
Route::get('recepcion/pagos/{id}', [PagoController::class, 'show'])->name('recepcion.pagos.show');

Route::put('configuracion/tasas/{divisa}', [TasaCambioController::class, 'update'])
    ->name('tasas.update');

Route::prefix('recepcion')->controller(OperationsAccessController::class)->group(function () {
    Route::resource('pacientes', PacienteController::class);

    Route::resource('ordenes', OrdenTrabajoController::class);
    Route::patch('ordenes/{id}/entregar', [OrdenTrabajoController::class, 'marcarEntregado']) ->name('ordenes.marcar-entregado');


})->middleware('auth');
Route::prefix('catalogo')->controller(OperationsAccessController::class)->group(function () {
    Route::resource('examenes', CatalogoController::class);
    Route::patch('examenes/{examen}/toggle-activo', [CatalogoController::class, 'toggleActivo'])
        ->name('examenes.toggle-activo');
    Route::resource('medicos', MedicoController::class);
    Route::patch('medicos/{medico}/toggle-activo', [MedicoController::class, 'toggleActivo'])
        ->name('medicos.toggle-activo');
    Route::get('medicos/buscar', [MedicoController::class, 'buscar'])
        ->name('medicos.buscar');

    Route::resource('perfiles', PerfilController::class);

})->middleware('auth');
// Rutas protegidas
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
Route::get('resultados/{orden}/pdf', [PdfController::class, 'resultados'])->name('resultados.pdf');
Route::get('ordenes/{orden}/pdf',    [PdfController::class, 'orden'])->name('ordenes.pdf');
    Route::get('muestras', [MuestraController::class, 'index'])
        ->name('muestras.index');
    Route::get('muestras/{orden}', [MuestraController::class, 'show'])
        ->name('muestras.show');
    Route::post('muestras/{orden}/generar', [MuestraController::class, 'generar'])
        ->name('muestras.generar');
    Route::patch('muestras/{orden}/{muestra}/registrar-toma', [MuestraController::class, 'registrarToma'])
        ->name('muestras.registrar-toma');
    Route::patch('muestras/{orden}/{muestra}/rechazar', [MuestraController::class, 'rechazar'])
        ->name('muestras.rechazar');
    Route::patch('muestras/{orden}/tomar-todas', [MuestraController::class, 'tomarTodas'])
        ->name('muestras.tomar-todas');
    Route::get('resultados', [ResultadoController::class, 'index'])
        ->name('resultados.index');
    Route::get('resultados/{orden}', [ResultadoController::class, 'show'])
        ->name('resultados.show');
    Route::patch('resultados/{orden}/items/{item}/cargar', [ResultadoController::class, 'cargarResultado'])
        ->name('resultados.cargar');
    Route::post('resultados/{orden}/cargar-todos', [ResultadoController::class, 'cargarTodos'])
        ->name('resultados.cargar-todos');
    Route::post('resultados/{orden}/validar', [ResultadoController::class, 'validar'])
        ->name('resultados.validar');
    Route::patch('resultados/{orden}/items/{item}/descartar', [ResultadoController::class, 'descartarResultado'])
        ->name('resultados.descartar');



    Route::get('dashboard/drilldown', [DashboardController::class, 'drilldown'])->name('dashboard.drilldown');

    // Flota
    Route::post('vehicles/import', [VehicleController::class, 'import'])->name('vehicles.import');
    Route::post('vehicles/{vehicle}/documents', [VehicleController::class, 'storeDocument'])->name('vehicles.documents.store');
    Route::get('vehicles/{vehicle}/documents/{document}/download', [VehicleController::class, 'downloadDocument'])->name('vehicles.documents.download');
    Route::resource('vehicles', VehicleController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('providers', ProviderController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('users', UserController::class);
    Route::get('profile', [UserController::class, 'profile'])->name('users.profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('users.profile.update');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('users.profile.update-password');
    Route::post('/profile/avatar', [UserController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [UserController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::resource('invoices', InvoiceController::class);
    Route::get('/export-invoices', [ExportController::class, 'exportCsv'])->name('invoices.export');
    Route::get('/export-zip-invoices', [ExportController::class, 'exportZip'])->name('invoices.export.zip');
    // Taller
    Route::get('workshop', fn() => Inertia::render('PlaceholderPage', ['title' => 'Taller']))->name('workshop.index');
    Route::resource('workshop/orders', WorkshopOrderController::class)->names('workshop.orders');
    Route::get('workshop/checklist', fn() => Inertia::render('PlaceholderPage', ['title' => 'Checklist DAB']))->name('workshop.checklist');

    // Clientes y ventas
    Route::resource('clients', ClientController::class);
    Route::get('sales', fn() => Inertia::render('PlaceholderPage', ['title' => 'Contratos de venta']))->name('sales.index');
    Route::get('renting', fn() => Inertia::render('PlaceholderPage', ['title' => 'Contratos de renting']))->name('renting.index');

    // Finanzas
    //Route::get('invoices', fn() => Inertia::render('PlaceholderPage', ['title' => 'Facturas proveedor']))->name('invoices.index');
    Route::get('costs', fn() => Inertia::render('PlaceholderPage', ['title' => 'Costes']))->name('costs.index');
    Route::get('recurring-invoices', fn() => Inertia::render('PlaceholderPage', ['title' => 'Facturacion recurrente']))->name('recurring-invoices.index');

    // Documentacion
    Route::get('documents', fn() => Inertia::render('PlaceholderPage', ['title' => 'Documentos vehiculo']))->name('documents.index');
    Route::get('insurance', fn() => Inertia::render('PlaceholderPage', ['title' => 'Polizas de seguro']))->name('insurance.index');

    // Administración
    // Route::get('users', fn() => Inertia::render('PlaceholderPage', ['title' => 'Usuarios']))->name('users.index');

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
    Route::get('alerts', [AlertController::class, 'index'])->name('alerts');
    Route::post('alerts/{alert}/action', [AlertController::class, 'runAction'])->name('alerts.action');
    Route::post('alerts/{alert}/resolve', [AlertController::class, 'resolve'])->name('alerts.resolve');
    Route::post('alerts/{alert}/dismiss', [AlertController::class, 'dismiss'])->name('alerts.dismiss');
    Route::inertia('avatars', 'UiElements/Avatars')->name('avatars');
    Route::inertia('badges', 'UiElements/Badges')->name('badges');
    Route::inertia('buttons', 'UiElements/Buttons')->name('buttons');
    Route::inertia('ui-images', 'UiElements/Images')->name('ui-images');
    Route::inertia('videos', 'UiElements/Videos')->name('videos');

    // Pages
    Route::inertia('blank-page', 'Pages/BlankPage')->name('blank-page');
});

require __DIR__ . '/settings.php';

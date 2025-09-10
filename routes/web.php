<?php

use App\Http\Controllers\Admin\AssetAuditController;
use App\Http\Controllers\Admin\AssetRegisterController;
use App\Http\Controllers\Admin\AuditorController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;



// Public auth pages
Route::get('/login',  [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login.post');
Route::post('/logout',[UserController::class, 'logout'])->name('logout');

// ADMIN AREA (web guard)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web','auth:web'])
    ->group(function () {
        Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
        
        Route::get ('/auditors',                [AuditorController::class, 'index'])->name('auditor.index');
        Route::get ('/auditors/create',         [AuditorController::class, 'create'])->name('auditor.create');
        Route::post('/auditors',                [AuditorController::class, 'store'])->name('auditor.store');
        Route::get ('/auditors/{auditor}/edit', [AuditorController::class, 'edit'])->name('auditor.edit');
        Route::put ('/auditors/{auditor}',      [AuditorController::class, 'update'])->name('auditor.update');
        Route::delete('/auditors/{auditor}',    [AuditorController::class, 'destroy'])->name('auditor.destroy');

        Route::get ('/clients',                 [ClientController::class, 'index'])->name('client.index');
        Route::get ('/clients/create',          [ClientController::class, 'create'])->name('client.create');
        Route::post('/clients',                 [ClientController::class, 'store'])->name('client.store');
        Route::get ('/clients/{client}/edit',   [ClientController::class, 'edit'])->name('client.edit');
        Route::put ('/clients/{client}',        [ClientController::class, 'update'])->name('client.update');
        Route::delete('/clients/{client}',      [ClientController::class, 'destroy'])->name('client.destroy');

        Route::get   ('/register/{client}', [AssetRegisterController::class, 'index'])->name('register.index');
        Route::post  ('/clients/{client}/register', [AssetRegisterController::class, 'store'])->name('register.store');
        Route::delete('/clients/{client}/register/{register}', [AssetRegisterController::class, 'destroy'])->name('register.destroy');

        Route::get   ('/clients/{client}/audit/schema',                         [AssetAuditController::class,'schema'])->name('audit.schema');
        Route::post  ('/clients/{client}/audit/schema/field',                   [AssetAuditController::class,'fieldStore'])->name('audit.field.store');
        Route::delete('/clients/{client}/audit/schema/field/{field}',           [AssetAuditController::class,'fieldDestroy'])->name('audit.field.destroy');
        Route::post  ('/clients/{client}/audit/schema/{field}/option',          [AssetAuditController::class,'optionStore'])->name('audit.option.store');
        Route::delete('/clients/{client}/audit/schema/{field}/option/{option}', [AssetAuditController::class,'optionDestroy'])->name('audit.option.destroy');
        Route::get   ('/clients/{client}/audit/uploads',                        [AssetAuditController::class,'uploads'])->name('audit.uploads');
        Route::post  ('/clients/{client}/audit/upload',                         [AssetAuditController::class,'uploadStore'])->name('audit.upload.store');
        Route::delete('/clients/{client}/audit/upload/{file}',                  [AssetAuditController::class,'uploadDestroy'])->name('audit.upload.destroy');

        // If these are admin-only capture routes, keep them here; otherwise move to auditor group below.
        Route::get ('/auditor/{client}/capture',    [AssetAuditController::class,'capture'])->name('audit.capture');
        Route::post('/auditor/{client}/save',       [AssetAuditController::class,'saveRow'])->name('audit.saveRow');
        Route::get ('/auditor/{client}/my-rows',    [AssetAuditController::class,'myRows'])->name('audit.myRows');
        Route::post('/auditor/{client}/finish-day', [AssetAuditController::class,'finishDay'])->name('audit.finishDay');
    });

// AUDITOR AREA (auditor guard)
Route::prefix('auditor')
    ->name('auditor.')
    ->middleware(['web','auth:auditor'])
    ->group(function () {
        Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');
        Route::get('/logout',[UserController::class, 'logout'])->name('logout');
        // If auditors should manage/view clients:
        Route::get ('/clients',               [ClientController::class, 'index'])->name('client.index');
        Route::get ('/clients/create',        [ClientController::class, 'create'])->name('client.create');
        Route::post('/clients',               [ClientController::class, 'store'])->name('client.store');
        Route::get ('/clients/{client}/edit', [ClientController::class, 'edit'])->name('client.edit');
        Route::put ('/clients/{client}',      [ClientController::class, 'update'])->name('client.update');
        Route::delete('/clients/{client}',    [ClientController::class, 'destroy'])->name('client.destroy');

        Route::get   ('/register/{client}',                             [AssetRegisterController::class, 'index'])->name('register.index');
        Route::post  ('/clients/{client}/register',                     [AssetRegisterController::class, 'store'])->name('register.store');
        Route::delete('/clients/{client}/register/{register}',          [AssetRegisterController::class, 'destroy'])->name('register.destroy');

        Route::get   ('/clients/{client}/audit/schema',                         [AssetAuditController::class,'schema'])->name('audit.schema');
        Route::post  ('/clients/{client}/audit/schema/field',                   [AssetAuditController::class,'fieldStore'])->name('audit.field.store');
        Route::delete('/clients/{client}/audit/schema/field/{field}',           [AssetAuditController::class,'fieldDestroy'])->name('audit.field.destroy');
        Route::post  ('/clients/{client}/audit/schema/{field}/option',          [AssetAuditController::class,'optionStore'])->name('audit.option.store');
        Route::delete('/clients/{client}/audit/schema/{field}/option/{option}', [AssetAuditController::class,'optionDestroy'])->name('audit.option.destroy');
        Route::get   ('/clients/{client}/audit/uploads',                        [AssetAuditController::class,'uploads'])->name('audit.uploads');
        Route::post  ('/clients/{client}/audit/upload',                         [AssetAuditController::class,'uploadStore'])->name('audit.upload.store');
        Route::delete('/clients/{client}/audit/upload/{file}',                  [AssetAuditController::class,'uploadDestroy'])->name('audit.upload.destroy');

        Route::get ('/capture/{client}',     [AssetAuditController::class,'capture'])->name('audit.capture');
        Route::post('/save/{client}',        [AssetAuditController::class,'saveRow'])->name('audit.saveRow');
        Route::get ('/my-rows/{client}',     [AssetAuditController::class,'myRows'])->name('audit.myRows');
        Route::post('/finish-day/{client}',  [AssetAuditController::class,'finishDay'])->name('audit.finishDay');
    });
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Anwar\CrudGenerator\Http\Controllers\Admin\ModuleGeneratorController;

// Admin CRUD Generator UI routes
Route::prefix('admin')
    ->middleware(config('anwarcrud.admin_middleware', ['web', 'auth']))
    ->group(function () {
        // CRUD Generator Management
        Route::get('anwar-crud-generator', [ModuleGeneratorController::class, 'index'])->name('admin.crudgenerator.index');
        Route::post('anwar-crud-generator/run', [ModuleGeneratorController::class, 'runGenerator'])->name('admin.crudgenerator.run');
        Route::post('anwar-crud-generator/preview', [ModuleGeneratorController::class, 'previewGenerator'])->name('admin.crudgenerator.preview');
        Route::delete('anwar-crud-generator/delete/{module}', [ModuleGeneratorController::class, 'deleteModule'])->name('admin.crudgenerator.delete');

        // Form & Table Data (AJAX)
        Route::post('anwar-crud-generator/getColumns', [ModuleGeneratorController::class, 'getCollumns']);
        Route::post('anwar-crud-generator/getFormView', [ModuleGeneratorController::class, 'getFormView']);
        Route::post('anwar-crud-generator/final', [ModuleGeneratorController::class, 'finalSubmit']);

        // Stub Management
        Route::get('anwar-crud-generator/stubs', [ModuleGeneratorController::class, 'listStubs'])->name('admin.crudgenerator.stubs.list');
        Route::post('anwar-crud-generator/stubs/upload', [ModuleGeneratorController::class, 'uploadStub'])->name('admin.crudgenerator.stubs.upload');

        // Documentation Generation
        Route::post('anwar-crud-generator/docs/{module}', [ModuleGeneratorController::class, 'generateDocumentation'])->name('admin.crudgenerator.docs.generate');
        Route::get('anwar-crud-generator/docs/{module}', [ModuleGeneratorController::class, 'viewDocumentation'])->name('admin.crudgenerator.docs.view');
    });

// API routes for CRUD generator (can be used independently)
Route::prefix('api/crud-modules')
    ->middleware(['api'])
    ->group(function () {
        Route::get('/', [ModuleGeneratorController::class, 'apiListModules'])->name('api.crudgenerator.list');
        Route::post('/', [ModuleGeneratorController::class, 'apiGenerateModule'])->name('api.crudgenerator.generate');
        Route::delete('/{module}', [ModuleGeneratorController::class, 'apiDeleteModule'])->name('api.crudgenerator.delete');
    });

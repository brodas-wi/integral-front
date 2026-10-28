<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MapLocationController;
use App\Http\Controllers\ScriptController;
use App\Http\Controllers\ExtraordinaryAssetController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/api/banners/active', [BannerController::class, 'active'])
    ->name('api.banners.active');

Route::get('/api/announcements/for-page', [AnnouncementController::class, 'forPage'])
    ->name('api.announcements.for-page');

Route::get('/api/scripts/active', [ScriptController::class, 'active'])
    ->name('api.scripts.active');

Route::get('/api/assets/active', [ExtraordinaryAssetController::class, 'active'])
    ->name('api.assets.active');

Route::get('/api/map-locations', [MapLocationController::class, 'index'])
    ->name('api.map-locations');

Route::get('/api/geography', [FormController::class, 'geography'])
    ->name('api.geography');

Route::get('/api/forms/{slug}', [FormController::class, 'show'])
    ->name('api.forms.show')
    ->where('slug', '[a-z0-9\-]+');

Route::post('/api/forms/{slug}/submissions', [FormController::class, 'submit'])
    ->name('api.forms.submit')
    ->middleware('throttle:form-submissions')
    ->where('slug', '[a-z0-9\-]+');

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/{slug}/styles.css', [PageController::class, 'styles'])
    ->name('page.styles')
    ->where('slug', '[a-z0-9\-]+');

Route::get('/{slug}/script.js', [PageController::class, 'script'])
    ->name('page.script')
    ->where('slug', '[a-z0-9\-]+');

Route::get('/{slug}', [PageController::class, 'show'])
    ->name('page.show')
    ->where('slug', '[a-z0-9\-]+');

Route::get('/api/search', [SearchController::class, 'index'])
    ->middleware('throttle:30,1')
    ->name('api.search');

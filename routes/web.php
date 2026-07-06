<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MapLocationController;
use App\Http\Controllers\ScriptController;
use App\Http\Controllers\ExtraordinaryAssetController;
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

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GithubWebhookController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::post('/webhook/github/{domain}', [GithubWebhookController::class, 'handle'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// GitHub Auth Routes
Route::get('/auth/github', [\App\Http\Controllers\GitHubAuthController::class, 'redirect'])->name('github.login');
Route::get('/auth/github/callback', [\App\Http\Controllers\GitHubAuthController::class, 'callback']);

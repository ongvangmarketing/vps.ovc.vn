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

// Vibe Deploy Self-Update Route
Route::post('/webhook/self-update', function (\Illuminate\Http\Request $request) {
    if ($request->query('token') !== config('app.self_update_token', env('SELF_UPDATE_TOKEN', 'vibe-secret-token'))) {
        abort(403, 'Unauthorized self-update webhook');
    }
    
    \App\Jobs\SelfUpdateJob::dispatch();
    
    return response()->json(['message' => 'Self-update job queued successfully.']);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

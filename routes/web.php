<?php

use App\Http\Controllers\Webhooks\CaptureWebhookController;
use App\Http\Controllers\Webhooks\WebhookInspectorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebhookInspectorController::class, 'index'])->name('home');

Route::get('/inspector/bin', [WebhookInspectorController::class, 'bin'])->name('inspector.bin');
Route::get('/inspector/{bin:token}/requests', [WebhookInspectorController::class, 'requests'])->name('inspector.requests');
Route::delete('/inspector/{bin:token}/requests', [WebhookInspectorController::class, 'clear'])->name('inspector.requests.clear');

Route::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], '/hook/{token}', CaptureWebhookController::class)
    ->middleware('throttle:webhook-capture')
    ->name('webhooks.capture');

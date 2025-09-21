<?php

use App\Http\Controllers\Api\WhatsAppController;
use Illuminate\Support\Facades\Route;


Route::post('/whatsapp/webhook', [WhatsAppController::class, 'webhook']);
Route::get('/whatsapp/webhook', [WhatsAppController::class, 'webhook']);

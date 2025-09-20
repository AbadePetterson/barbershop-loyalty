<?php

use App\Http\Controllers\api\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ('Hello World!');
});

// Rota para o webhook do WhatsApp
Route::post('/whatsapp/webhook', [WhatsAppController::class, 'webhook']);
Route::get('/whatsapp/webhook', [WhatsAppController::class, 'webhook']); // Para verificação

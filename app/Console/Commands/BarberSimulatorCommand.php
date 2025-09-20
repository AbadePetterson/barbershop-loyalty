<?php

namespace App\Console\Commands;

use App\Services\BarberService;
use Illuminate\Console\Command;

class BarberSimulatorCommand extends Command
{
    protected $signature = 'barber:chat {message?}';
    protected $description = 'Simula chat do WhatsApp para testar sistema do barbeiro';

    public function __construct(
        private BarberService $barberService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔷 SIMULADOR BARBEIRO WHATSAPP 🔷');
        $this->info('Digite "sair" para encerrar ou "menu" para ver opções');
        $this->line('');

        // Se passou mensagem como parâmetro
        if ($this->argument('message')) {
            $response = $this->barberService->processMessage($this->argument('message'));
            $this->info("📱 Resposta: {$response}");
            return;
        }

        // Loop interativo
        while (true) {
            $message = $this->ask('💬 Digite sua mensagem');

            if (strtolower($message) === 'sair') {
                $this->info('👋 Até logo!');
                break;
            }

            $response = $this->barberService->processMessage($message);
            $this->line('');
            $this->info("🤖 Bot: {$response}");
            $this->line('');
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Services\BarberService;
use Illuminate\Console\Command;

class BarberStatsCommand extends Command
{
    protected $signature = 'barber:stats';
    protected $description = 'Mostra estatísticas do sistema do barbeiro';

    public function __construct(
        private BarberService $barberService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('📊 ESTATÍSTICAS DO BARBEIRO 📊');
        $this->line('');

        $data = $this->barberService->getStats();
        $stats = $data['stats'];

        $this->info("👥 Total de clientes: {$stats['total_clients']}");
        $this->info("✂️ Total de cortes: {$stats['total_cuts']}");
        $this->info("🎁 Cortes grátis disponíveis: {$stats['total_free_cuts']}");
        $this->info("📈 Média por cliente: {$stats['average_cuts']} cortes");
        $this->line('');

        // Top clientes
        if ($data['top_clients']->isNotEmpty()) {
            $this->info('🏆 TOP 5 CLIENTES:');
            foreach ($data['top_clients'] as $index => $client) {
                $position = $index + 1;
                $this->line("{$position}° {$client->name} - {$client->cuts_count} cortes");
            }
            $this->line('');
        }

        // Clientes próximos do grátis
        if ($data['almost_free']->isNotEmpty()) {
            $this->info('⚡ PRÓXIMOS DO CORTE GRÁTIS:');
            foreach ($data['almost_free'] as $client) {
                $remaining = $client->cuts_to_free;
                $this->line("{$client->name} - faltam {$remaining} cortes");
            }
        }
    }
}

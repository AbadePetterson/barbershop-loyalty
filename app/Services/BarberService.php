<?php

namespace App\Services;

use App\Repositories\ClientRepository;
use App\Models\Client;
use Illuminate\Support\Facades\Cache; // ← ADICIONAR
use Exception;

class BarberService
{
    public function __construct(
        private ClientRepository $clientRepository
    ) {}

    // TODOS OS SEUS MÉTODOS EXISTENTES PERMANECEM IGUAIS:
    // getMenu(), listClients(), addCut(), useFreeCut(), registerClient(), getClientDetails(), getStats()

    public function getMenu(): string
    {
        return "🔷 *MENU BARBEIRO* 🔷\n\n" .
            "1️⃣ Listar clientes\n" .
            "2️⃣ Adicionar corte (+1)\n" .
            "3️⃣ Usar corte grátis (-1)\n" .
            "4️⃣ Cadastrar cliente\n\n" .
            "📝 *Comandos rápidos:*\n" .
            "• [nome] +1 - adiciona corte\n" .
            "• [nome] -1 - usa corte grátis\n" .
            "• cliente [nome] - ver detalhes\n\n" .
            "Digite o número da opção desejada!";
    }

    public function listClients(): string
    {
        $clients = $this->clientRepository->getAll();

        if ($clients->isEmpty()) {
            return "Nenhum cliente cadastrado ainda.";
        }

        $message = "👥 *LISTA DE CLIENTES* 👥\n\n";

        foreach ($clients as $client) {
            $message .= "📋 *{$client->name}*\n";
            $message .= "   Cortes: {$client->cuts_count}\n";
            $message .= "   Grátis: {$client->free_cuts_earned}\n";
            $message .= "   Progresso: {$client->progress}/10\n";
            $message .= "   Faltam: {$client->cuts_to_free} para próximo grátis\n\n";
        }

        return $message . "Digite 'menu' para voltar ao menu principal.";
    }

    public function addCut(string $clientName): string
    {
        $client = $this->clientRepository->findByName($clientName);

        if (!$client) {
            return "Cliente '{$clientName}' não encontrado.\nDigite '4' para cadastrar novo cliente.";
        }

        $earnedFree = $client->addCut();

        $message = "✅ Corte adicionado para *{$client->name}*!\n\n";
        $message .= "📊 Total de cortes: {$client->cuts_count}\n";
        $message .= "🎁 Cortes grátis: {$client->free_cuts_earned}\n";
        $message .= "📈 Progresso: {$client->progress}/10\n";

        if ($earnedFree) {
            $message .= "\n🎉 *PARABÉNS!* Cliente ganhou um corte grátis!";
        } else {
            $message .= "⏳ Faltam {$client->cuts_to_free} cortes para o próximo grátis";
        }

        return $message;
    }

    public function useFreeCut(string $clientName): string
    {
        $client = $this->clientRepository->findByName($clientName);

        if (!$client) {
            return "Cliente '{$clientName}' não encontrado.";
        }

        if (!$client->useFreecut()) {
            return "❌ Cliente *{$client->name}* não possui cortes grátis disponíveis.\n\nCortes grátis: {$client->free_cuts_earned}";
        }

        $message = "🎁 Corte grátis usado por *{$client->name}*!\n\n";
        $message .= "📊 Total de cortes: {$client->cuts_count}\n";
        $message .= "🎁 Cortes grátis restantes: {$client->free_cuts_earned}\n";
        $message .= "📈 Progresso: {$client->progress}/10";

        return $message;
    }

    public function registerClient(string $name, string $phone): string
    {
        // Verificar se já existe
        $existingClient = $this->clientRepository->findByPhone($phone);
        if ($existingClient) {
            return "❌ Já existe um cliente com este telefone: {$existingClient->name}";
        }

        try {
            $client = $this->clientRepository->create([
                'name' => $name,
                'phone' => $phone,
                'cuts_count' => 0,
                'free_cuts_earned' => 0
            ]);

            return "✅ Cliente *{$client->name}* cadastrado com sucesso!\nTelefone: {$client->phone}";
        } catch (Exception $e) {
            return "❌ Erro ao cadastrar cliente: " . $e->getMessage();
        }
    }

    public function getClientDetails(string $clientName): string
    {
        $client = $this->clientRepository->findByName($clientName);

        if (!$client) {
            return "Cliente '{$clientName}' não encontrado.";
        }

        $message = "👤 *DETALHES DO CLIENTE* 👤\n\n";
        $message .= "📋 *Nome:* {$client->name}\n";
        $message .= "📱 *Telefone:* {$client->phone}\n";
        $message .= "✂️ *Total de cortes:* {$client->cuts_count}\n";
        $message .= "🎁 *Cortes grátis:* {$client->free_cuts_earned}\n";
        $message .= "📊 *Progresso atual:* {$client->progress}/10\n";
        $message .= "⏳ *Faltam:* {$client->cuts_to_free} cortes para próximo grátis\n";
        $message .= "📅 *Cadastrado em:* " . $client->created_at->format('d/m/Y');

        return $message;
    }

    // MODIFICAR APENAS ESTE MÉTODO - adicionar parâmetro $userPhone
    public function processMessage(string $message, string $userPhone = '11999999999'): string
    {
        // ← ADICIONAR: Verificar se está em fluxo step-by-step
        $sessionKey = "barber_session_{$userPhone}";
        $session = Cache::get($sessionKey, ['state' => 'idle', 'data' => []]);

        if ($session['state'] !== 'idle') {
            return $this->handleStepByStep($message, $session, $sessionKey);
        }

        // ← SUA LÓGICA EXISTENTE CONTINUA IGUAL (só mover para método separado)
        return $this->handleNormalMessage($message, $sessionKey);
    }

    // ← NOVO: Sua lógica atual movida para cá
    private function handleNormalMessage(string $message, string $sessionKey): string
    {
        $message = strtolower(trim($message));

        // Menu principal
        if ($message === 'menu' || $message === '/start') {
            return $this->getMenu();
        }

        // Listar clientes
        if ($message === '1' || str_contains($message, 'listar')) {
            return $this->listClients();
        }

        // Adicionar corte
        if (str_contains($message, '+1') || $message === '2') {
            if ($message === '2') {
                return "Digite o nome do cliente para adicionar um corte:\nExemplo: joão +1";
            }
            $clientName = trim(str_replace('+1', '', $message));
            if (empty($clientName)) {
                return "Por favor, informe o nome do cliente.\nExemplo: joão +1";
            }
            return $this->addCut($clientName);
        }

        // Usar corte grátis
        if (str_contains($message, '-1') || $message === '3') {
            if ($message === '3') {
                return "Digite o nome do cliente para usar corte grátis:\nExemplo: joão -1";
            }
            $clientName = trim(str_replace('-1', '', $message));
            if (empty($clientName)) {
                return "Por favor, informe o nome do cliente.\nExemplo: joão -1";
            }
            return $this->useFreeCut($clientName);
        }

        // ← MODIFICAR: Cadastrar cliente - agora inicia step-by-step
        if ($message === '4' || str_contains($message, 'cadastrar')) {
            // Se começar com "cadastrar", tentar parsing primeiro
            if (str_starts_with($message, 'cadastrar ')) {
                return $this->handleCadastrarCommand($message);
            }

            // Senão, iniciar step-by-step
            Cache::put($sessionKey, ['state' => 'waiting_name', 'data' => []], 3600);

            return "📝 *CADASTRAR CLIENTE* - Passo 1/3\n\n" .
                "👤 Digite o **nome completo**:\n\n" .
                "💡 Ou use: cadastrar [nome] [telefone]\n" .
                "❌ Digite 'cancelar' para sair";
        }

        // Ver detalhes de cliente
        if (str_starts_with($message, 'cliente ')) {
            $clientName = trim(str_replace('cliente ', '', $message));
            return $this->getClientDetails($clientName);
        }

        return "Comando não reconhecido! Digite 'menu' para ver as opções disponíveis.";
    }

    // ← NOVO: Manter compatibilidade com formato antigo
    private function handleCadastrarCommand(string $message): string
    {
        $parts = explode(' ', $message);

        if (count($parts) < 3) {
            return "Formato incorreto. Use:\ncadastrar [nome] [telefone]\n\nExemplo: cadastrar João Silva 11999999999";
        }

        array_shift($parts); // Remove "cadastrar"
        $phone = array_pop($parts); // Remove último elemento (telefone)
        $name = implode(' ', $parts); // Junta o resto como nome

        return $this->registerClient($name, $phone);
    }

    // ← NOVO: Gerenciar fluxo step-by-step
    private function handleStepByStep(string $message, array $session, string $sessionKey): string
    {
        return match($session['state']) {
            'waiting_name' => $this->handleWaitingName($message, $sessionKey),
            'waiting_phone' => $this->handleWaitingPhone($message, $session, $sessionKey),
            'confirm_client' => $this->handleConfirmClient($message, $session, $sessionKey),
            default => $this->handleNormalMessage($message, $sessionKey)
        };
    }

    // ← NOVO: Aguardar nome
    private function handleWaitingName(string $message, string $sessionKey): string
    {
        $message = trim($message);

        if (strtolower($message) === 'cancelar') {
            Cache::forget($sessionKey);
            return "❌ Cadastro cancelado.";
        }

        if (strlen($message) < 2) {
            return "⚠️ Nome muito curto!\n\n👤 Digite o nome completo:";
        }

        // Verificar se já existe
        $existingClient = $this->clientRepository->findByName($message);
        if ($existingClient) {
            Cache::forget($sessionKey);
            return "⚠️ Cliente similar já existe!\n\n" .
                "📋 *{$existingClient->name}*\n" .
                "📱 {$existingClient->phone}\n\n" .
                "Digite '4' para tentar outro nome";
        }

        Cache::put($sessionKey, [
            'state' => 'waiting_phone',
            'data' => ['name' => $message]
        ], 3600);

        return "✅ Nome: *{$message}*\n\n" .
            "📝 *Passo 2/3*\n\n" .
            "📱 Digite o **telefone**:\n\n" .
            "🔙 'voltar' para alterar nome\n" .
            "❌ 'cancelar' para sair";
    }

    // ← NOVO: Aguardar telefone
    private function handleWaitingPhone(string $message, array $session, string $sessionKey): string
    {
        $message = trim($message);

        if (strtolower($message) === 'cancelar') {
            Cache::forget($sessionKey);
            return "❌ Cadastro cancelado.";
        }

        if (strtolower($message) === 'voltar') {
            Cache::put($sessionKey, ['state' => 'waiting_name', 'data' => []], 3600);
            return "🔙 Voltando...\n\n👤 Digite o nome completo:";
        }

        $cleanPhone = preg_replace('/\D/', '', $message);

        if (strlen($cleanPhone) < 10) {
            return "⚠️ Telefone inválido!\n\n📱 Digite novamente:";
        }

        // Verificar duplicata
        $existingClient = $this->clientRepository->findByPhone($cleanPhone);
        if ($existingClient) {
            Cache::forget($sessionKey);
            return "⚠️ Telefone já cadastrado!\n\n" .
                "📋 *{$existingClient->name}*\n" .
                "📱 {$existingClient->phone}";
        }

        $data = $session['data'];
        $data['phone'] = $cleanPhone;

        Cache::put($sessionKey, [
            'state' => 'confirm_client',
            'data' => $data
        ], 3600);

        return "✅ Telefone: *{$cleanPhone}*\n\n" .
            "📝 *Passo 3/3 - Confirmação*\n\n" .
            "👤 Nome: *{$data['name']}*\n" .
            "📱 Telefone: *{$cleanPhone}*\n\n" .
            "✅ Digite 'sim' para confirmar\n" .
            "🔙 'voltar' para alterar\n" .
            "❌ 'cancelar' para sair";
    }

    // ← NOVO: Confirmar cadastro
    private function handleConfirmClient(string $message, array $session, string $sessionKey): string
    {
        $message = strtolower(trim($message));

        if ($message === 'cancelar') {
            Cache::forget($sessionKey);
            return "❌ Cadastro cancelado.";
        }

        if ($message === 'voltar') {
            Cache::put($sessionKey, [
                'state' => 'waiting_phone',
                'data' => ['name' => $session['data']['name']]
            ], 3600);
            return "🔙 Voltando...\n\n📱 Digite o telefone:";
        }

        if ($message === 'sim' || $message === 's' || $message === 'confirmar') {
            $data = $session['data'];

            try {
                $client = $this->clientRepository->create([
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'cuts_count' => 0,
                    'free_cuts_earned' => 0
                ]);

                Cache::forget($sessionKey);

                return "🎉 *CLIENTE CADASTRADO!*\n\n" .
                    "📋 *{$client->name}*\n" .
                    "📱 {$client->phone}\n\n" .
                    "🎯 Agora use: `{$client->name} +1` para adicionar corte!";

            } catch (Exception $e) {
                Cache::forget($sessionKey);
                return "❌ Erro ao cadastrar: " . $e->getMessage();
            }
        }

        return "⚠️ Digite 'sim' para confirmar, 'voltar' para alterar ou 'cancelar' para sair";
    }

    public function getStats(): array
    {
        $stats = $this->clientRepository->getStats();
        $topClients = $this->clientRepository->getTopClients();
        $almostFree = $this->clientRepository->getAlmostFreeClients();

        return [
            'stats' => $stats,
            'top_clients' => $topClients,
            'almost_free' => $almostFree
        ];
    }
}

<?php
session_start();
require 'db.php';

// 1. DEFINIR A DATA SELECIONADA E O ID DO LAVA JATO
$data_selecionada = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$wash_id = isset($_GET['wash_id']) ? (int)$_GET['wash_id'] : 0; // Pega a loja da URL
$tipo_pre_selecionado = isset($_GET['type']) ? $_GET['type'] : '';

// 2. BUSCAR OS CARROS DO USUÁRIO LOGADO
$usuario_id = $_SESSION['user_id'] ?? 0;
$meus_carros = [];

if ($usuario_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT id, brand, model, plate FROM user_cars WHERE user_id = ?");
        $stmt->execute([$usuario_id]);
        $meus_carros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

// 3. BUSCAR OS PREÇOS ESPECÍFICOS DESTA LOJA ($wash_id)
$preco_simples = 0.00;
$preco_completa = 0.00;
$nome_loja = "Lava Jato Desconhecido";

if ($wash_id > 0) {
    try {
        $stmt_precos = $pdo->prepare("SELECT name, price_simple, price_complete FROM car_washes WHERE id = ?");
        $stmt_precos->execute([$wash_id]);
        $dados_precos = $stmt_precos->fetch(PDO::FETCH_ASSOC);
        
        if ($dados_precos) {
            $nome_loja = $dados_precos['name'];
            $preco_simples = $dados_precos['price_simple'];
            $preco_completa = $dados_precos['price_complete'];
        }
    } catch (Exception $e) {}
}

// 4. BUSCAR OS HORÁRIOS QUE JÁ ESTÃO OCUPADOS NESTA LOJA ESPECÍFICA
$horarios_bloqueados = [];
try {
    $stmt = $pdo->prepare("SELECT appointment_date FROM appointments WHERE appointment_date LIKE ? AND car_wash_id = ? AND status != 'Cancelado'");
    $stmt->execute([$data_selecionada . " %", $wash_id]);
    $agendados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($agendados as $linha) {
        $partes = explode(' ', $linha);
        if (isset($partes[1])) {
            $horarios_bloqueados[] = substr($partes[1], 0, 5); // Pega só o "HH:MM"
        }
    }
} catch (Exception $e) {}

// 5. GRADE DE HORÁRIOS DISPONÍVEIS
$grade = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agendamento - <?= htmlspecialchars($nome_loja) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #0f1014; color: white; padding: 20px; font-family: sans-serif; }
        .card-main { background: #1a1e23; border: 1px solid #333; border-radius: 15px; padding: 25px; max-width: 550px; margin: auto; }
        .btn-check { display: none; }
        .service-card { background: #2b3035; border: 2px solid #444; padding: 15px; border-radius: 10px; cursor: pointer; text-align: center; transition: 0.3s; }
        .btn-check:checked + .service-card { border-color: #0d6efd; background: #1c2d4d; }
        .time-label { display: block; background: #2b3035; border: 1px solid #444; padding: 12px; text-align: center; border-radius: 8px; cursor: pointer; font-weight: bold; }
        .btn-check:checked + .time-label { background: #0d6efd; border-color: white; }
        .btn-check:disabled + .time-label { background: #121212; color: #444; cursor: not-allowed; opacity: 0.5; text-decoration: line-through; }
        .form-control, .form-select { background: #2b3035 !important; color: white !important; border: 1px solid #444 !important; }
    </style>
</head>
<body>

<div class="card-main shadow">
    <h3 class="text-center mb-1">Agendar Serviço</h3>
    <p class="text-center text-primary fw-bold mb-4">📍 <?= htmlspecialchars($nome_loja) ?></p>

    <form method="GET" class="mb-4">
        <input type="hidden" name="wash_id" value="<?= $wash_id ?>">
        <label class="small text-secondary mb-1">1. Escolha o dia:</label>
        <input type="date" name="data" class="form-control" value="<?= $data_selecionada ?>" onchange="this.form.submit()">
    </form>

    <hr class="border-secondary">

    <form action="save_booking.php" method="POST">
        <input type="hidden" name="wash_id" value="<?= $wash_id ?>">
        <input type="hidden" name="data_base" value="<?= $data_selecionada ?>">

        <div class="mb-4">
            <label class="small text-secondary mb-2">2. Selecione seu Veículo:</label>
            <select name="veiculo_id" class="form-select form-select-lg" required>
                <option value="">-- Escolha um veículo --</option>
                <?php foreach ($meus_carros as $carro): ?>
                    <option value="<?= $carro['id'] ?>">
                        <?= htmlspecialchars($carro['brand']) ?> <?= htmlspecialchars($carro['model']) ?> (Placa: <?= htmlspecialchars($carro['plate']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if (empty($meus_carros) && $usuario_id > 0): ?>
                <div class="alert alert-warning mt-2 small p-2 text-dark">
                    Nenhum carro encontrado. <a href="my_cars.php" class="fw-bold">Cadastrar um carro.</a>
                </div>
            <?php elseif ($usuario_id == 0): ?>
                <div class="alert alert-danger mt-2 small p-2 text-white">
                    Acesso restrito. <a href="auth.php" class="fw-bold text-warning text-decoration-underline">Faça login</a> para ver seus carros.
                </div>
            <?php endif; ?>
        </div>

        <label class="small text-secondary mb-2">3. Tipo de Lavagem:</label>
        <div class="row g-2 mb-4">
            <div class="col-6">
                <input type="radio" name="servico" id="s1" value="Lavagem Simples" class="btn-check" <?= ($tipo_pre_selecionado == 'Simples') ? 'checked' : '' ?> required>
                <label class="service-card d-block" for="s1">
                    <strong>Simples</strong><br>
                    <small>R$ <?= number_format($preco_simples, 2, ',', '.') ?></small>
                </label>
            </div>
            <div class="col-6">
                <input type="radio" name="servico" id="s2" value="Lavagem Completa" class="btn-check" <?= ($tipo_pre_selecionado == 'Completa') ? 'checked' : '' ?>>
                <label class="service-card d-block" for="s2">
                    <strong>Completa</strong><br>
                    <small>R$ <?= number_format($preco_completa, 2, ',', '.') ?></small>
                </label>
            </div>
        </div>

        <label class="small text-secondary mb-2">4. Horários para <?= date('d/m/Y', strtotime($data_selecionada)) ?>:</label>
        <div class="row g-2 mb-4">
            <?php foreach ($grade as $hora): ?>
                <?php $ocupado = in_array($hora, $horarios_bloqueados); ?>
                <div class="col-4">
                    <input type="radio" name="hora_selecionada" id="h-<?= $hora ?>" value="<?= $hora ?>" 
                           class="btn-check" <?= $ocupado ? 'disabled' : '' ?> required>
                    <label class="time-label" for="h-<?= $hora ?>"><?= $hora ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2 py-3 fw-bold shadow" <?= empty($meus_carros) ? 'disabled' : '' ?>>
            CONFIRMAR AGENDAMENTO
        </button>
    </form>
</div>

</body>
</html>
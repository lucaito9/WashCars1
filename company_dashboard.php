<?php
// Modo Debug Ativado (Ajuda a ver erros caso não seja o 500 do servidor)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';
date_default_timezone_set('America/Sao_Paulo');

// Segurança: Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// ---------------------------------------------------------
// 1. AÇÕES DE FORMULÁRIOS (REGISTRAR GASTO, CONFIGS)
// ---------------------------------------------------------
$mensagem_sucesso = "";

// Ação: REGISTRAR GASTO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    $desc = $_POST['expense_desc'] ?? '';
    // Troca vírgula por ponto para o banco de dados
    $amount = str_replace(',', '.', $_POST['expense_amount'] ?? '0');
    $amount = (float) $amount;

    if (!empty($desc) && $amount > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO expenses (description, amount) VALUES (?, ?)");
            $stmt->execute([$desc, $amount]);
            echo "<script>window.location.href='company_dashboard.php?msg=gasto_ok';</script>";
            exit;
        } catch (Exception $e) {
            echo "<script>alert('Erro ao salvar gasto. Verifique se a tabela expenses existe no banco.'); window.location.href='company_dashboard.php';</script>";
            exit;
        }
    }
}

// Ação: SALVAR CONFIGURAÇÕES (Placeholder)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    echo "<script>window.location.href='company_dashboard.php?msg=config_ok';</script>";
    exit;
}

// MENSAGENS DE SUCESSO
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'gasto_ok') {
        $mensagem_sucesso = "Gasto registrado com sucesso!";
    } elseif ($_GET['msg'] === 'config_ok') {
        $mensagem_sucesso = "Configurações e preços salvos com sucesso!";
    }
}

// ---------------------------------------------------------
// 2. AÇÕES DOS BOTÕES (STATUS E WHATSAPP)
// ---------------------------------------------------------
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'concluir' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $pdo->query("UPDATE appointments SET status = 'Concluído' WHERE id = $id");
        echo "<script>window.location.href='company_dashboard.php';</script>"; exit;
    }
    if ($action == 'aceitar' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $pdo->query("UPDATE appointments SET status = 'Confirmado' WHERE id = $id");
        echo "<script>window.location.href='company_dashboard.php';</script>"; exit;
    }
    if ($action == 'cancelar' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $pdo->query("UPDATE appointments SET status = 'Cancelado' WHERE id = $id");
        echo "<script>window.location.href='company_dashboard.php';</script>"; exit;
    }
}

// ---------------------------------------------------------
// 3. BUSCAR DADOS DO BANCO (Com Filtros)
// ---------------------------------------------------------
$appointments = [];
$gastos_total = 0;
$lista_gastos = [];

// Captura a data e o status enviados pelos filtros (se houver)
$data_filtro = $_GET['data_filtro'] ?? '';
$status_filtro = $_GET['status_filtro'] ?? '';

// Busca Agendamentos
try {
    $sql_appointments = "
        SELECT a.*, u.name AS client_name, u.phone AS client_phone, c.brand, c.model, c.plate 
        FROM appointments a 
        LEFT JOIN users u ON a.user_id = u.id 
        LEFT JOIN user_cars c ON a.car_id = c.id 
    ";

    $where_clauses = [];
    $params = [];

    // Adiciona o filtro de data se foi preenchido
    if (!empty($data_filtro)) {
        $where_clauses[] = "DATE(a.appointment_date) = :data_filtro";
        $params['data_filtro'] = $data_filtro;
    }

    // Adiciona o filtro de status se foi preenchido
    if (!empty($status_filtro)) {
        $where_clauses[] = "a.status = :status_filtro";
        $params['status_filtro'] = $status_filtro;
    }

    // Monta o WHERE se houver filtros
    if (!empty($where_clauses)) {
        $sql_appointments .= " WHERE " . implode(" AND ", $where_clauses);
    }

    // Ordenação (Mais antigo primeiro se tiver filtro de data, senão os mais recentes primeiro)
    if (!empty($data_filtro)) {
        $sql_appointments .= " ORDER BY a.appointment_date ASC";
    } else {
        $sql_appointments .= " ORDER BY a.appointment_date DESC";
    }

    $stmt = $pdo->prepare($sql_appointments);
    $stmt->execute($params);

    if ($stmt) {
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Falha silenciosa para não quebrar a tela
}

// Busca Gastos
try {
    // Total do mês
    $stmtGastos = $pdo->query("
        SELECT SUM(amount) as total 
        FROM expenses 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    if ($stmtGastos) {
        $gastos_data = $stmtGastos->fetch(PDO::FETCH_ASSOC);
        $gastos_total = !empty($gastos_data['total']) ? (float)$gastos_data['total'] : 0;
    }

    // Lista dos últimos 30 gastos
    $stmtListaGastos = $pdo->query("SELECT * FROM expenses ORDER BY created_at DESC LIMIT 30");
    if ($stmtListaGastos) {
        $lista_gastos = $stmtListaGastos->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Se a tabela ainda não existir, apenas zera os gastos em vez de dar erro
    $gastos_total = 0;
    $lista_gastos = [];
}

// ---------------------------------------------------------
// 4. CÁLCULO DE FATURAMENTO, MÉTRICAS E LUCRO
// ---------------------------------------------------------
$faturamento_total = 0;
$agendamentos_pendentes = 0;
$lavagens_concluidas = 0;

if (is_array($appointments)) {
    foreach ($appointments as $app) {
        if (isset($app['status'])) {
            if ($app['status'] == 'Concluído') {
                $faturamento_total += floatval($app['total_price'] ?? 0);
                $lavagens_concluidas++;
            }
            if ($app['status'] == 'Pendente') {
                $agendamentos_pendentes++;
            }
        }
    }
}

$lucro_total = $faturamento_total - $gastos_total;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel da Empresa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: #000; padding: 15px 0; }
        .card-metric { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; }
        .card-metric:hover { transform: translateY(-5px); }
        .metric-title { font-size: 0.85rem; color: #6c757d; font-weight: bold; text-transform: uppercase; }
        .metric-value { font-size: 1.6rem; font-weight: bold; color: #000; }
        .metric-value.gold { color: #D4AF37; }
        .table-container { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .badge-pendente { background-color: #ffc107; color: #000; }
        .badge-confirmado { background-color: #0d6efd; color: #fff; }
        .badge-concluido { background-color: #198754; color: #fff; }
        .badge-cancelado { background-color: #dc3545; color: #fff; }
        .btn-action { padding: 5px 10px; font-size: 0.85rem; border-radius: 8px; font-weight: bold; }
        .scroll-table { max-height: 250px; overflow-y: auto; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container d-flex justify-content-between">
        <a href="index.php" class="text-white text-decoration-none fw-bold fs-5">
            <i class="bi bi-droplet-fill" style="color: #D4AF37;"></i> Painel da Empresa
        </a>
        <a href="logout.php" class="btn btn-outline-light btn-sm fw-bold">Sair</a>
    </div>
</nav>

<div class="container mb-5">

    <?php if(!empty($mensagem_sucesso)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?= $mensagem_sucesso ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-dark fw-bold" data-bs-toggle="modal" data-bs-target="#configModal">
            <i class="bi bi-gear-fill"></i> Configurar Loja e Preços
        </button>
    </div>
    
    <div class="row mb-4">
        <div class="col-md mb-3">
            <div class="card card-metric p-3 h-100">
                <div class="metric-title">Faturamento</div>
                <div class="metric-value gold">R$ <?= number_format($faturamento_total, 2, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-md mb-3">
            <div class="card card-metric p-3 h-100" style="background: #212529; color: white;">
                <div class="metric-title text-light">Gastos (Mês)</div>
                <div class="metric-value text-danger">R$ <?= number_format($gastos_total, 2, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-md mb-3">
            <div class="card card-metric p-3 h-100">
                <div class="metric-title">Lucro Líquido</div>
                <div class="metric-value <?= $lucro_total >= 0 ? 'text-success' : 'text-danger' ?>">
                    R$ <?= number_format($lucro_total, 2, ',', '.') ?>
                </div>
            </div>
        </div>
        <div class="col-md mb-3">
            <div class="card card-metric p-3 h-100">
                <div class="metric-title">Pendentes</div>
                <div class="metric-value"><?= $agendamentos_pendentes ?></div>
            </div>
        </div>
        <div class="col-md mb-3">
            <div class="card card-metric p-3 h-100">
                <div class="metric-title">Concluídas</div>
                <div class="metric-value"><?= $lavagens_concluidas ?></div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-5 mb-3">
            <div class="table-container h-100 border-start border-4 border-danger">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-down-arrow text-danger"></i> Registrar Novo Gasto</h5>
                <form action="company_dashboard.php" method="POST" class="row g-2">
                    <input type="hidden" name="add_expense" value="1">
                    <div class="col-12 mb-2">
                        <input type="text" class="form-control" name="expense_desc" placeholder="Ex: Conta de Água, Produtos..." required>
                    </div>
                    <div class="col-8">
                        <div class="input-group">
                            <span class="input-group-text bg-light">R$</span>
                            <input type="number" step="0.01" class="form-control" name="expense_amount" placeholder="0,00" required>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Salvar</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-7 mb-3">
            <div class="table-container h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-secondary"></i> Histórico de Gastos</h5>
                <div class="scroll-table pe-2">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Data</th>
                                <th>Descrição</th>
                                <th class="text-end">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($lista_gastos) > 0): ?>
                                <?php foreach ($lista_gastos as $gasto): 
                                    $data_gasto = date('d/m/Y', strtotime($gasto['created_at']));
                                    $valor_gasto = number_format($gasto['amount'], 2, ',', '.');
                                ?>
                                <tr>
                                    <td class="text-muted small"><?= $data_gasto ?></td>
                                    <td><?= htmlspecialchars($gasto['description']) ?></td>
                                    <td class="text-danger fw-bold text-end">- R$ <?= $valor_gasto ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Nenhum gasto registrado ainda.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h4 class="fw-bold mb-0">Gestão de Agendamentos</h4>
            
            <form method="GET" action="company_dashboard.php" class="d-flex gap-2 align-items-center flex-wrap">
                <label for="data_filtro" class="fw-bold mb-0 text-nowrap text-muted"><i class="bi bi-calendar-event"></i> Data:</label>
                <input type="date" id="data_filtro" name="data_filtro" class="form-control form-control-sm" style="width: auto;" value="<?= htmlspecialchars($data_filtro) ?>">
                
                <label for="status_filtro" class="fw-bold mb-0 text-nowrap text-muted ms-lg-2"><i class="bi bi-funnel"></i> Status:</label>
                <select id="status_filtro" name="status_filtro" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Todos</option>
                    <option value="Pendente" <?= $status_filtro == 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="Confirmado" <?= $status_filtro == 'Confirmado' ? 'selected' : '' ?>>Confirmado</option>
                    <option value="Concluído" <?= $status_filtro == 'Concluído' ? 'selected' : '' ?>>Concluído</option>
                    <option value="Cancelado" <?= $status_filtro == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm fw-bold px-3">Buscar</button>
                <a href="company_dashboard.php" class="btn btn-outline-secondary btn-sm px-3">Limpar</a>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Data/Hora</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Serviços</th>
                        <th>Valor (R$)</th>
                        <th>Status</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $app): 
                            $date_format = !empty($app['appointment_date']) ? date('d/m/Y H:i', strtotime($app['appointment_date'])) : '--/--/---- --:--';
                            $car_info = !empty($app['brand']) ? "{$app['brand']} {$app['model']} ({$app['plate']})" : "Veículo Removido";
                            $servicos = !empty($app['services']) ? $app['services'] : ($app['service_type'] ?? 'Não informado');
                            $valor = !empty($app['total_price']) && $app['total_price'] > 0 ? number_format($app['total_price'], 2, ',', '.') : '---';

                            $badgeClass = 'badge-pendente';
                            if (isset($app['status'])) {
                                if ($app['status'] == 'Confirmado') $badgeClass = 'badge-confirmado';
                                if ($app['status'] == 'Concluído') $badgeClass = 'badge-concluido';
                                if ($app['status'] == 'Cancelado') $badgeClass = 'badge-cancelado';
                            }

                            // Link WhatsApp
                            $numero = preg_replace('/[^0-9]/', '', $app['client_phone'] ?? '');
                            if (!empty($numero)) {
                                if (strlen($numero) < 12) { $numero = "55" . $numero; }
                                $nome_cliente = $app['client_name'] ?? 'Cliente';
                                $nome_carro = !empty($app['brand']) ? "{$app['brand']} {$app['model']}" : 'veículo';
                                $msg_wa = urlencode("Olá {$nome_cliente}, a lavagem do seu {$nome_carro} foi concluída e está pronto para retirada!");
                                $link_wa = "https://wa.me/{$numero}?text={$msg_wa}";
                            } else {
                                $link_wa = "";
                            }
                        ?>
                        <tr>
                            <td class="fw-bold"><?= $date_format ?></td>
                            <td>
                                <?= htmlspecialchars($app['client_name'] ?? 'Sem nome') ?><br>
                                <small class="text-muted"><?= htmlspecialchars($app['client_phone'] ?? 'Sem telefone') ?></small>
                            </td>
                            <td><?= htmlspecialchars($car_info ?? '') ?></td>
                            <td><?= htmlspecialchars($servicos ?? '') ?></td>
                            <td class="fw-bold gold">R$ <?= $valor ?></td>
                            <td><span class="badge <?= $badgeClass ?> px-2 py-1"><?= htmlspecialchars($app['status'] ?? 'Pendente') ?></span></td>
                            
                            <td class="text-center">
                                <?php if (isset($app['status']) && $app['status'] == 'Pendente'): ?>
                                    <a href="company_dashboard.php?action=aceitar&id=<?= $app['id'] ?>" class="btn btn-success btn-action text-white mb-1"><i class="bi bi-check-circle"></i> Aceitar</a>
                                    <a href="company_dashboard.php?action=cancelar&id=<?= $app['id'] ?>" class="btn btn-danger btn-action text-white mb-1"><i class="bi bi-x-circle"></i> Recusar</a>
                                
                                <?php elseif (isset($app['status']) && $app['status'] == 'Confirmado'): ?>
                                    <button onclick="concluirServico(<?= $app['id'] ?>, '<?= $link_wa ?>')" class="btn btn-dark btn-action w-100" style="border: 1px solid #D4AF37; color: #D4AF37;">
                                        <i class="bi bi-whatsapp"></i> Concluir e Avisar
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nenhum agendamento encontrado para estes filtros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="configModalLabel"><i class="bi bi-gear"></i> Configurar Loja e Preços</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="company_dashboard.php" method="POST">
                    <input type="hidden" name="save_config" value="1">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Nome da Loja</label>
                            <input type="text" class="form-control" name="store_name" value="teste3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Frase/Descrição</label>
                            <input type="text" class="form-control" name="store_desc" placeholder="Ex: O melhor brilho da cidade!">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 border-bottom pb-2">Tabela de Preços (R$)</h6>
                    
                    <div class="row g-3" id="servicesContainer">
                        <div class="col-md-4">
                            <label class="form-label text-muted">Lavagem Simples</label>
                            <input type="text" class="form-control" name="prices[simples]" value="150,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Lavagem Completa</label>
                            <input type="text" class="form-control" name="prices[completa]" value="200,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Lavagem Interna</label>
                            <input type="text" class="form-control" name="prices[interna]" value="200,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Lavagem Detalhada</label>
                            <input type="text" class="form-control" name="prices[detalhada]" value="500,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Aplicação de Cera</label>
                            <input type="text" class="form-control" name="prices[cera]" value="220,00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted">Lavagem de Chassi</label>
                            <input type="text" class="form-control" name="prices[chassi]" value="300,00">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="button" class="btn btn-outline-dark btn-sm fw-bold" onclick="addService()">
                            <i class="bi bi-plus-circle"></i> Adicionar Mais Opções de Lavagem
                        </button>
                    </div>

                    <div class="modal-footer border-0 mt-4 px-0 pb-0">
                        <button type="submit" class="btn btn-success w-100 fw-bold">Salvar Configurações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function concluirServico(idAgendamento, linkWhatsApp) {
        if (linkWhatsApp !== '') {
            window.open(linkWhatsApp, '_blank');
        } else {
            alert('Atenção: Este cliente não possui número de telefone cadastrado.');
        }
        window.location.href = 'company_dashboard.php?action=concluir&id=' + idAgendamento;
    }

    function addService() {
        const container = document.getElementById('servicesContainer');
        const newDiv = document.createElement('div');
        newDiv.className = 'col-md-4 mt-3';
        newDiv.innerHTML = `
            <label class="form-label text-muted d-flex justify-content-between">
                Novo Serviço
                <i class="bi bi-trash text-danger" style="cursor:pointer;" onclick="this.parentElement.parentElement.remove()"></i>
            </label>
            <div class="input-group">
                <input type="text" class="form-control" name="new_service_names[]" placeholder="Ex: Higienização">
                <span class="input-group-text">R$</span>
                <input type="text" class="form-control" name="new_service_prices[]" placeholder="00,00">
            </div>
        `;
        container.appendChild(newDiv);
    }
</script>

</body>
</html>

<?php
// LIGANDO O MODO DETETIVE
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// company_dashboard.php - Painel Completo
session_start();
require 'db.php';

// Segurança
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'company') {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// 1. ATUALIZAR DADOS DA EMPRESA
if (isset($_POST['update_info'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $price_simple = $_POST['price_simple'];
    $price_complete = $_POST['price_complete'];

    $stmt = $pdo->prepare("SELECT id FROM car_washes WHERE owner_id = ?");
    $stmt->execute([$user_id]);
    
    if ($stmt->fetch()) {
        $sql = "UPDATE car_washes SET name=?, address=?, phone=?, price_simple=?, price_complete=? WHERE owner_id=?";
        $pdo->prepare($sql)->execute([$name, $address, $phone, $price_simple, $price_complete, $user_id]);
        $message = "Dados salvos com sucesso!";
    } else {
        $sql = "INSERT INTO car_washes (owner_id, name, address, phone, price_simple, price_complete) VALUES (?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$user_id, $name, $address, $phone, $price_simple, $price_complete]);
        $message = "Lava Jato ativado!";
    }
}

// BUSCAR OS DADOS DO MEU LAVA JATO
$stmt = $pdo->prepare("SELECT * FROM car_washes WHERE owner_id = ?");
$stmt->execute([$user_id]);
$my_wash = $stmt->fetch();

// 2. CONFIRMAR AGENDAMENTO
if (isset($_POST['confirm_appt'])) {
    $id = $_POST['appointment_id'];
    $pdo->prepare("UPDATE appointments SET status = 'Confirmado' WHERE id = ?")->execute([$id]);
    $message = "Agendamento aceito!";
}

// 3. FINALIZAR SERVIÇO E ADICIONAR FATURAMENTO
if (isset($_POST['mark_done']) && $my_wash) {
    $id = $_POST['appointment_id'];
    $pdo->prepare("UPDATE appointments SET status = 'Concluído' WHERE id = ?")->execute([$id]);
    
    // Insere valor base no faturamento
    $pdo->prepare("INSERT INTO caixa (car_wash_id, valor) VALUES (?, ?)")->execute([$my_wash['id'], 50.00]);
    $message = "Serviço finalizado! Valor adicionado ao faturamento.";
}

// 4. REGISTRAR NOVO GASTO / DESPESA
if (isset($_POST['add_expense']) && $my_wash) {
    $descricao = $_POST['descricao'];
    $categoria = $_POST['categoria'];
    $valor = str_replace(',', '.', $_POST['valor']); 
    
    try {
        $pdo->prepare("INSERT INTO despesas (car_wash_id, descricao, categoria, valor) VALUES (?, ?, ?, ?)")->execute([$my_wash['id'], $descricao, $categoria, $valor]);
        $message = "Gasto registrado com sucesso!";
    } catch (Throwable $e) {
        $message = "Erro ao salvar despesa: " . $e->getMessage();
    }
}

// 4.5. APAGAR SERVIÇO OU GASTO
if (isset($_POST['delete_appt'])) {
    $pdo->prepare("DELETE FROM appointments WHERE id = ?")->execute([$_POST['appointment_id']]);
    $message = "Serviço apagado do histórico!";
}
if (isset($_POST['delete_expense'])) {
    $pdo->prepare("DELETE FROM despesas WHERE id = ?")->execute([$_POST['expense_id']]);
    $message = "Gasto apagado com sucesso!";
}

// 5. CÁLCULOS DO MÊS
$total_faturado = 0.00;
$total_despesas = 0.00;

if ($my_wash) {
    try {
        $stmt_caixa = $pdo->prepare("SELECT SUM(valor) as total FROM caixa WHERE car_wash_id = ?");
        $stmt_caixa->execute([$my_wash['id']]);
        $dados_caixa = $stmt_caixa->fetch();
        $total_faturado = !empty($dados_caixa['total']) ? (float)$dados_caixa['total'] : 0.00;
    } catch (Throwable $e) {}

    try {
        $stmt_desp = $pdo->prepare("SELECT SUM(valor) as total FROM despesas WHERE car_wash_id = ?");
        $stmt_desp->execute([$my_wash['id']]);
        $dados_desp = $stmt_desp->fetch();
        $total_despesas = !empty($dados_desp['total']) ? (float)$dados_desp['total'] : 0.00;
    } catch (Throwable $e) {}
}

$lucro_liquido = $total_faturado - $total_despesas;

// BUSCAR A AGENDA 
$appointments = [];
if ($my_wash) {
    try {
        $sql = "SELECT a.*, u.name as client_name, '' as client_phone, uc.brand, uc.model, uc.plate 
                FROM appointments a 
                JOIN users u ON a.user_id = u.id 
                LEFT JOIN user_cars uc ON a.car_id = uc.id
                WHERE a.car_wash_id = ? 
                ORDER BY a.appointment_date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$my_wash['id']]);
        $appointments = $stmt->fetchAll();
    } catch (Throwable $e) {
        $message = "Erro ao buscar agenda: " . $e->getMessage();
    }
}

// BUSCAR HISTÓRICO DE GASTOS
$lista_gastos = [];
if ($my_wash) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM despesas WHERE car_wash_id = ? ORDER BY data_despesa DESC");
        $stmt->execute([$my_wash['id']]);
        $lista_gastos = $stmt->fetchAll();
    } catch (Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Empresa - Wash Cars</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-dark text-white">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-speedometer"></i> Painel de Controle</h2>
        <div>
            <button class="btn btn-primary btn-sm me-2 fw-bold" data-bs-toggle="modal" data-bs-target="#perfilModal">
                <i class="bi bi-person-fill"></i> Meu Negócio
            </button>
            <a href="index.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-house-door"></i> Ir para o Site</a>
            <a href="logout.php" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </div>
    </div>

    <?php if($message): ?>
        <div class="alert <?= strpos($message, 'Erro') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show text-dark fw-bold">
            <i class="bi bi-info-circle me-2"></i><?= $message ?> 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($my_wash): ?>
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card shadow" style="background: linear-gradient(45deg, #198754, #20c997); border: none; border-radius: 15px;">
                <div class="card-body p-3 text-white">
                    <h6 class="text-uppercase text-white-50 fw-bold mb-1"><i class="bi bi-graph-up-arrow"></i> Faturamento Total</h6>
                    <h3 class="fw-bold mb-0">R$ <?= number_format($total_faturado, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow" style="background: linear-gradient(45deg, #dc3545, #f87171); border: none; border-radius: 15px;">
                <div class="card-body p-3 text-white">
                    <h6 class="text-uppercase text-white-50 fw-bold mb-1"><i class="bi bi-graph-down-arrow"></i> Total de Gastos</h6>
                    <h3 class="fw-bold mb-0">R$ <?= number_format($total_despesas, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow" style="background: linear-gradient(45deg, #0d6efd, #3b82f6); border: none; border-radius: 15px;">
                <div class="card-body p-3 text-white">
                    <h6 class="text-uppercase text-white-50 fw-bold mb-1"><i class="bi bi-piggy-bank"></i> Lucro Líquido</h6>
                    <h3 class="fw-bold mb-0">R$ <?= number_format($lucro_liquido, 2, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <?php if($my_wash): ?>
            <div class="card bg-dark border-danger mb-4 shadow-sm">
                <div class="card-header border-danger text-danger fw-bold"><i class="bi bi-cart-dash me-2"></i>Registrar Gasto</div>
                <div class="card-body">
                    <form method="POST">
                        <label class="small text-white-50 mb-1">O que foi pago?</label>
                        <input type="text" name="descricao" class="form-control mb-2 bg-dark text-white border-secondary" placeholder="Ex: Conta de Água, Sabão..." required>
                        
                        <label class="small text-white-50 mb-1">Categoria</label>
                        <select name="categoria" class="form-select mb-2 bg-dark text-white border-secondary" required>
                            <option value="Contas">Água, Luz, Internet</option>
                            <option value="Produtos">Produtos de Limpeza</option>
                            <option value="Funcionarios">Pagamento de Funcionários</option>
                            <option value="Manutencao">Manutenção de Equipamentos</option>
                            <option value="Outros">Outros</option>
                        </select>
                        
                        <label class="small text-white-50 mb-1">Valor</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-secondary text-white border-secondary">R$</span>
                            <input type="number" step="0.01" name="valor" class="form-control bg-dark text-white border-secondary" placeholder="0.00" required>
                        </div>
                        <button type="submit" name="add_expense" class="btn btn-outline-danger w-100 fw-bold">Salvar Despesa</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-8">
            <div class="card bg-dark border-secondary mb-4 shadow-sm">
                <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-white"><i class="bi bi-calendar-check me-2"></i>Agenda de Serviços</span>
                    <span class="badge bg-primary rounded-pill"><?= count($appointments) ?></span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th class="ps-3">Data</th>
                                <th>Veículo / Cliente</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($appointments) == 0): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum agendamento encontrado.</td></tr>
                            <?php endif; ?>

                            <?php foreach($appointments as $app): 
                                $date = date('d/m H:i', strtotime($app['appointment_date'] ?? $app['created_at']));
                                $status = $app['status'];
                                $telefone_limpo = preg_replace('/[^0-9]/', '', $app['client_phone'] ?? '');
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold text-info"><?= $date ?></td>
                                <td>
                                    <?php if(isset($app['plate']) && $app['plate']): ?>
                                        <div class="fw-bold"><?= $app['brand'] ?> <?= $app['model'] ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">Não informado</span>
                                    <?php endif; ?>
                                    
                                    <div class="small mt-1 d-flex align-items-center">
                                        <span class="text-muted"><i class="bi bi-person-fill"></i> <?= $app['client_name'] ?></span>
                                        <?php if(!empty($telefone_limpo)): ?>
                                            <a href="https://wa.me/55<?= $telefone_limpo ?>?text=Ol%C3%A1%21+Vimos+seu+agendamento+no+Wash+Cars%21" target="_blank" class="btn btn-sm btn-success py-0 px-2 ms-2 rounded-pill" title="Chamar no WhatsApp"><i class="bi bi-whatsapp" style="font-size: 0.8rem;"></i></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($status == 'Pendente'): ?>
                                        <span class="badge bg-warning text-dark">Aguardando</span>
                                    <?php elseif($status == 'Confirmado'): ?>
                                        <span class="badge bg-primary">Em Andamento</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Concluído</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <?php if($status == 'Pendente'): ?>
                                        <form method="POST" class="d-inline"><input type="hidden" name="appointment_id" value="<?= $app['id'] ?>"><button type="submit" name="confirm_appt" class="btn btn-sm btn-outline-primary fw-bold">Aceitar</button></form>
                                    <?php elseif($status == 'Confirmado'): ?>
                                        <form method="POST" class="d-inline"><input type="hidden" name="appointment_id" value="<?= $app['id'] ?>"><button type="submit" name="mark_done" class="btn btn-sm btn-success fw-bold">Finalizar & Faturar</button></form>
                                    <?php else: ?>
                                        <span class="text-muted small me-2"><i class="bi bi-check2-circle"></i> Pago</span>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja apagar este serviço do histórico?');">
                                            <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                            <button type="submit" name="delete_appt" class="btn btn-sm btn-outline-danger" title="Apagar Serviço"><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($my_wash): ?>
            <div class="card bg-dark border-danger shadow-sm">
                <div class="card-header border-danger d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-danger"><i class="bi bi-receipt me-2"></i>Histórico de Gastos</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-dark table-hover mb-0 align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th class="ps-3">Data</th>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th class="text-end">Valor</th>
                                <th class="text-end pe-3">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($lista_gastos) == 0): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Nenhum gasto registrado ainda.</td></tr>
                            <?php endif; ?>

                            <?php foreach($lista_gastos as $gasto): ?>
                            <tr>
                                <td class="ps-3 text-muted small"><?= date('d/m/Y', strtotime($gasto['data_despesa'])) ?></td>
                                <td class="fw-bold"><?= $gasto['descricao'] ?></td>
                                <td><span class="badge bg-secondary"><?= $gasto['categoria'] ?></span></td>
                                <td class="text-end text-danger fw-bold">- R$ <?= number_format($gasto['valor'], 2, ',', '.') ?></td>
                                <td class="text-end pe-3">
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Apagar este gasto? O seu Lucro será recalculado.');">
                                        <input type="hidden" name="expense_id" value="<?= $gasto['id'] ?>">
                                        <button type="submit" name="delete_expense" class="btn btn-sm btn-outline-danger py-0 px-2" title="Apagar Gasto"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<div class="modal fade" id="perfilModal" tabindex="-1" aria-labelledby="perfilModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="perfilModalLabel"><i class="bi bi-shop me-2"></i>Editar Meu Negócio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <label class="small text-white-50 mb-1">Nome do Estabelecimento</label>
                    <input type="text" name="name" class="form-control mb-3 bg-dark text-white border-secondary" placeholder="Nome do Lava Jato" value="<?= $my_wash['name'] ?? '' ?>" required>
                    
                    <label class="small text-white-50 mb-1">Endereço Completo</label>
                    <input type="text" name="address" class="form-control mb-3 bg-dark text-white border-secondary" placeholder="Endereço" value="<?= $my_wash['address'] ?? '' ?>" required>
                    
                    <label class="small text-white-50 mb-1">WhatsApp para Contato</label>
                    <input type="text" name="phone" class="form-control mb-3 bg-dark text-white border-secondary" placeholder="Ex: 11999999999" value="<?= $my_wash['phone'] ?? '' ?>">
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small text-white-50 mb-1">R$ Lavagem Simples</label>
                            <input type="number" step="0.01" name="price_simple" class="form-control bg-dark text-white border-secondary" placeholder="0.00" value="<?= $my_wash['price_simple'] ?? '' ?>">
                        </div>
                        <div class="col-6">
                            <label class="small text-white-50 mb-1">R$ Lav. Completa</label>
                            <input type="number" step="0.01" name="price_complete" class="form-control bg-dark text-white border-secondary" placeholder="0.00" value="<?= $my_wash['price_complete'] ?? '' ?>">
                        </div>
                    </div>
                    <button type="submit" name="update_info" class="btn btn-primary w-100 fw-bold">Salvar Configurações</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
</body>
</html>
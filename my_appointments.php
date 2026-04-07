<?php
// LIGANDO O MODO DETETIVE: Mostra o erro real na tela ao invés do Erro 500
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Painel do Cliente (Baseado no seu Painel da Empresa)
session_start();
require 'db.php';

// Segurança: Verifica se o cliente está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// 1. ATUALIZAR DADOS CADASTRAIS DO CLIENTE
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    try {
        $sql = "UPDATE users SET name=?, email=?, phone=? WHERE id=?";
        $pdo->prepare($sql)->execute([$name, $email, $phone, $user_id]);
        $message = "Dados salvos com sucesso!";
    } catch (Throwable $e) {
        $message = "Erro ao salvar: " . $e->getMessage();
    }
}

// BUSCAR OS DADOS DO CLIENTE PARA O MODAL
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

// BUSCAR A AGENDA DO CLIENTE
$appointments = [];
try {
    $sql = "SELECT a.*, cw.name as car_wash_name, uc.brand, uc.model, uc.plate 
            FROM appointments a 
            JOIN car_washes cw ON a.car_wash_id = cw.id 
            LEFT JOIN user_cars uc ON a.car_id = uc.id
            WHERE a.user_id = ? 
            ORDER BY a.appointment_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll();
} catch (Throwable $e) {
    $message = "Erro ao buscar agendamentos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Cliente - Wash Cars</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-dark text-white">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-person-badge"></i> Área do Cliente</h2>
        <div>
            <button class="btn btn-primary btn-sm me-2 fw-bold" data-bs-toggle="modal" data-bs-target="#perfilModal">
                <i class="bi bi-person-fill"></i> Meu Perfil
            </button>
            <a href="booking.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-calendar-plus"></i> Agendar Lavagem</a>
            <a href="my_cars.php" class="btn btn-outline-light btn-sm me-2"><i class="bi bi-car-front"></i> Meus Carros</a>
            <a href="logout.php" class="btn btn-danger btn-sm fw-bold"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </div>
    </div>

    <?php if($message): ?>
        <div class="alert <?= strpos($message, 'Erro') !== false ? 'alert-danger' : 'alert-success' ?> alert-dismissible fade show text-dark fw-bold">
            <i class="bi bi-info-circle me-2"></i><?= $message ?> 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card bg-dark border-secondary mb-4 shadow-sm">
        <div class="card-header border-secondary d-flex justify-content-between align-items-center">
            <span class="fw-bold text-white"><i class="bi bi-calendar-check me-2"></i>Meus Agendamentos</span>
            <span class="badge bg-primary rounded-pill"><?= count($appointments) ?></span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-dark table-hover mb-0 align-middle">
                <thead>
                    <tr class="text-secondary small text-uppercase">
                        <th class="ps-3">Data</th>
                        <th>Lava Jato</th>
                        <th>Veículo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($appointments) == 0): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">Nenhum agendamento encontrado.</td></tr>
                    <?php endif; ?>

                    <?php foreach($appointments as $app): 
                        $date = date('d/m H:i', strtotime($app['appointment_date'] ?? $app['created_at']));
                        $status = $app['status'];
                    ?>
                    <tr>
                        <td class="ps-3 fw-bold text-info"><?= $date ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($app['car_wash_name'] ?? 'Não informado') ?></td>
                        <td>
                            <?php if(!empty($app['plate'])): ?>
                                <div class="fw-bold"><?= htmlspecialchars($app['brand']) ?> <?= htmlspecialchars($app['model']) ?></div>
                                <span class="badge bg-secondary"><?= htmlspecialchars($app['plate']) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">Não informado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($status == 'Pendente'): ?>
                                <span class="badge bg-warning text-dark">Aguardando</span>
                            <?php elseif($status == 'Confirmado'): ?>
                                <span class="badge bg-primary">Confirmado</span>
                            <?php else: ?>
                                <span class="badge bg-success">Concluído</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="perfilModal" tabindex="-1" aria-labelledby="perfilModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold" id="perfilModalLabel"><i class="bi bi-person-lines-fill me-2"></i>Editar Meu Perfil</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <label class="small text-white-50 mb-1">Nome Completo</label>
                    <input type="text" name="name" class="form-control mb-3 bg-dark text-white border-secondary" placeholder="Seu nome" value="<?= htmlspecialchars($user_data['name'] ?? '') ?>" required>
                    
                    <label class="small text-white-50 mb-1">E-mail</label>
                    <input type="email" name="email" class="form-control mb-3 bg-dark text-white border-secondary" placeholder="seu@email.com" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required>
                    
                    <label class="small text-white-50 mb-1">WhatsApp / Telefone</label>
                    <input type="text" name="phone" class="form-control mb-4 bg-dark text-white border-secondary" placeholder="Ex: 11999999999" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>">
                    
                    <button type="submit" name="update_profile" class="btn btn-primary w-100 fw-bold">Salvar Configurações</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Evita o reenvio de formulários ao atualizar a página (F5)
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>
</body>
</html>
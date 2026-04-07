<?php
// my_cars.php - Gerenciar Veículos
session_start();
require 'db.php';

// Segurança: Apenas clientes logados
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'client') {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$msg_type = '';

// 1. ADICIONAR NOVO CARRO
if (isset($_POST['add_car'])) {
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $color = trim($_POST['color']);
    $plate = strtoupper(trim($_POST['plate'])); // Deixa a placa maiúscula automaticamente

    if(!empty($brand) && !empty($model) && !empty($plate)){
        $stmt = $pdo->prepare("INSERT INTO user_cars (user_id, brand, model, color, plate) VALUES (?, ?, ?, ?, ?)");
        if($stmt->execute([$user_id, $brand, $model, $color, $plate])){
            $message = "Veículo adicionado com sucesso!";
            $msg_type = "success";
        } else {
            $message = "Erro ao adicionar veículo.";
            $msg_type = "danger";
        }
    }
}

// 2. EXCLUIR CARRO
if (isset($_GET['delete'])) {
    $car_id = $_GET['delete'];
    // Verifica se o carro pertence mesmo ao usuário logado (segurança)
    $stmt = $pdo->prepare("DELETE FROM user_cars WHERE id = ? AND user_id = ?");
    if($stmt->execute([$car_id, $user_id])){
        $message = "Veículo removido.";
        $msg_type = "warning";
    }
}

// 3. BUSCAR MEUS CARROS
$stmt = $pdo->prepare("SELECT * FROM user_cars WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$my_cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Carros - Wash Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark mb-5">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🚿 Wash Cars</a>
            <div>
                <a href="my_appointments.php" class="btn btn-outline-light btn-sm me-2">Agendamentos</a>
                <a href="index.php" class="btn btn-outline-light btn-sm">Voltar</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-plus-circle"></i> Adicionar Novo Carro
                    </div>
                    <div class="card-body">
                        <?php if($message): ?>
                            <div class="alert alert-<?= $msg_type ?> py-2"><?= $message ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label small text-muted">Marca</label>
                                    <input type="text" name="brand" class="form-control" placeholder="Ex: Fiat" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label small text-muted">Modelo</label>
                                    <input type="text" name="model" class="form-control" placeholder="Ex: Toro" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label small text-muted">Cor do Veículo</label>
                                <input type="text" name="color" class="form-control" placeholder="Ex: Preto Metálico" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small text-muted">Placa</label>
                                <input type="text" name="plate" class="form-control text-uppercase fw-bold" placeholder="ABC-1234" maxlength="8" required>
                            </div>

                            <button type="submit" name="add_car" class="btn btn-primary w-100">
                                Salvar Veículo
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <h4 class="mb-3 text-secondary">Meus Veículos Cadastrados</h4>
                
                <?php if(count($my_cars) == 0): ?>
                    <div class="alert alert-light text-center border">
                        Nenhum carro cadastrado. Adicione um ao lado!
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach($my_cars as $car): ?>
                        <div class="col-12 mb-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-3 me-3 text-secondary">
                                            <i class="bi bi-car-front fs-4"></i>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?></h5>
                                            <small class="text-muted">Cor: <?= htmlspecialchars($car['color']) ?> | Placa: <span class="badge bg-secondary"><?= htmlspecialchars($car['plate']) ?></span></small>
                                        </div>
                                    </div>
                                    
                                    <a href="?delete=<?= $car['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este carro?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

</body>
</html>
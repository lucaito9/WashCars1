<?php
// Modo debug mantido caso algo mais aconteça, mas agora deve passar direto!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'client') { 
    header("Location: auth.php"); 
    exit; 
}

$wash_id = $_GET['wash_id'] ?? null;
if (!$wash_id) { header("Location: index.php"); exit; }

$stmtWash = $pdo->prepare("SELECT * FROM car_washes WHERE id = ?");
$stmtWash->execute([$wash_id]);
$wash = $stmtWash->fetch(PDO::FETCH_ASSOC);

if (!$wash) { header("Location: index.php"); exit; }

$stmtCars = $pdo->prepare("SELECT * FROM user_cars WHERE user_id = ?");
$stmtCars->execute([$_SESSION['user_id']]);
$my_cars = $stmtCars->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book'])) {
    
    if (empty($_POST['car_id'])) {
        echo "<script>alert('Erro: Nenhum veículo selecionado.'); window.history.back();</script>";
        exit;
    }

    $car_id = $_POST['car_id'];
    $date = $_POST['booking_date'];
    
    if (strtotime($date) < time()) {
        echo "<script>alert('Escolha uma data futura!'); window.history.back();</script>";
        exit;
    }

    // MISTÉRIO RESOLVIDO: Usando 'car_wash_id' conforme o seu banco de dados
    $stmtCheck = $pdo->prepare("SELECT id FROM appointments WHERE car_wash_id = ? AND appointment_date = ? AND status != 'Cancelado'");
    $stmtCheck->execute([$wash_id, $date]);
    if ($stmtCheck->rowCount() > 0) {
        echo "<script>alert('Este horário já está ocupado!'); window.history.back();</script>";
        exit;
    }

    $selected_services = $_POST['services'] ?? [];
    $services_string = implode(", ", $selected_services); 
    $total_price = floatval($_POST['total_price_hidden'] ?? 0);

    if (empty($selected_services)) {
        echo "<script>alert('Selecione pelo menos um serviço!'); window.history.back();</script>";
        exit;
    }

    try {
        // MISTÉRIO RESOLVIDO: Inserindo no 'car_wash_id'
        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, car_wash_id, car_id, appointment_date, services, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'Pendente')");
        $stmt->execute([$_SESSION['user_id'], $wash_id, $car_id, $date, $services_string, $total_price]);
        
        echo "<script>alert('Agendamento realizado com sucesso!'); window.location.href='index.php';</script>";
        exit;

    } catch (PDOException $e) {
        die("<strong>ERRO GRAVE NO BANCO DE DADOS:</strong> " . $e->getMessage());
    }
}

$min_datetime = date('Y-m-d\TH:i');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Reservar - <?= htmlspecialchars($wash['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: #000; padding: 15px 0; }
        .hero-section { 
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?q=80&w=1000') center/cover;
            height: 200px; display: flex; align-items: center; justify-content: center; color: white; border-radius: 0 0 40px 40px;
        }
        .main-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-top: -40px; padding: 30px; border: none; }
        .service-box { border: 2px solid #eee; border-radius: 12px; padding: 12px; margin-bottom: 10px; cursor: pointer; transition: 0.2s; display: flex; justify-content: space-between; align-items: center; }
        .service-check:checked + .service-box { border-color: #D4AF37; background: #fffdf5; }
        .price-tag { color: #D4AF37; font-weight: bold; }
        .btn-confirm { background: #000; color: #fff; border-radius: 50px; padding: 15px; font-weight: bold; border: 2px solid #D4AF37; }
        .btn-confirm:hover { background: #D4AF37; color: #000; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
    <div class="container"><a href="index.php" class="text-white text-decoration-none fw-bold"><i class="bi bi-arrow-left"></i> VOLTAR</a></div>
</nav>

<div class="hero-section text-center">
    <h2 class="fw-bold"><?= htmlspecialchars($wash['name']) ?></h2>
</div>

<div class="container mb-5">
    <div class="main-card">
        <form method="POST">
            <input type="hidden" name="total_price_hidden" id="total_price_hidden" value="0">
            <input type="hidden" name="book" value="1">
            <div class="row">
                <div class="col-md-6 border-end pe-md-4">
                    <h5 class="fw-bold mb-3">Escolha os Serviços</h5>
                    <?php 
                    $services_list = [
                        'Lavagem Simples' => 'price_simple',
                        'Lavagem Completa' => 'price_complete',
                        'Lavagem Interna' => 'price_interna',
                        'Lavagem Detalhada' => 'price_detalhada',
                        'Aplicação de Cera' => 'price_cera',
                        'Lavagem de Chassi' => 'price_chassi'
                    ];
                    foreach ($services_list as $label => $col): 
                        $val = $wash[$col] ?? 0;
                        if ($val > 0):
                    ?>
                    <label class="w-100 mb-0">
                        <input type="checkbox" name="services[]" value="<?= $label ?>" class="d-none service-check" data-price="<?= $val ?>">
                        <div class="service-box">
                            <span><?= $label ?></span>
                            <span class="price-tag">R$ <?= number_format($val, 2, ',', '.') ?></span>
                        </div>
                    </label>
                    <?php endif; endforeach; ?>
                </div>
                <div class="col-md-6 ps-md-4 mt-4 mt-md-0">
                    <h5 class="fw-bold mb-3">Dados da Reserva</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">VEÍCULO</label>
                        <select name="car_id" class="form-select" required>
                            <?php foreach($my_cars as $car): ?>
                                <option value="<?= $car['id'] ?>"><?= $car['brand'] ?> <?= $car['model'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">DATA E HORA</label>
                        <input type="datetime-local" name="booking_date" class="form-control" min="<?= $min_datetime ?>" required>
                    </div>
                    <div class="p-3 bg-dark text-white rounded-3 mb-4 d-flex justify-content-between align-items-center">
                        <span class="small opacity-75">VALOR TOTAL</span>
                        <h3 class="mb-0 fw-bold" id="total_display" style="color: #D4AF37;">R$ 0,00</h3>
                    </div>
                    <button type="submit" class="btn btn-confirm w-100">CONFIRMAR AGENDAMENTO</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.service-check').forEach(input => {
        input.addEventListener('change', () => {
            let total = 0;
            document.querySelectorAll('.service-check:checked').forEach(c => {
                total += parseFloat(c.dataset.price);
            });
            document.getElementById('total_display').innerText = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('total_price_hidden').value = total;
        });
    });
</script>
</body>
</html>

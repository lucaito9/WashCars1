<?php
// index.php - Dark Edition
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();
require 'db.php';

// Busca lava jatos
$stmt = $pdo->query("SELECT * FROM car_washes");
$car_washes = $stmt->fetchAll();

// Imagens Premium de Carros
$stock_images = [
    "https://images.pexels.com/photos/100656/pexels-photo-100656.jpeg?auto=compress&cs=tinysrgb&w=600", // BMW Dark
    "https://images.pexels.com/photos/3354648/pexels-photo-3354648.jpeg?auto=compress&cs=tinysrgb&w=600", // Interior Luxo
    "https://images.pexels.com/photos/253905/pexels-photo-253905.jpeg?auto=compress&cs=tinysrgb&w=600", // Lavagem Detalhada
    "https://images.pexels.com/photos/1402787/pexels-photo-1402787.jpeg?auto=compress&cs=tinysrgb&w=600", // Audi R8
    "https://images.pexels.com/photos/3972755/pexels-photo-3972755.jpeg?auto=compress&cs=tinysrgb&w=600"  // Espuma
];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wash Cars - Premium Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="index.php">
                <i class="bi bi-speedometer2 text-primary"></i> Wash Cars
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item d-flex align-items-center me-3">
                            <span class="text-white-50 small me-1">Olá,</span>
                            <span class="fw-bold text-white"><?= explode(' ', $_SESSION['user_name'])[0] ?></span>
                        </li>
                        
                        <?php if($_SESSION['user_role'] == 'company'): ?>
                            <li class="nav-item"><a href="company_dashboard.php" class="nav-link text-info">Painel Empresa</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a href="my_cars.php" class="nav-link">Meus Carros</a></li>
                            <li class="nav-item"><a href="my_appointments.php" class="nav-link">Meus Pedidos</a></li>
                        <?php endif; ?>
                        
                        <li class="nav-item"><a href="logout.php" class="btn btn-outline-danger btn-sm ms-3">Sair</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a href="auth.php" class="btn btn-primary ms-2 px-4 rounded-pill fw-bold">Entrar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section text-center">
        <div class="container">
            <span class="badge bg-primary px-3 py-2 mb-3 rounded-pill">Novo Sistema 2.0</span>
            <h1 class="display-3 fw-bold text-white mb-3">Estética Automotiva <br> de Alto Nível</h1>
            <p class="lead text-white-50 mb-5 mx-auto" style="max-width: 600px;">
                Conectamos você aos melhores estúdios de detalhamento e lavagem da região. Agende com precisão e qualidade.
            </p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="auth.php" class="btn btn-lg btn-light px-5 py-3 fw-bold rounded-pill shadow">Começar Agora</a>
            <?php else: ?>
                <a href="#lista" class="btn btn-lg btn-primary px-5 py-3 fw-bold rounded-pill shadow">Ver Lava Jatos</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container mb-5 mt-5" id="lista">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="fw-bold m-0">Parceiros Disponíveis</h2>
            <span class="text-muted small">Mostrando <?= count($car_washes) ?> unidades</span>
        </div>

        <div class="row">
            <?php 
            if(count($car_washes) == 0): ?>
                <div class="col-12 py-5 text-center">
                    <i class="bi bi-cone-striped display-1 text-secondary"></i>
                    <p class="mt-3 text-muted">Nenhum lava jato cadastrado ainda.</p>
                </div>
            <?php endif; 
            
            foreach($car_washes as $index => $wash): 
                $img_url = $stock_images[$wash['id'] % 5]; 
            ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div style="height: 220px; overflow: hidden; position: relative;">
                         <img src="<?= $img_url ?>" class="w-100 h-100" style="object-fit: cover;" alt="Lava Jato">
                         <div style="position: absolute; top: 15px; right: 15px;">
                             <span class="badge bg-success shadow">Aberto</span>
                         </div>
                    </div>
                    
                    <div class="card-body d-flex flex-column p-4">
                        <h4 class="card-title fw-bold mb-1"><?= htmlspecialchars($wash['name']) ?></h4>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-geo-alt-fill text-primary"></i> <?= htmlspecialchars($wash['address']) ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4 mt-2 p-3 rounded" style="background: rgba(0,0,0,0.3);">
                            <div class="text-center">
                                <small class="text-white-50 d-block" style="font-size: 0.7rem;">SIMPLES</small>
                                <span class="fw-bold text-white">R$ <?= number_format($wash['price_simple'], 0, ',', '.') ?></span>
                            </div>
                            <div class="vr bg-secondary"></div>
                            <div class="text-center">
                                <small class="text-white-50 d-block" style="font-size: 0.7rem;">COMPLETA</small>
                                <span class="fw-bold text-warning">R$ <?= number_format($wash['price_complete'], 0, ',', '.') ?></span>
                            </div>
                        </div>

                        <div class="mt-auto d-grid gap-2">
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'client'): ?>
                                <a href="booking.php?wash_id=<?= $wash['id'] ?>&type=Simples" class="btn btn-outline-light btn-sm">Agendar Simples</a>
                                <a href="booking.php?wash_id=<?= $wash['id'] ?>&type=Completa" class="btn btn-primary btn-sm">Agendar Completa</a>
                            <?php elseif(isset($_SESSION['user_id'])): ?>
                                <button disabled class="btn btn-dark border-secondary w-100">Modo Empresa</button>
                            <?php else: ?>
                                <a href="auth.php" class="btn btn-primary w-100">Entrar para Agendar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="text-center py-5 border-top border-secondary text-muted mt-5" style="background: #0b0c0e;">
        <small>&copy; 2025 Wash Cars Inc. • Developed for Performance</small>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'client') { 
    header("Location: auth.php"); 
    exit; 
}

// ==========================================
// PROCESSAMENTO DOS FORMULÁRIOS
// ==========================================

// 1. Atualizar dados do cliente (NOVO)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['phone']);
    
    // Se o cliente digitou uma nova senha, atualiza com a senha. Se não, atualiza só os outros dados.
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $password, $_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
    }
    
    $_SESSION['user_name'] = $name; // Atualiza o nome na sessão atual
    header("Location: index.php");
    exit;
}

// 2. Cadastro de novo veículo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_car'])) {
    $stmt = $pdo->prepare("INSERT INTO user_cars (user_id, brand, model, plate) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['brand'], $_POST['model'], $_POST['plate']]);
    header("Location: index.php");
    exit;
}

// ==========================================
// BUSCA DE DADOS PARA A TELA
// ==========================================

// Busca os dados atuais do usuário para preencher o formulário
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION['user_id']]);
$currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Busca os carros do cliente
$stmt = $pdo->prepare("SELECT * FROM user_cars WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca os lava-jatos
$washes = $pdo->query("SELECT * FROM car_washes")->fetchAll(PDO::FETCH_ASSOC);

// GALERIA OTIMIZADA E LEVE
$luxury_images = [
    'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&q=80&w=600',
    'https://images.unsplash.com/photo-1503376760367-158a183d2c88?auto=format&fit=crop&q=80&w=600',
    'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&q=80&w=600',
    'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&q=80&w=600',
    'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&q=80&w=600',
    'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&q=80&w=600',
    'https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?auto=format&fit=crop&q=80&w=600',
    'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?auto=format&fit=crop&q=80&w=600'
];

$fallback_img = "https://placehold.co/600x400/111111/D4AF37/png?text=WASH+CARS";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Wash Cars - Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background-color: #000 !important; }
        .wash-card { transition: all 0.4s ease; border-radius: 20px; overflow: hidden; }
        .wash-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
        .hero-banner { 
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1601362840469-51e4d8d58785?auto=format&fit=crop&q=80&w=1200') center/cover;
            padding: 100px 0; border-radius: 25px; margin-bottom: 40px; color: white; text-shadow: 2px 2px 10px rgba(0,0,0,1);
        }
        .frase-dourada {
            color: #D4AF37; 
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .card-img-top { height: 220px; object-fit: cover; filter: brightness(0.9); transition: 0.5s; background-color: #111; }
        .wash-card:hover .card-img-top { filter: brightness(1.1); }
        .btn-view { background: #000; color: #fff; border: 2px solid #D4AF37; transition: 0.3s; }
        .btn-view:hover { background: #D4AF37; color: #000; border-color: #000; }
    </style>
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-dark mb-4 shadow-lg py-3">
        <div class="container">
            <span class="navbar-brand fw-bold fs-2"><i class="bi bi-droplet-half text-danger"></i> WASH <span class="text-danger">CARS</span></span>
            <div>
                <button class="btn btn-outline-light btn-sm me-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-person-circle me-1"></i> MEUS DADOS
                </button>
                <button class="btn btn-warning btn-sm me-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addCarModal">
                    <i class="bi bi-car-front-fill me-1"></i> MEUS VEÍCULOS
                </button>
                <a href="logout.php" class="btn btn-danger btn-sm fw-bold shadow-sm">SAIR</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="hero-banner text-center shadow-lg">
            <h1 class="fw-bold display-3">ESTÉTICA AUTOMOTIVA</h1>
            <p class="lead fw-bold">O cuidado que o seu carro de luxo merece, agora ao seu alcance.</p>
        </div>

        <div class="row">
            <?php foreach($washes as $w): ?>
            <?php 
                $seed = abs(crc32($w['name'] . $w['id']));
                $img_index = $seed % count($luxury_images);
                $current_img = $luxury_images[$img_index];
            ?>
            <div class="col-md-4 mb-5">
                <div class="card border-0 shadow h-100 wash-card">
                    <img src="<?= $current_img ?>" loading="lazy" class="card-img-top" alt="Carro de Luxo" onerror="this.onerror=null; this.src='<?= $fallback_img ?>';">
                    <div class="card-body text-center d-flex flex-column p-4">
                        <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($w['name']) ?></h3>
                        <p class="text-muted small mb-3 text-uppercase"><i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($w['address'] ?? 'Premium Location') ?></p>
                        
                        <div class="mt-auto">
                            <p class="fst-italic frase-dourada mb-4">
                                "<?= htmlspecialchars($w['description'] ?: 'O BRILHO DA PERFEIÇÃO.') ?>"
                            </p>
                            <a href="booking.php?wash_id=<?= $w['id'] ?>" class="btn btn-view w-100 fw-bold py-3 rounded-pill shadow-sm">RESERVAR AGORA</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-dark text-white border-0 py-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill me-2 text-warning"></i> MEUS DADOS</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" class="bg-light p-4 rounded-4 border shadow-sm">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Nome Completo</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($currentUser['name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Telefone / WhatsApp</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" required>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Nova Senha (opcional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Deixe em branco para manter a atual">
                        </div>

                        <button type="submit" class="btn btn-dark fw-bold w-100 py-3 mt-2 border-warning">SALVAR ALTERAÇÕES</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCarModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header bg-dark text-white border-0 py-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-car-front me-2 text-warning"></i> GARAGEM DO CLIENTE</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" class="mb-4 bg-light p-4 rounded-4 border shadow-sm">
                        <h6 class="fw-bold mb-3 text-uppercase text-muted">Novo Registro</h6>
                        <input type="hidden" name="add_car" value="1">
                        <div class="mb-2"><input type="text" name="brand" placeholder="Marca (Ex: BMW)" class="form-control" required></div>
                        <div class="mb-2"><input type="text" name="model" placeholder="Modelo (Ex: M3 Competition)" class="form-control" required></div>
                        <div class="mb-3"><input type="text" name="plate" placeholder="Placa (Ex: LUX-2024)" class="form-control" required></div>
                        <button type="submit" class="btn btn-dark fw-bold w-100 py-2 border-warning">ADICIONAR À GARAGEM</button>
                    </form>

                    <h6 class="fw-bold mb-3 text-muted text-uppercase">Meus Veículos Atuais:</h6>
                    <div class="list-group shadow-sm">
                        <?php if(empty($cars)): ?>
                            <div class="text-center py-3 text-muted">Sua garagem está vazia.</div>
                        <?php else: ?>
                            <?php foreach($cars as $c): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-start-0 border-end-0 py-3">
                                    <div>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($c['model']) ?></span><br>
                                        <small class="text-muted"><?= htmlspecialchars($c['brand']) ?></small>
                                    </div>
                                    <span class="badge bg-dark rounded-pill border border-warning px-3"><?= htmlspecialchars($c['plate']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

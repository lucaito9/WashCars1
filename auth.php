<?php
session_start();
require 'db.php';

// --- AUTO-REPARO: Cria a coluna phone se ela não existir na tabela users ---
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS phone VARCHAR(20)");
} catch (Exception $e) {
    // Se o banco não suportar IF NOT EXISTS, tentamos o método tradicional
    try { $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20)"); } catch (Exception $f) {}
}

$error = '';
$success = '';

// --- LÓGICA DE REGISTO ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role']; // 'client' ou 'company'

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $password, $role]);
        $success = "Conta criada com sucesso! Faça o login abaixo.";
    } catch (PDOException $e) {
        $error = "Erro: Este e-mail já está em uso.";
    }
}

// --- LÓGICA DE LOGIN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] == 'company') {
            header("Location: company_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "E-mail ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso - Wash Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .auth-container { max-width: 400px; margin: 50px auto; }
        .card { border: none; border-radius: 0; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .nav-tabs .nav-link { color: #666; border: none; font-weight: bold; }
        .nav-tabs .nav-link.active { color: #D5001C; border-bottom: 3px solid #D5001C; background: none; }
        .btn-danger { background-color: #D5001C; border: none; border-radius: 0; font-weight: bold; padding: 12px; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="text-center mb-4">
        <h2 class="fw-bold">WASH <span class="text-danger">CARS</span></h2>
        <p class="text-muted">Gestão Inteligente de Lavagens</p>
    </div>

    <?php if($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>

    <div class="card">
        <div class="card-body p-4">
            <ul class="nav nav-tabs mb-4 justify-content-center" id="authTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button">LOGIN</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button">CADASTRO</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="login">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">E-MAIL</label>
                            <input type="email" name="email" class="form-control shadow-sm" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">SENHA</label>
                            <input type="password" name="password" class="form-control shadow-sm" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-danger w-100">ENTRAR NO SISTEMA</button>
                    </form>
                </div>

                <div class="tab-pane fade" id="register">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">NOME COMPLETO</label>
                            <input type="text" name="name" class="form-control shadow-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">E-MAIL</label>
                            <input type="email" name="email" class="form-control shadow-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">WHATSAPP (COM DDD)</label>
                            <input type="text" name="phone" class="form-control shadow-sm" placeholder="Ex: 11999999999" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">SENHA</label>
                            <input type="password" name="password" class="form-control shadow-sm" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">EU SOU:</label>
                            <select name="role" class="form-select shadow-sm">
                                <option value="client">Cliente (Quero lavar meu carro)</option>
                                <option value="company">Lava Jato (Quero gerir agendamentos)</option>
                            </select>
                        </div>
                        <button type="submit" name="register" class="btn btn-danger w-100">CRIAR MINHA CONTA</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
ob_start(); 
session_start();
require 'db.php';

$message = '';

// --- LÓGICA DE REGISTRO ---
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $message = "Erro: E-mail já cadastrado!";
    } else {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$name, $email, $password, $role]);
        $message = "Sucesso: Conta criada! Faça login abaixo.";
    }
}

// --- LÓGICA DE LOGIN ---
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];

        $destiny = ($user['role'] == 'company') ? 'company_dashboard.php' : 'index.php';
        header("Location: $destiny");
        exit;
    } else {
        $message = "Erro: E-mail ou senha incorretos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Wash Cars - Acesso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #121212; color: white; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .box { background: #1e1e1e; padding: 30px; border-radius: 10px; width: 100%; max-width: 400px; border: 1px solid #333; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="box">
        <h2 class="text-center">Wash Cars</h2>
        <p class="text-center text-muted"><?= $message ?></p>

        <div id="login-box">
            <form method="POST">
                <input type="email" name="email" class="form-control mb-2" placeholder="E-mail" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="Senha" required>
                <button type="submit" name="login" class="btn btn-primary w-100">Entrar</button>
            </form>
            <button class="btn btn-link w-100 mt-2 text-info" onclick="toggle()">Criar uma conta</button>
        </div>

        <div id="register-box" class="hidden">
            <form method="POST">
                <input type="text" name="name" class="form-control mb-2" placeholder="Nome Completo" required>
                <input type="email" name="email" class="form-control mb-2" placeholder="E-mail" required>
                <input type="password" name="password" class="form-control mb-2" placeholder="Senha" required>
                <select name="role" class="form-control mb-2">
                    <option value="client">Cliente</option>
                    <option value="company">Empresa (Dono)</option>
                </select>
                <button type="submit" name="register" class="btn btn-success w-100">Cadastrar</button>
            </form>
            <button class="btn btn-link w-100 mt-2 text-info" onclick="toggle()">Já tenho conta</button>
        </div>
    </div>

    <script>
        function toggle() {
            document.getElementById('login-box').classList.toggle('hidden');
            document.getElementById('register-box').classList.toggle('hidden');
        }
    </script>
</body>
</html>
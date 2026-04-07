<?php
// MODO DETETIVE LIGADO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

// Segurança: Se não estiver logado, manda pro login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Sua sessão expirou. Faça login novamente.'); window.location.href='auth.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Coleta os dados que vieram do formulário
    $user_id     = $_SESSION['user_id'];
    $car_wash_id = $_POST['wash_id'] ?? null; // AQUI ESTÁ O SEGREDO: Pega o ID da loja correta!
    $data_base   = $_POST['data_base'] ?? null;
    $hora        = $_POST['hora_selecionada'] ?? null;
    $servico     = $_POST['servico'] ?? null;
    $car_id      = $_POST['veiculo_id'] ?? null;

    // Validação
    if (!$car_wash_id || !$data_base || !$hora || !$servico || !$car_id) {
        echo "<script>alert('Por favor, preencha todos os campos do agendamento.'); window.history.back();</script>";
        exit;
    }

    // 2. Formata a data para o padrão do MySQL (YYYY-MM-DD HH:MM:SS)
    $appointment_date = $data_base . " " . $hora . ":00";

    try {
        // 3. Verifica se o horário já não foi pego por alguém NESTA loja específica
        $check = $pdo->prepare("SELECT id FROM appointments WHERE appointment_date = ? AND car_wash_id = ? AND status != 'Cancelado'");
        $check->execute([$appointment_date, $car_wash_id]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('Atenção: Este horário acabou de ser reservado nesta loja. Escolha outro.'); window.history.back();</script>";
            exit;
        }

        // 4. Prepara a inserção vinculando o cliente, o carro e a LOJA CORRETA
        $sql = "INSERT INTO appointments (user_id, car_wash_id, car_id, appointment_date, status) 
                VALUES (?, ?, ?, ?, 'Pendente')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $car_wash_id, $car_id, $appointment_date]);

        // 5. Sucesso! Redireciona para os agendamentos do cliente
        echo "<script>alert('Agendamento realizado com sucesso!'); window.location.href='my_appointments.php';</script>";
        exit;

    } catch (PDOException $e) {
        die("Erro no banco de dados: " . $e->getMessage());
    }
} else {
    // Se tentarem acessar direto pela URL
    header("Location: index.php");
    exit;
}
?>
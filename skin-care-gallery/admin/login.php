<?php

if (headers_sent($arquivo, $linha)) {
    error_log("SAIDA ANTES DO LOGIN: $arquivo na linha $linha");
} else {
    error_log("LOGIN INICIADO SEM SAIDA ANTERIOR");
}

// Configurações de segurança da sessão
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

require_once __DIR__ . '/../config/conexao.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT id, usuario, senha
         FROM administradores
         WHERE usuario = :usuario
         LIMIT 1"
    );

    $stmt->execute([':usuario' => $usuario]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($senha, $admin['senha'])) {

        session_regenerate_id(true);

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_usuario'] = $admin['usuario'];

        header('Location: index.php');
        exit;

    } else {
        $erro = 'Usuário ou senha incorretos.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Administrativo | Skin Care Gallery</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5dce5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login {
            background: white;
            padding: 35px;
            border-radius: 12px;
            width: 320px;
            box-shadow: 0 4px 20px #00000015;
        }

        h1 {
            text-align: center;
            font-size: 24px;
        }

        input, button {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            box-sizing: border-box;
        }

        button {
            background: #d98eaa;
            color: white;
            border: none;
            cursor: pointer;
        }

        .erro {
            color: #b00020;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="login">

    <h1>Área administrativa</h1>
    <p>Skin Care Gallery</p>

    <?php if ($erro): ?>
        <p class="erro">
            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <input
            type="text"
            name="usuario"
            placeholder="Usuário"
            autocomplete="username"
            required
        >

        <input
            type="password"
            name="senha"
            placeholder="Senha"
            autocomplete="current-password"
            required
        >

        <button type="submit">Entrar</button>

    </form>

</div>

</body>
</html>

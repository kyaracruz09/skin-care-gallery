<?php

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/skin-care-gallery/admin/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Verifica se existe uma administradora autenticada
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Painel Administrativo | Skin Care Gallery</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9edf1;
            margin: 0;
        }

        header {
            background: #d98eaa;
            color: white;
            padding: 25px;
            text-align: center;
        }

        main {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .painel {
            background: white;
            padding: 30px;
            border-radius: 12px;
        }

        a {
            display: inline-block;
            padding: 12px 20px;
            margin: 10px 5px 0 0;
            background: #d98eaa;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<header>
    <h1>Skin Care Gallery</h1>
    <p>Painel Administrativo</p>
</header>

<main>
    <div class="painel">

        <h2>
            Bem-vinda,
            <?= htmlspecialchars($_SESSION['admin_usuario'], ENT_QUOTES, 'UTF-8') ?>!
        </h2>

        <p>Você está na área administrativa da loja.</p>

        <a href="produtos.php">Gerenciar produtos</a>

        <a href="logout.php">Sair da conta</a>

    </div>
</main>

</body>
</html>

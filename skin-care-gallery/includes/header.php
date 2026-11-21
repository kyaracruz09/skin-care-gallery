<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$quantidadeCarrinho = 0;

if (isset($_SESSION['carrinho'])) {
    foreach ($_SESSION['carrinho'] as $quantidade) {
        $quantidadeCarrinho += $quantidade;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Skin Care Gallery</title>

    <link rel="stylesheet" href="/skin-care-gallery/assets/css/style.css">
</head>

<body>

<div class="aviso">
    SUA ROTINA DE CUIDADOS COMEÇA AQUI
</div>

<header class="cabecalho">

    <a href="index.php" class="logo">
        Skin Care <span>Gallery</span>
    </a>

    <nav>
        <a href="index.php">Início</a>
        <a href="catalogo.php">Produtos</a>
        <a href="login.php">Minha conta</a>
        <a href="carrinho.php">
            Carrinho (<?= $quantidadeCarrinho ?>)
        </a>
    </nav>

</header>
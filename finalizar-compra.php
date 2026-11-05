<?php
require_once "config/conexao.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['carrinho'])) {
    header("Location: carrinho.php");
    exit;
}

$ids = array_keys($_SESSION['carrinho']);
$marcadores = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("
    SELECT * FROM produtos
    WHERE id IN ($marcadores)
");

$stmt->execute($ids);
$produtos = $stmt->fetchAll();

$total = 0;

foreach ($produtos as $produto) {
    $total += (float) $produto['preco']
        * $_SESSION['carrinho'][$produto['id']];
}

try {

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO pedidos (cliente_id, total)
        VALUES (?, ?)
    ");

    $stmt->execute([$_SESSION['cliente_id'], $total]);

    $pedidoId = $pdo->lastInsertId();

    $stmtItem = $pdo->prepare("
        INSERT INTO itens_pedido
        (pedido_id, produto_id, quantidade, preco)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($produtos as $produto) {

        $stmtItem->execute([
            $pedidoId,
            $produto['id'],
            $_SESSION['carrinho'][$produto['id']],
            $produto['preco']
        ]);
    }

    $pdo->commit();

    unset($_SESSION['carrinho']);

    $sucesso = true;

} catch (Exception $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $sucesso = false;
}

include "includes/header.php";
?>

<main class="secao">

    <?php if ($sucesso): ?>

        <h1>Pedido realizado!</h1>

        <p>
            Seu pedido foi registrado com sucesso.
            Número do pedido: <?= $pedidoId ?>
        </p>

        <a href="index.php" class="botao">
            Voltar ao início
        </a>

    <?php else: ?>

        <h1>Não foi possível registrar seu pedido.</h1>

        <a href="carrinho.php" class="botao">
            Voltar ao carrinho
        </a>

    <?php endif; ?>

</main>

<?php include "includes/footer.php"; ?>
<?php
require_once "config/conexao.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['adicionar'])) {

    $id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

    if ($id && $quantidade && $quantidade > 0 && $quantidade <= 20) {

        $stmt = $pdo->prepare("SELECT id FROM produtos WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->fetch()) {
            $_SESSION['carrinho'][$id] =
                ($_SESSION['carrinho'][$id] ?? 0) + $quantidade;
        }
    }

    header("Location: carrinho.php");
    exit;
}

if (isset($_GET['remover'])) {

    $id = filter_input(INPUT_GET, 'remover', FILTER_VALIDATE_INT);

    if ($id) {
        unset($_SESSION['carrinho'][$id]);
    }

    header("Location: carrinho.php");
    exit;
}

$itens = [];
$total = 0;

if (!empty($_SESSION['carrinho'])) {

    $ids = array_keys($_SESSION['carrinho']);
    $marcadores = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("
        SELECT * FROM produtos
        WHERE id IN ($marcadores)
    ");

    $stmt->execute($ids);
    $itens = $stmt->fetchAll();
}

include "includes/header.php";
?>

<main class="secao">

    <h1 class="titulo-secao">Meu carrinho</h1>

    <?php if (empty($itens)): ?>

        <p>Seu carrinho está vazio.</p>

        <a href="catalogo.php" class="botao">
            Continuar comprando
        </a>

    <?php else: ?>

        <table class="tabela-carrinho">

            <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
                <th>Ação</th>
            </tr>

            <?php foreach ($itens as $item): ?>

                <?php
                $quantidade = $_SESSION['carrinho'][$item['id']];
                $preco = (float) $item['preco'];
                $subtotal = $preco * $quantidade;
                $total += $subtotal;
                ?>

                <tr>

                    <td><?= htmlspecialchars($item['nome']) ?></td>

                    <td>
                        R$ <?= number_format($preco, 2, ',', '.') ?>
                    </td>

                    <td><?= $quantidade ?></td>

                    <td>
                        R$ <?= number_format($subtotal, 2, ',', '.') ?>
                    </td>

                    <td>
                        <a href="carrinho.php?remover=<?= $item['id'] ?>">
                            Remover
                        </a>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

        <h2 style="margin-top:25px;">
            Total: R$ <?= number_format($total, 2, ',', '.') ?>
        </h2>

        <a href="finalizar-compra.php" class="botao">
            Finalizar compra
        </a>

    <?php endif; ?>

</main>

<?php include "includes/footer.php"; ?>
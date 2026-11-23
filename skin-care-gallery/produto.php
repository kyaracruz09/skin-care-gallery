<?php
require_once "config/conexao.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die("Produto inválido.");
}

$stmt = $pdo->prepare("
    SELECT
        p.*,
        m.nome AS marca,
        c.nome AS categoria
    FROM produtos p
    INNER JOIN marcas m ON p.marca_id = m.id
    INNER JOIN categorias c ON p.categoria_id = c.id
    WHERE p.id = ?
");

$stmt->execute([$id]);

$produto = $stmt->fetch();

if (!$produto) {
    die("Produto não encontrado.");
}

include "includes/header.php";
?>

<main class="secao">

    <div class="detalhe">

        <img
            src="<?= htmlspecialchars($produto['imagem']) ?>"
            alt="<?= htmlspecialchars($produto['nome']) ?>"
        >

        <div>

            <p><?= htmlspecialchars($produto['marca']) ?></p>

            <h1><?= htmlspecialchars($produto['nome']) ?></h1>

            <p>
                Categoria:
                <?= htmlspecialchars($produto['categoria']) ?>
            </p>

            <p>
                <?= nl2br(htmlspecialchars($produto['descricao'] ?? 'Descrição não cadastrada.')) ?>
            </p>

            <?php if ($produto['preco'] !== null): ?>
                <h2>
                    R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                </h2>
            <?php endif; ?>

            <form action="carrinho.php" method="POST">

                <input type="hidden" name="produto_id"
                       value="<?= $produto['id'] ?>">

                <label for="quantidade">Quantidade:</label>

                <input
                    type="number"
                    id="quantidade"
                    name="quantidade"
                    value="1"
                    min="1"
                    max="20"
                    required
                >

                <button type="submit" name="adicionar"
                        class="botao">
                    Adicionar ao carrinho
                </button>

            </form>

        </div>

    </div>

</main>

<?php include "includes/footer.php"; ?>
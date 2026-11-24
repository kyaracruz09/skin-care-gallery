<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/conexao.php";

$marca = $_GET['marca'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$pesquisa = trim($_GET['pesquisa'] ?? '');

$sql = "
    SELECT
        p.*,
        m.nome AS marca,
        c.nome AS categoria
    FROM produtos p
    INNER JOIN marcas m ON p.marca_id = m.id
    INNER JOIN categorias c ON p.categoria_id = c.id
    WHERE 1=1
";

$params = [];

if ($marca !== '') {
    $sql .= " AND m.nome = :marca";
    $params[':marca'] = $marca;
}

if ($categoria !== '') {
    $sql .= " AND c.nome = :categoria";
    $params[':categoria'] = $categoria;
}

if ($pesquisa !== '') {
    $sql .= " AND p.nome LIKE :pesquisa";
    $params[':pesquisa'] = "%$pesquisa%";
}

$sql .= " ORDER BY p.nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$produtos = $stmt->fetchAll();

$categorias = $pdo->query(
    "SELECT nome FROM categorias ORDER BY nome"
)->fetchAll();

include "includes/header.php";
?>

<main class="secao">

    <h1 class="titulo-secao">Nosso catálogo</h1>

    <form method="GET" class="busca">

        <?php if ($marca !== ''): ?>
            <input type="hidden" name="marca"
                   value="<?= htmlspecialchars($marca) ?>">
        <?php endif; ?>

        <?php if ($categoria !== ''): ?>
            <input type="hidden" name="categoria"
                   value="<?= htmlspecialchars($categoria) ?>">
        <?php endif; ?>

        <input
            type="text"
            name="pesquisa"
            placeholder="Pesquise um produto..."
            value="<?= htmlspecialchars($pesquisa) ?>"
        >

        <button class="botao" type="submit">
            Pesquisar
        </button>

    </form>

    <h3 style="text-align:center;">Marcas</h3>

    <div class="filtros">

        <a href="catalogo.php"
           class="<?= $marca === '' ? 'ativo' : '' ?>">
            Todos os produtos
        </a>

        <?php foreach (['Principia', 'CeraVe', 'Creamy'] as $nomeMarca): ?>

            <a href="catalogo.php?marca=<?= urlencode($nomeMarca) ?>"
               class="<?= $marca === $nomeMarca ? 'ativo' : '' ?>">
                <?= htmlspecialchars($nomeMarca) ?>
            </a>

        <?php endforeach; ?>

    </div>

    <h3 style="text-align:center;">Tipos de produtos</h3>

    <div class="filtros">

        <a href="catalogo.php<?= $marca ? '?marca='.urlencode($marca) : '' ?>"
           class="<?= $categoria === '' ? 'ativo' : '' ?>">
            Todos os tipos
        </a>

        <?php foreach ($categorias as $item): ?>

            <a href="catalogo.php?<?= $marca ? 'marca='.urlencode($marca).'&' : '' ?>categoria=<?= urlencode($item['nome']) ?>"
               class="<?= $categoria === $item['nome'] ? 'ativo' : '' ?>">
                <?= htmlspecialchars($item['nome']) ?>
            </a>

        <?php endforeach; ?>

    </div>

    <div class="produtos">

        <?php if (count($produtos) > 0): ?>

            <?php foreach ($produtos as $produto): ?>

                <article class="produto-card">

                    <img
                        src="<?= htmlspecialchars($produto['imagem']) ?>"
                        alt="<?= htmlspecialchars($produto['nome']) ?>"
                    >

                    <h3>
                        <?= htmlspecialchars($produto['nome']) ?>
                    </h3>

                    <p><?= htmlspecialchars($produto['marca']) ?></p>

                    <p><?= htmlspecialchars($produto['categoria']) ?></p>

                    <a href="produto.php?id=<?= $produto['id'] ?>"
                       class="botao">
                        Ver detalhes
                    </a>

                </article>

            <?php endforeach; ?>

        <?php else: ?>

            <p>Nenhum produto encontrado.</p>

        <?php endif; ?>

    </div>

</main>

<?php include "includes/footer.php"; ?>
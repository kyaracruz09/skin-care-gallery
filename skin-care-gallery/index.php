<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/conexao.php";

$stmt = $pdo->query("
    SELECT p.*, m.nome AS marca
    FROM produtos p
    INNER JOIN marcas m ON p.marca_id = m.id
    WHERE p.destaque = 1
    ORDER BY p.id DESC
    LIMIT 8
");

$produtos = $stmt->fetchAll();

include "includes/header.php";

?>

<main>

    <section class="banner">

        <div>
            <h1>Seu cuidado merece um espaço especial.</h1>

            <p>
                Descubra produtos de skincare,
                encontre seus favoritos e construa
                uma rotina de cuidados com a sua pele.
            </p>

            <a href="catalogo.php" class="botao">
                Explorar produtos
            </a>
        </div>

        <img src="assets/images/banners/banner.png"
             alt="Produtos de skincare">

    </section>

    <section class="secao">

        <h2 class="titulo-secao">Explore nossas marcas</h2>

        <div class="marcas">

            <a href="catalogo.php" class="marca-card">
                <h3>Todos os produtos</h3>
                <p>Conheça nosso catálogo completo</p>
            </a>

            <a href="catalogo.php?marca=Principia" class="marca-card">
                <h3>Principia</h3>
                <p>Explore a marca</p>
            </a>

            <a href="catalogo.php?marca=CeraVe" class="marca-card">
                <h3>CeraVe</h3>
                <p>Explore a marca</p>
            </a>

            <a href="catalogo.php?marca=Creamy" class="marca-card">
                <h3>Creamy</h3>
                <p>Explore a marca</p>
            </a>

        </div>

    </section>

    <section class="secao">

        <h2 class="titulo-secao">Produtos em destaque</h2>

        <div class="produtos">

            <?php foreach ($produtos as $produto): ?>

                <article class="produto-card">

                    <img
                        src="assets/images/produtos/<?= htmlspecialchars($produto['imagem']) ?>"
                        alt="<?= htmlspecialchars($produto['nome']) ?>"
                    >

                    <h3>
                        <?= htmlspecialchars($produto['nome']) ?>
                    </h3>

                    <p>
                        <?= htmlspecialchars($produto['marca']) ?>
                    </p>

                    <a href="produto.php?id=<?= $produto['id'] ?>" class="botao">
                        Ver produto
                    </a>

                </article>

            <?php endforeach; ?>

        </div>

        <div style="text-align:center; margin-top:30px;">
            <a href="catalogo.php" class="botao">
                Ver todos os produtos
            </a>
        </div>

    </section>

</main>

<?php include "includes/footer.php"; ?>
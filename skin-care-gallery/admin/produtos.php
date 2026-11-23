
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config/conexao.php';

$sql = "SELECT
            p.id,
            p.nome,
            p.descricao,
            p.imagem,
            p.preco,
            p.destaque,
            m.nome AS marca,
            c.nome AS categoria
        FROM produtos p
        LEFT JOIN marcas m ON p.marca_id = m.id
        LEFT JOIN categorias c ON p.categoria_id = c.id
        ORDER BY p.id DESC";

$stmt = $pdo->query($sql);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produtos | Skin Care Gallery</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #fff5f8;
            color: #5c414b;
        }

        header {
            background: #d98eaa;
            color: white;
            padding: 22px;
            text-align: center;
        }

        main {
            max-width: 1100px;
            margin: 35px auto;
            padding: 20px;
        }

        .topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .botao {
            background: #d98eaa;
            color: white;
            padding: 12px 18px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
        }

        .botao:hover {
            background: #bd718e;
        }

        .tabela-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #f0dce4;
        }

        th {
            background: #fbeaf0;
        }

        .vazio {
            text-align: center;
            padding: 35px;
        }

        .acoes a {
            color: #bd718e;
            margin-right: 10px;
            text-decoration: none;
        }

        .voltar {
            margin-top: 20px;
            display: inline-block;
            color: #bd718e;
        }
    </style>
</head>

<body>

<header>
    <h1>Skin Care Gallery</h1>
    <p>Gerenciamento de produtos</p>
</header>

<main>

    <div class="topo">
        <div>
            <h2>Produtos cadastrados</h2>
            <p>Gerencie os produtos da sua loja.</p>
        </div>

        <a href="cadastrar-produto.php" class="botao">
            + Novo produto
        </a>
    </div>

    <div class="tabela-container">

        <?php if (count($produtos) > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Preço</th>
                        <th>Marca</th>
                        <th>Categoria</th>
                        <th>Destaque</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($produtos as $produto): ?>

                    <tr>
                        <td>
                            <?= (int)$produto['id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($produto['nome']) ?>
                        </td>

                        <td>
                            <?php if ($produto['preco'] !== null): ?>
                                R$ <?= number_format(
                                    (float)$produto['preco'],
                                    2, ',', '.'
                                ) ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($produto['marca'] ?? 'Não informada') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($produto['categoria'] ?? 'Não informada') ?>
                        </td>

                        <td>
                            <?= $produto['destaque'] ? 'Sim' : 'Não' ?>
                        </td>

                        <td class="acoes">
                            <a href="editar-produto.php?id=<?= (int)$produto['id'] ?>">
                                Editar
                            </a>

                            <form
    action="excluir-produto.php"
    method="POST"
    style="display:inline;"
    onsubmit="return confirm('Tem certeza que deseja excluir este produto?');"
>
    <input
        type="hidden"
        name="id"
        value="<?= (int)$produto['id'] ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
    >

    <button
        type="submit"
        style="width:auto; margin:0; padding:0; background:none; color:#bd718e;"
    >
        Excluir
    </button>
</form>
                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        <?php else: ?>

            <div class="vazio">
                <h3>Nenhum produto cadastrado.</h3>
                <p>Comece adicionando o primeiro produto da loja!</p>
            </div>

        <?php endif; ?>

    </div>

    <a href="index.php" class="voltar">
        ← Voltar ao painel administrativo
    </a>

</main>

</body>
</html>

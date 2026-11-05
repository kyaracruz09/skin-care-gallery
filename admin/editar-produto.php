
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erro = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: produtos.php');
    exit;
}

// Buscar produto
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header('Location: produtos.php');
    exit;
}

// Buscar marcas e categorias
$marcas = $pdo->query(
    "SELECT id, nome FROM marcas ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$categorias = $pdo->query(
    "SELECT id, nome FROM categorias ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

// Processar edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $imagemNova = null;

    try {
        if (!hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token'] ?? ''
        )) {
            throw new RuntimeException('Solicitação inválida. Atualize a página.');
        }

        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $preco = str_replace(',', '.', trim($_POST['preco'] ?? ''));
        $marca_id = $_POST['marca_id'] ?? '';
        $categoria_id = $_POST['categoria_id'] ?? '';
        $destaque = isset($_POST['destaque']) ? 1 : 0;

        if (
            $nome === '' ||
            strlen($nome) > 150 ||
            !is_numeric($preco) ||
            (float)$preco <= 0 ||
            (float)$preco > 99999999 ||
            !ctype_digit((string)$marca_id) ||
            !ctype_digit((string)$categoria_id)
        ) {
            throw new RuntimeException('Verifique os campos obrigatórios.');
        }

        // Validar marca e categoria
        $stmt = $pdo->prepare("SELECT id FROM marcas WHERE id = ?");
        $stmt->execute([$marca_id]);

        if (!$stmt->fetch()) {
            throw new RuntimeException('Marca inválida.');
        }

        $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ?");
        $stmt->execute([$categoria_id]);

        if (!$stmt->fetch()) {
            throw new RuntimeException('Categoria inválida.');
        }

        $imagem = $produto['imagem'];

        // Verificar se uma nova imagem foi enviada
        if (isset($_FILES['imagem']) &&
            $_FILES['imagem']['error'] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Erro ao enviar a imagem.');
            }

            $arquivo = $_FILES['imagem'];

            if ($arquivo['size'] > 5 * 1024 * 1024) {
                throw new RuntimeException('A imagem deve ter no máximo 5 MB.');
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $tipo = $finfo->file($arquivo['tmp_name']);

            $formatos = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (
                !isset($formatos[$tipo]) ||
                getimagesize($arquivo['tmp_name']) === false
            ) {
                throw new RuntimeException('Formato de imagem inválido.');
            }

            $pasta = __DIR__ . '/../assets/images/produtos/';

            if (!is_dir($pasta) &&
                !mkdir($pasta, 0755, true)) {
                throw new RuntimeException('Não foi possível acessar a pasta de imagens.');
            }

            $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $formatos[$tipo];

            if (!move_uploaded_file(
                $arquivo['tmp_name'],
                $pasta . $nomeArquivo
            )) {
                throw new RuntimeException('Não foi possível salvar a nova imagem.');
            }

            $imagemNova = 'assets/images/produtos/' . $nomeArquivo;
            $imagem = $imagemNova;
        }

        // Atualizar produto
        $sql = "UPDATE produtos SET
                    nome = ?,
                    descricao = ?,
                    imagem = ?,
                    preco = ?,
                    marca_id = ?,
                    categoria_id = ?,
                    destaque = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $nome,
            $descricao,
            $imagem,
            $preco,
            $marca_id,
            $categoria_id,
            $destaque,
            $id
        ]);

        // Remover imagem anterior somente após atualizar o banco
        if ($imagemNova !== null) {
            $imagemAntiga = $produto['imagem'] ?? '';

            if (strpos($imagemAntiga, 'assets/images/produtos/') === 0) {
                $caminhoAntigo = realpath(
                    __DIR__ . '/../' . $imagemAntiga
                );

                $pastaProdutos = realpath(
                    __DIR__ . '/../assets/images/produtos/'
                );

                if (
                    $caminhoAntigo &&
                    $pastaProdutos &&
                    strpos(
                        $caminhoAntigo,
                        $pastaProdutos . DIRECTORY_SEPARATOR
                    ) === 0 &&
                    is_file($caminhoAntigo)
                ) {
                    unlink($caminhoAntigo);
                }
            }
        }

        header('Location: produtos.php?sucesso=editado');
        exit;

    } catch (Throwable $e) {

        if ($imagemNova !== null) {
            $caminho = __DIR__ . '/../' . $imagemNova;

            if (is_file($caminho)) {
                unlink($caminho);
            }
        }

        $erro = $e instanceof PDOException
            ? 'Não foi possível atualizar o produto.'
            : $e->getMessage();

        // Manter os dados preenchidos
        $produto['nome'] = $nome ?? $produto['nome'];
        $produto['descricao'] = $descricao ?? $produto['descricao'];
        $produto['preco'] = $preco ?? $produto['preco'];
        $produto['marca_id'] = $marca_id ?? $produto['marca_id'];
        $produto['categoria_id'] = $categoria_id ?? $produto['categoria_id'];
        $produto['destaque'] = $destaque ?? $produto['destaque'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar produto | Skin Care Gallery</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #fff5f8;
            color: #5c414b;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            padding: 35px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 20px #00000010;
        }

        h1 {
            color: #bd718e;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #e8cbd6;
            border-radius: 7px;
            font-size: 15px;
        }

        textarea {
            min-height: 110px;
        }

        .imagem-atual {
            max-width: 160px;
            max-height: 160px;
            object-fit: contain;
            display: block;
            margin-top: 12px;
            border-radius: 8px;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox input {
            width: auto;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #d98eaa;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .erro {
            background: #ffe3e3;
            color: #a32323;
            padding: 12px;
            border-radius: 7px;
        }

        .voltar {
            display: inline-block;
            margin-top: 20px;
            color: #bd718e;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Editar produto</h1>

    <p>Atualize as informações do produto selecionado.</p>

    <?php if ($erro): ?>
        <div class="erro">
            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
        >

        <label>Nome do produto *</label>
        <input
            type="text"
            name="nome"
            maxlength="150"
            value="<?= htmlspecialchars($produto['nome']) ?>"
            required
        >

        <label>Descrição</label>
        <textarea name="descricao"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>

        <label>Preço (R$) *</label>
        <input
            type="text"
            name="preco"
            value="<?= htmlspecialchars((string)$produto['preco']) ?>"
            required
        >

        <label>Imagem atual</label>

        <?php if (!empty($produto['imagem'])): ?>
            <img
                src="../<?= htmlspecialchars($produto['imagem']) ?>"
                alt="Imagem atual do produto"
                class="imagem-atual"
            >
        <?php else: ?>
            <p>Este produto não possui imagem.</p>
        <?php endif; ?>

        <label>Escolher outra imagem (opcional)</label>
        <input
            type="file"
            name="imagem"
            accept=".jpg,.jpeg,.png,.webp"
        >

        <label>Marca *</label>
        <select name="marca_id" required>

            <?php foreach ($marcas as $marca): ?>
                <option
                    value="<?= (int)$marca['id'] ?>"
                    <?= (string)$produto['marca_id'] === (string)$marca['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($marca['nome']) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <label>Categoria *</label>
        <select name="categoria_id" required>

            <?php foreach ($categorias as $categoria): ?>
                <option
                    value="<?= (int)$categoria['id'] ?>"
                    <?= (string)$produto['categoria_id'] === (string)$categoria['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($categoria['nome']) ?>
                </option>
            <?php endforeach; ?>

        </select>

        <label class="checkbox">
            <input
                type="checkbox"
                name="destaque"
                value="1"
                <?= $produto['destaque'] ? 'checked' : '' ?>
            >
            Produto em destaque
        </label>

        <button type="submit">Salvar alterações</button>

    </form>

    <a href="produtos.php" class="voltar">
        ← Voltar aos produtos
    </a>

</div>

</body>
</html>


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
$nome = '';
$descricao = '';
$preco = '';
$marca_id = '';
$categoria_id = '';
$destaque = 0;

// Buscar marcas e categorias
$marcas = $pdo->query(
    "SELECT id, nome FROM marcas ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$categorias = $pdo->query(
    "SELECT id, nome FROM categorias ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = str_replace(',', '.', trim($_POST['preco'] ?? ''));
    $marca_id = $_POST['marca_id'] ?? '';
    $categoria_id = $_POST['categoria_id'] ?? '';
    $destaque = isset($_POST['destaque']) ? 1 : 0;

    $imagemSalva = null;

    try {
        if (!hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token'] ?? ''
        )) {
            throw new RuntimeException('Solicitação inválida. Atualize a página.');
        }

        if (
            $nome === '' ||
            !is_numeric($preco) ||
            (float)$preco <= 0 ||
            !ctype_digit((string)$marca_id) ||
            !ctype_digit((string)$categoria_id)
        ) {
            throw new RuntimeException('Preencha corretamente todos os campos obrigatórios.');
        }

        if (!isset($_FILES['imagem']) ||
            $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Selecione uma imagem válida.');
        }

        // Verificar marca
        $stmt = $pdo->prepare(
            "SELECT id FROM marcas WHERE id = ?"
        );
        $stmt->execute([$marca_id]);

        if (!$stmt->fetch()) {
            throw new RuntimeException('Selecione uma marca válida.');
        }

        // Verificar categoria
        $stmt = $pdo->prepare(
            "SELECT id FROM categorias WHERE id = ?"
        );
        $stmt->execute([$categoria_id]);

        if (!$stmt->fetch()) {
            throw new RuntimeException('Selecione uma categoria válida.');
        }

        // Validar imagem
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
            throw new RuntimeException('Use uma imagem JPG, PNG ou WEBP.');
        }

        // Salvar imagem com nome aleatório
        $pasta = __DIR__ . '/../assets/images/produtos/';

        if (!is_dir($pasta) && !mkdir($pasta, 0755, true)) {
            throw new RuntimeException('Não foi possível criar a pasta de imagens.');
        }

        $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $formatos[$tipo];

        if (!move_uploaded_file(
            $arquivo['tmp_name'],
            $pasta . $nomeArquivo
        )) {
            throw new RuntimeException('Não foi possível salvar a imagem.');
        }

        $imagemSalva = 'assets/images/produtos/' . $nomeArquivo;

        // Inserir produto no banco
        $sql = "INSERT INTO produtos
                (nome, descricao, imagem, preco, marca_id, categoria_id, destaque)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $nome,
            $descricao,
            $imagemSalva,
            $preco,
            $marca_id,
            $categoria_id,
            $destaque
        ]);

        header('Location: produtos.php?sucesso=cadastrado');
        exit;

    } catch (Throwable $e) {

        if ($imagemSalva !== null &&
            is_file(__DIR__ . '/../' . $imagemSalva)) {
            unlink(__DIR__ . '/../' . $imagemSalva);
        }

        if ($e instanceof PDOException) {
            $erro = 'Não foi possível cadastrar o produto. Verifique os dados e tente novamente.';
        } else {
            $erro = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar produto | Skin Care Gallery</title>

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
            background: white;
            padding: 35px;
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
            resize: vertical;
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

        button:hover {
            background: #bd718e;
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

    <h1>Novo produto</h1>

    <p>Preencha as informações para adicionar um produto à loja.</p>

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
            value="<?= htmlspecialchars($nome) ?>"
            required
        >

        <label>Descrição</label>
        <textarea name="descricao"><?= htmlspecialchars($descricao) ?></textarea>

        <label>Preço (R$) *</label>
        <input
            type="text"
            name="preco"
            placeholder="Ex.: 59,90"
            value="<?= htmlspecialchars($preco) ?>"
            required
        >

        <label>Imagem do produto *</label>
        <input
            type="file"
            name="imagem"
            accept=".jpg,.jpeg,.png,.webp"
            required
        >

        <label>Marca *</label>
        <select name="marca_id" required>
            <option value="">Selecione uma marca</option>

            <?php foreach ($marcas as $marca): ?>
                <option
                    value="<?= (int)$marca['id'] ?>"
                    <?= (string)$marca_id === (string)$marca['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($marca['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Categoria *</label>
        <select name="categoria_id" required>
            <option value="">Selecione uma categoria</option>

            <?php foreach ($categorias as $categoria): ?>
                <option
                    value="<?= (int)$categoria['id'] ?>"
                    <?= (string)$categoria_id === (string)$categoria['id'] ? 'selected' : '' ?>
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
                <?= $destaque ? 'checked' : '' ?>
            >
            Produto em destaque
        </label>

        <button type="submit">Cadastrar produto</button>

    </form>

    <a href="produtos.php" class="voltar">
        ← Voltar aos produtos
    </a>

</div>

</body>
</html>

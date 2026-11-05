
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: produtos.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    exit('Solicitação inválida. Atualize a página e tente novamente.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: produtos.php?erro=id');
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT imagem FROM produtos WHERE id = ?"
    );

    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        header('Location: produtos.php?erro=nao_encontrado');
        exit;
    }

    // Excluir produto do banco
    $stmt = $pdo->prepare(
        "DELETE FROM produtos WHERE id = ?"
    );

    $stmt->execute([$id]);

    // Remover imagem somente se estiver na pasta de produtos
    $imagem = $produto['imagem'] ?? '';

    if (strpos($imagem, 'assets/images/produtos/') === 0) {

        $caminho = realpath(__DIR__ . '/../' . $imagem);
        $pasta = realpath(
            __DIR__ . '/../assets/images/produtos/'
        );

        if (
            $caminho &&
            $pasta &&
            strpos($caminho, $pasta . DIRECTORY_SEPARATOR) === 0 &&
            is_file($caminho)
        ) {
            unlink($caminho);
        }
    }

    header('Location: produtos.php?sucesso=excluido');
    exit;

} catch (PDOException $e) {

    header('Location: produtos.php?erro=banco');
    exit;
}

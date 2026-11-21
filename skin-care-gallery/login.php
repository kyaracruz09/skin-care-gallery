<?php
require_once "config/conexao.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $stmt = $pdo->prepare("
        SELECT * FROM clientes WHERE email = ?
    ");

    $stmt->execute([$email]);
    $cliente = $stmt->fetch();

    if ($cliente && password_verify($senha, $cliente['senha'])) {

        session_regenerate_id(true);

        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nome'] = $cliente['nome'];

        header("Location: index.php");
        exit;

    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}

include "includes/header.php";
?>

<main class="secao">

    <form method="POST" class="formulario">

        <h1>Entrar na minha conta</h1>

        <?php if (isset($_GET['cadastro'])): ?>
            <p class="mensagem">Cadastro realizado com sucesso!</p>
        <?php endif; ?>

        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Senha</label>
        <input type="password" name="senha" required>

        <button type="submit" class="botao">
            Entrar
        </button>

        <p style="margin-top:20px;">
            Não possui uma conta?
            <a href="cadastro.php">Cadastre-se</a>
        </p>

    </form>

</main>

<?php include "includes/footer.php"; ?>
<?php
require_once "config/conexao.php";

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($senha) < 8) {

        $erro = "Preencha os campos corretamente. A senha deve ter pelo menos 8 caracteres.";

    } else {

        try {
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO clientes (nome, email, senha)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([$nome, $email, $hash]);

            header("Location: login.php?cadastro=sucesso");
            exit;

        } catch (PDOException $e) {
            $erro = "Não foi possível realizar o cadastro. Verifique se o e-mail já está cadastrado.";
        }
    }
}

include "includes/header.php";
?>

<main class="secao">

    <form method="POST" class="formulario">

        <h1>Criar minha conta</h1>

        <?php if ($erro): ?>
            <p class="erro"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <label>Nome completo</label>
        <input type="text" name="nome" required>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Senha</label>
        <input type="password" name="senha"
               minlength="8" required>

        <button type="submit" class="botao">
            Cadastrar
        </button>

        <p style="margin-top:20px;">
            Já possui uma conta?
            <a href="login.php">Entrar</a>
        </p>

    </form>

</main>

<?php include "includes/footer.php"; ?>
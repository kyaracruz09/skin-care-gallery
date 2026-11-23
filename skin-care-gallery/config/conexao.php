
<?php

$host = getenv('DB_HOST');
$porta = getenv('DB_PORT');
$banco = getenv('DB_NAME');
$usuario = getenv('DB_USER');
$senha = getenv('DB_PASSWORD');

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/isrgrootx1.pem',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true
        ]
    );

} catch (PDOException $e) {
    error_log("Erro de conexão com o banco: " . $e->getMessage());
    http_response_code(500);
    exit("Não foi possível conectar ao banco de dados.");
}

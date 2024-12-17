<?php
/**
 * Primeira Tela
 *
 * Objetivo : 
 *            Exibir a Razão social e o nome da cidade da empresa
 *            Usuário continua conectado nessa empresa após selecioná-la
 */

// Mantém o Usuário conectado
session_start(); 
include("classes/db.php");
$db = new db();
$db->conectar();
$conn = $db->conexao();

// Enviando sessão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empresa_cidade'])) {

    $empresaCidade = $_POST['empresa_cidade'];
    list($empresaId, $cidadeId) = explode('-', $empresaCidade); 

    $_SESSION['empresa_id'] = $empresaId;
    $_SESSION['cidade_id'] = $cidadeId;

    header("Location: src/home.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/style_inicio.css">
    <title>Escolha da Empresa</title>
</head>
<body>
<main class="container">
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-buildings-fill top-icon" viewBox="0 0 16 16">
        <path d="M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zM2 11h1v1H2zm2 0h1v1H4zm-1 2v1H2v-1zm1 0h1v1H4zm9-10v1h-1V3zM8 5h1v1H8zm1 2v1H8V7zM8 9h1v1H8zm2 0h1v1h-1zm-1 2v1H8v-1zm1 0h1v1h-1zm3-2v1h-1V9zm-1 2h1v1h-1zm-2-4h1v1h-1zm3 0v1h-1V7zm-2-2v1h-1V5zm1 0h1v1h-1z"/>
    </svg>
    <h1>Escolha a Empresa</h1>
    <div id="ContainerLogin">
        <form action="" method="post">
            <!-- Razão Social e Cidades -->
            <select name="empresa_cidade" required>
                <option value="">Selecione a Empresa e Cidade</option>
                <?php
                $query = $conn->query("
                    SELECT 
                        emp.EMPRESA,
                        emp.RAZAO_SOCIAL,
                        cid.CIDADE,
                        cid.DESCRICAO_CIDADE AS NOME_CIDADE
                    FROM 
                        EMPRESA emp
                    INNER JOIN 
                        CIDADE cid ON emp.EMPRESA = cid.EMPRESA
                    ORDER BY emp.RAZAO_SOCIAL ASC, cid.DESCRICAO_CIDADE ASC
                ");
                $registros = $query->fetchAll(PDO::FETCH_ASSOC);

                foreach ($registros as $option) {
                ?>
                    <option value="<?php echo $option['EMPRESA'] . '-' . $option['CIDADE']; ?>">
                        <?php echo $option['RAZAO_SOCIAL'] . " - " . $option['NOME_CIDADE']; ?>
                    </option>
                <?php } ?>
            </select>
            <input type="submit" value="Entrar">
        </form>
    </div>
</main>
</body>
</html>

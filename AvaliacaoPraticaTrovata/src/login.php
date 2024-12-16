<?php
/**
 * Segunda Tela
 *
 * Objetivo : 
 *            Mostra apenas as cidades conectadas a essa empresa
 *            Usuario seleciona a cidade
 * 
 */


session_start();

include("../classes/db.php");
$db = new db();
$db->conectar();
$conn = $db->conexao();

// Caso não estiver conectado, volta para o index
if (!isset($_SESSION['empresa_id'])) {
    header("Location: ../index.php");
    exit;
}


// Sessions
$empresaId = $_SESSION['empresa_id'];


// razão social dessa empresa para mostrar na tela
$queryEmp = $conn->prepare("SELECT RAZAO_SOCIAL FROM empresa WHERE EMPRESA = :empresaId");
$queryEmp->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
$queryEmp->execute();
$empresa = $queryEmp->fetch(PDO::FETCH_ASSOC);
$razaoSocial = $empresa['RAZAO_SOCIAL'];



// Busca as cidades da empresa selecionada  
$queryCid = $conn->prepare("
    SELECT 
        c.CIDADE, 
        c.DESCRICAO_CIDADE 
    FROM 
        cidade c
    INNER JOIN 
        empresa e ON c.EMPRESA = e.EMPRESA
    WHERE 
        e.EMPRESA = :empresaId
");
$queryCid->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
$queryCid->execute();
$cidades = $queryCid->fetchAll(PDO::FETCH_ASSOC);


// Enviando id da cidade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cidade'])) {
    $_SESSION['cidade_id'] = $_POST['cidade'];

    header("Location: ../src/home.php");
    exit;
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/style_inicio.css">
    <title>Selecione a Cidade</title>
</head>
<body>
<main class="container">
    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-buildings-fill top-icon" viewBox="0 0 16 16">
        <path d="M15 .5a.5.5 0 0 0-.724-.447l-8 4A.5.5 0 0 0 6 4.5v3.14L.342 9.526A.5.5 0 0 0 0 10v5.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V14h1v1.5a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zM2 11h1v1H2zm2 0h1v1H4zm-1 2v1H2v-1zm1 0h1v1H4zm9-10v1h-1V3zM8 5h1v1H8zm1 2v1H8V7zM8 9h1v1H8zm2 0h1v1h-1zm-1 2v1H8v-1zm1 0h1v1h-1zm3-2v1h-1V9zm-1 2h1v1h-1zm-2-4h1v1h-1zm3 0v1h-1V7zm-2-2v1h-1V5zm1 0h1v1h-1z"/>
    </svg>
    <h1>Empresa Selecionada</h1>
    <h2><?php echo $razaoSocial; ?></h2>
    <form method="POST" action="">
        <!-- Cidades -->
        <select name="cidade" required>
            <option value="">Selecione uma Cidade</option>
            <?php foreach ($cidades as $cidade) { ?>
                <option value="<?php echo $cidade['CIDADE']; ?>"><?php echo $cidade['DESCRICAO_CIDADE']; ?></option>
            <?php } ?>
        </select>
        <input type="submit" value="Entrar">
    </form>
</main>
</body>
</html>

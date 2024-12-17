<?php
/**
 *
 *
 * Objetivo: Altera os Dados dos produtos 
 *           Pode voltar para o home ou salvar a alteração do item
 * 
 */
include("../classes/db.php");
$db = new db();
$db->conectar();
$conn = $db->conexao();

session_start();
if (!isset($_SESSION['empresa_id'])) {
    header("Location: ../index.php");
    exit;
}

$empresaId = $_SESSION['empresa_id'];

$produtoId = $_GET['id'] ?? null;
if (!$produtoId) {
    header("Location: home.php");
    exit;
}

$query = $conn->prepare("
    SELECT * FROM PRODUTO 
    WHERE EMPRESA = :empresaId AND PRODUTO = :produtoId
");
$query->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
$query->bindValue(':produtoId', $produtoId, PDO::PARAM_STR);
$query->execute();
$produto = $query->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    echo "Produto não encontrado.";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    $descricaoProduto = trim($_POST['descricao_produto']);
    $apelidoProduto = trim($_POST['apelido_produto']);
    $grupoProduto = $_POST['grupo_produto'];
    $subgrupoProduto = $_POST['subgrupo_produto'];
    $situacao = $_POST['situacao'];
    $pesoLiquido = $_POST['peso_liquido'];
    $classificacaoFiscal = $_POST['classificacao_fiscal'];
    $codigoBarras = $_POST['codigo_barras'];
    $colecao = $_POST['colecao'];

    try {

        $updateQuery = $conn->prepare("
            UPDATE PRODUTO SET 
                DESCRICAO_PRODUTO = :descricaoProduto,
                APELIDO_PRODUTO = :apelidoProduto,
                GRUPO_PRODUTO = :grupoProduto,
                SUBGRUPO_PRODUTO = :subgrupoProduto,
                SITUACAO = :situacao,
                PESO_LIQUIDO = :pesoLiquido,
                CLASSIFICACAO_FISCAL = :classificacaoFiscal,
                CODIGO_BARRAS = :codigoBarras,
                COLECAO = :colecao
            WHERE EMPRESA = :empresaId AND PRODUTO = :produtoId
        ");

        $updateQuery->bindValue(':descricaoProduto', $descricaoProduto, PDO::PARAM_STR);
        $updateQuery->bindValue(':apelidoProduto', $apelidoProduto, PDO::PARAM_STR);
        $updateQuery->bindValue(':grupoProduto', $grupoProduto, PDO::PARAM_INT);
        $updateQuery->bindValue(':subgrupoProduto', $subgrupoProduto, PDO::PARAM_INT);
        $updateQuery->bindValue(':situacao', $situacao, PDO::PARAM_STR);
        $updateQuery->bindValue(':pesoLiquido', $pesoLiquido, PDO::PARAM_STR);
        $updateQuery->bindValue(':classificacaoFiscal', $classificacaoFiscal, PDO::PARAM_STR);
        $updateQuery->bindValue(':codigoBarras', $codigoBarras, PDO::PARAM_STR);
        $updateQuery->bindValue(':colecao', $colecao, PDO::PARAM_STR);
        $updateQuery->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
        $updateQuery->bindValue(':produtoId', $produtoId, PDO::PARAM_STR);
        
        $updateQuery->execute();
        header("Location: home.php?message=produto_atualizado");
        exit;
    } catch (PDOException $e) {
        echo "Erro ao atualizar o produto: " . $e->getMessage();
    }
}
?>




<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="../Css/styleeditar.css">


</head>
<body>
    <div class="container">
        <a href="home.php" class="icon-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
            Voltar
        </a>


        <h1>Editar Produto</h1>
        <form method="POST" action="">
            <label>Código do Produto:</label>
            <input type="text" name="produto" value="<?php echo ($produto['PRODUTO']); ?>" readonly>

            <label>Descrição do Produto:</label>
            <input type="text" name="descricao_produto" value="<?php echo ($produto['DESCRICAO_PRODUTO']); ?>" required>

            <label>Apelido do Produto:</label>
            <input type="text" name="apelido_produto" value="<?php echo ($produto['APELIDO_PRODUTO']); ?>">

            <label>Grupo do Produto:</label>
            <input type="number" name="grupo_produto" value="<?php echo ($produto['GRUPO_PRODUTO']); ?>">

            <label>Subgrupo do Produto:</label>
            <input type="number" name="subgrupo_produto" value="<?php echo ($produto['SUBGRUPO_PRODUTO']); ?>">

            <label>Situação:</label>
            <input type="text" name="situacao" value="<?php echo ($produto['SITUACAO']); ?>" maxlength="1">

            <label>Peso Líquido:</label>
            <input type="text" name="peso_liquido" value="<?php echo ($produto['PESO_LIQUIDO']); ?>">

            <label>Classificação Fiscal:</label>
            <input type="text" name="classificacao_fiscal" value="<?php echo ($produto['CLASSIFICACAO_FISCAL']); ?>">

            <label>Código de Barras:</label>
            <input type="text" name="codigo_barras" value="<?php echo ($produto['CODIGO_BARRAS']); ?>">

            <label>Coleção:</label>
            <input type="text" name="colecao" value="<?php echo ($produto['COLECAO']); ?>">

            <div class="button-container">
                <button type="submit" name="update">Salvar Alterações</button>
                <a href="home.php">Cancelar</a>
            </div>
        </form>
    </div>
</body>
</html>

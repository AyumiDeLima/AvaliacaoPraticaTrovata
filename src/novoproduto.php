<?php
/**
 * Primeira Tela
 *
 * Objetivo : 
 *            Cadastrar e Mostrar os Erros de cadastro
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
$erros = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $produto = trim($_POST['produto'] ?? '');
    $descricaoProduto = trim($_POST['descricao_produto'] ?? '');
    $apelidoProduto = trim($_POST['apelido_produto'] ?? '');
    $grupoProduto = $_POST['grupo_produto'] ?? null;
    $subgrupoProduto = $_POST['subgrupo_produto'] ?? null;
    $situacao = strtoupper(trim($_POST['situacao'] ?? ''));
    $pesoLiquido = $_POST['peso_liquido'] ?? null;
    $classificacaoFiscal = trim($_POST['classificacao_fiscal'] ?? '');
    $codigoBarras = trim($_POST['codigo_barras'] ?? '');
    $colecao = trim($_POST['colecao'] ?? '');

    if (strlen($produto) > 15 || strlen($descricaoProduto) > 250 || strlen($apelidoProduto) > 100 || 
        strlen($classificacaoFiscal) > 10 || strlen($codigoBarras) > 50 || strlen($colecao) > 100) {
        $erros[] = "Ultrapassou o limite de caracteres permitido.";
    }
    if (!is_null($grupoProduto) && !is_numeric($grupoProduto)) {
        $erros[] = "Grupo do produto deve ser um número.";
    }
    if (!is_null($subgrupoProduto) && !is_numeric($subgrupoProduto)) {
        $erros[] = "Subgrupo do produto deve ser um número.";
    }
    if (!is_null($pesoLiquido) && (!is_numeric($pesoLiquido) || $pesoLiquido < 0)) {
        $erros[] = "Peso líquido deve ser um número positivo.";
    }
    if ($situacao !== '' && !in_array($situacao, ['A', 'I'])) {
        $erros[] = "Situação deve ser 'A' (Ativo) ou 'I' (Inativo).";
    }
    if (empty($erros)) {
        try {
            $checkQuery = $conn->prepare("SELECT 1 FROM PRODUTO WHERE EMPRESA = :empresaId AND PRODUTO = :produto");
            $checkQuery->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
            $checkQuery->bindValue(':produto', $produto, PDO::PARAM_STR);
            $checkQuery->execute();

            if ($checkQuery->fetch()) {
                $erros[] = "O produto já existe na base de dados!";
            } else {
                $insertQuery = $conn->prepare("
                    INSERT INTO PRODUTO (
                        EMPRESA, PRODUTO, DESCRICAO_PRODUTO, APELIDO_PRODUTO, GRUPO_PRODUTO, 
                        SUBGRUPO_PRODUTO, SITUACAO, PESO_LIQUIDO, CLASSIFICACAO_FISCAL, CODIGO_BARRAS, COLECAO
                    ) VALUES (
                        :empresaId, :produto, :descricaoProduto, :apelidoProduto, :grupoProduto, 
                        :subgrupoProduto, :situacao, :pesoLiquido, :classificacaoFiscal, :codigoBarras, :colecao
                    )
                ");
                $insertQuery->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
                $insertQuery->bindValue(':produto', $produto, PDO::PARAM_STR);
                $insertQuery->bindValue(':descricaoProduto', $descricaoProduto, PDO::PARAM_STR);
                $insertQuery->bindValue(':apelidoProduto', $apelidoProduto, PDO::PARAM_STR);
                $insertQuery->bindValue(':grupoProduto', $grupoProduto, PDO::PARAM_INT);
                $insertQuery->bindValue(':subgrupoProduto', $subgrupoProduto, PDO::PARAM_INT);
                $insertQuery->bindValue(':situacao', $situacao, PDO::PARAM_STR);
                $insertQuery->bindValue(':pesoLiquido', $pesoLiquido, PDO::PARAM_STR);
                $insertQuery->bindValue(':classificacaoFiscal', $classificacaoFiscal, PDO::PARAM_STR);
                $insertQuery->bindValue(':codigoBarras', $codigoBarras, PDO::PARAM_STR);
                $insertQuery->bindValue(':colecao', $colecao, PDO::PARAM_STR);
                $insertQuery->execute();

                header("Location: home.php?message=produto_adicionado");
                exit;
            }
        } catch (PDOException $e) {
            $erros[] = "Erro ao salvar o produto: " . $e->getMessage();
        }
    }
}
?>

  
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Produto</title>
    <link rel="stylesheet" href="../Css/style_produto.css">
</head>
<body>
    <div class="voltar">
        <a href="home.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0"/>
            </svg>
            Voltar
        </a>
    </div>

    <div class="form-container">
        <h1>Adicionar Novo Produto</h1>
        <form method="POST" action="novoproduto.php">
            <div class="form-group">
                <label>Código do Produto:</label>
                <input type="text" name="produto" required>
            </div>
            <div class="form-group">
                <label>Descrição do Produto:</label>
                <input type="text" name="descricao_produto" required>
            </div>
            <div class="form-group">
                <label>Apelido do Produto:</label>
                <input type="text" name="apelido_produto" required>
            </div>
            <div class="form-group half">
                <label>Grupo do Produto:</label>
                <input type="number" name="grupo_produto">
            </div>
            <div class="form-group half">
                <label>Subgrupo do Produto:</label>
                <input type="number" name="subgrupo_produto">
            </div>
            <div class="form-group half">
                <label>Situação (A/I):</label>
                <input type="text" name="situacao" maxlength="1">
            </div>
            <div class="form-group half">
                <label>Peso Líquido:</label>
                <input type="text" name="peso_liquido">
            </div>
            <div class="form-group">
                <label>Classificação Fiscal:</label>
                <input type="text" name="classificacao_fiscal">
            </div>
            <div class="form-group">
                <label>Código de Barras:</label>
                <input type="text" name="codigo_barras">
            </div>
            <div class="form-group">
                <label>Coleção:</label>
                <input type="text" name="colecao">
            </div>

            <!-- Botão de Salvar -->
            <div class="form-footer">
                <button type="submit" name="save" class="btn-salvar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                    </svg>
                    Salvar
                </button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
/**
 * Tela Home
 *
 * Objetivo: Mostrar informações de Cadastro da Empresa e da Cidade Selecionada
 * *         Filtrar Pela Barra de pesquisa
 */

session_start();

include("../classes/db.php");
$db = new db();
$db->conectar();
$conn = $db->conexao();
 
// Sessions
if (!isset($_SESSION['empresa_id'])) {
    header("Location: ../index.php");
    exit;
}

$empresaId = $_SESSION['empresa_id'];
$cidadeId = $_SESSION['cidade_id'] ?? null;  // nulo no começo // evitar erros

/**
 *   COMEÇO INFORMAÇÕES DE CADASTRO E CIDADE      
 */

$queryEmp = $conn->prepare("
    SELECT 
        NOME_FANTASIA,
        RAZAO_SOCIAL,
        ENDERECO,
        BAIRRO,
        CEP,
        TELEFONE,
        FAX,
        CNPJ,
        IE
    FROM 
        EMPRESA
    WHERE 
        EMPRESA = :empresaId
");
$queryEmp->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
$queryEmp->execute();
$dadosEmp = $queryEmp->fetch(PDO::FETCH_ASSOC);

$nomeFantasia = $dadosEmp['NOME_FANTASIA'] ?? 'Informação indisponível';
$razaoSocial = $dadosEmp['RAZAO_SOCIAL'] ?? 'Informação indisponível';
$endereco = $dadosEmp['ENDERECO'] ?? 'Informação indisponível';
$bairro = $dadosEmp['BAIRRO'] ?? 'Informação indisponível';
$cepEmpresa = $dadosEmp['CEP'] ?? 'Informação indisponível'; 
$telefone = $dadosEmp['TELEFONE'] ?? 'Informação indisponível';
$fax = $dadosEmp['FAX'] ?? 'Informação indisponível';
$cnpj = $dadosEmp['CNPJ'] ?? 'Informação indisponível';
$ie = $dadosEmp['IE'] ?? 'Informação indisponível';

$queryCidades = $conn->prepare("
    SELECT 
        cid.CIDADE,
        cid.DESCRICAO_CIDADE AS NOME_CIDADE
    FROM 
        CIDADE cid
    WHERE 
        cid.EMPRESA = :empresaId
    ORDER BY cid.DESCRICAO_CIDADE ASC
");
$queryCidades->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
$queryCidades->execute();
$cidades = $queryCidades->fetchAll(PDO::FETCH_ASSOC);

$dadosCidade = [];
if ($cidadeId) {
    $queryCidade = $conn->prepare("
        SELECT 
            cid.DESCRICAO_CIDADE AS NOME_CIDADE,
            cid.PAIS AS PAIS,
            cid.UF AS ESTADO,
            cid.CEP_BASICO AS CEP_CIDADE,
            cid.DDD AS DDD
        FROM 
            CIDADE cid
        WHERE 
            cid.CIDADE = :cidadeId AND cid.EMPRESA = :empresaId
    ");
    $queryCidade->bindParam(':cidadeId', $cidadeId, PDO::PARAM_INT);
    $queryCidade->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
    $queryCidade->execute();
    $dadosCidade = $queryCidade->fetch(PDO::FETCH_ASSOC);
}

$cidade = $dadosCidade['NOME_CIDADE'] ?? 'Informação indisponível';
$estado = $dadosCidade['ESTADO'] ?? 'Informação indisponível';
$pais = $dadosCidade['PAIS'] ?? 'Informação indisponível';
$cepCidade = $dadosCidade['CEP_CIDADE'] ?? 'Informação indisponível';
$dddCidade = $dadosCidade['DDD'] ?? 'Informação indisponível';

/**
 *   FIM INFORMAÇÕES DE CADASTRO E CIDADE      
 */


/**
 *  INICIO PAGINAÇÃO    
 */

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$page = max($page, 1); 
$perPage = 15; 
$offset = ($page - 1) * $perPage; 

$sql = "
    SELECT 
        p.PRODUTO,
        p.DESCRICAO_PRODUTO,
        p.APELIDO_PRODUTO,
        g.DESCRICAO_GRUPO_PRODUTO
    FROM 
        PRODUTO p
    INNER JOIN 
        GRUPO_PRODUTO g ON p.EMPRESA = g.EMPRESA AND p.GRUPO_PRODUTO = g.GRUPO_PRODUTO
    WHERE 
        p.EMPRESA = :empresaId
    ORDER BY p.PRODUTO ASC
    LIMIT :perPage OFFSET :offset
";

$query = $conn->prepare($sql);
$query->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
$query->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$query->bindValue(':offset', $offset, PDO::PARAM_INT);
$query->execute();
$produtos = $query->fetchAll(PDO::FETCH_ASSOC);

$countQuery = $conn->prepare("
    SELECT COUNT(*) as total
    FROM PRODUTO p
    INNER JOIN GRUPO_PRODUTO g ON p.EMPRESA = g.EMPRESA AND p.GRUPO_PRODUTO = g.GRUPO_PRODUTO
    WHERE 
        p.EMPRESA = :empresaId
");
$countQuery->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
$countQuery->execute();
$total = $countQuery->fetchColumn();
$totalPages = ceil($total / $perPage);

/**
 *  FIM PAGINAÇÃO    
 */


/**
 *  INICIO DELETE 
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $produtoId = $_POST['produto_id'] ?? '';

    if (!empty($produtoId)) {
        echo "
            <div style='padding: 20px; border: 1px solid red; background: #ffe5e5; margin: 20px;'>
                <p>Tem certeza que deseja excluir o produto <strong>{$produtoId}</strong>?</p>
                <form method='POST' action='home.php'>
                    <input type='hidden' name='produto_id' value='{$produtoId}'>
                    <button type='submit' name='delete' style='background: green; color: white; padding: 10px; border: none; cursor: pointer;'>Sim</button>
                    <button type='submit' style='background: gray; color: white; padding: 10px; border: none; cursor: pointer;'>Não</button>
                </form>
            </div>
        ";
        exit; 
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $produtoId = $_POST['produto_id'] ?? '';
    $currentPage = $_POST['page'] ?? 1; 

    if (!empty($produtoId)) {
        try {
            $deleteQuery = $conn->prepare("DELETE FROM PRODUTO WHERE EMPRESA = :empresaId AND PRODUTO = :produtoId");
            $deleteQuery->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
            $deleteQuery->bindValue(':produtoId', $produtoId, PDO::PARAM_STR);
            $deleteQuery->execute();

            header("Location: home.php?page=$currentPage&message=success");
            exit;
        } catch (PDOException $e) {
            header("Location: home.php?page=$currentPage&message=error");
            exit;
        }
    }
}
/**
 *  FIM DELETE
 */


?>







<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="../Css/style_home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav>
        <span class="texto-nav">Bem-vindo!</span>
    </nav>

    <div class="container">
    <!-- Informações da Empresa e Cidade -->
    <div class="content-left">

        <div class="box">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-circle profile-icon" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                </svg>
            </div>
            <h2 id="nomeFantasia"><?php echo $nomeFantasia; ?></h2>
            <div id="info" class="info">
                <p><strong>Razão Social:</strong> <span><?php echo $razaoSocial; ?></span></p>
                <p><strong>Endereço:</strong> <span><?php echo $endereco; ?></span></p>
                <p><strong>Bairro:</strong> <span><?php echo $bairro; ?></span></p>
                <p><strong>CEP:</strong> <span><?php echo $cepEmpresa; ?></span></p>
                <p><strong>Cidades:</strong> 
                    <span>
                        <?php 
                            $nomesCidades = array_column($cidades, 'NOME_CIDADE');
                            echo implode(', ', $nomesCidades); 
                        ?>
                    </span>
                </p>
                <p><strong>Telefone:</strong> <span><?php echo $telefone; ?></span></p>
                <p><strong>Fax:</strong> <span><?php echo $fax; ?></span></p>
                <p><strong>CNPJ:</strong> <span><?php echo $cnpj; ?></span></p>
                <p><strong>Inscrição Estadual:</strong> <span><?php echo $ie; ?></span></p>
            </div>
            
            <!-- Botão sair -->
            <form method="POST" action="logout.php" style="margin-top: auto;">
                <button type="submit" class="sair-btn">
                    Sair
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-arrow-right logout-icon" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5.5 0 0 0 9.5 2h-8A1.5.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a.5.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                        <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                    </svg>
                </button>
            </form>
        </div>
        <div class="box">
            <h3>Informações da Cidade Selecionada</h3>
            <div id="info-cidade" class="info">
                <p><strong>Cidade:</strong> <span><?php echo $cidade; ?></span></p>
                <p><strong>Estado (UF):</strong> <span><?php echo $estado; ?></span></p>
                <p><strong>País:</strong> <span><?php echo $pais; ?></span></p>
                <p><strong>CEP da Cidade:</strong> <span><?php echo $cepCidade; ?></span></p>
                <p><strong>DDD:</strong> <span><?php echo $dddCidade; ?></span></p>
            </div>
        </div>
    </div>


 <!-- Produtos -->

    <div class="produto-container">
    <div class="produto-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="24" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
        </svg>
        <span class="produto-titulo">Produtos</span>
    </div>

    <!-- Barra de Pesquisa  -->
    <form method="POST" class="pesquisa-container">
    <div class="pesquisa-icon">
        <input 
            type="text" 
            name="search" 
            placeholder="Pesquisar por código, descrição, apelido ou grupo..." 
        >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-search search-icon" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
        </svg>
    </div>

    <!-- Descricao ou Codigo -->
    <select name="order" class="order-select">
    <option value="DESCRICAO_PRODUTO">Ordenar por Descrição</option>
    <option value="PRODUTO">Ordenar por Código</option>

    </select>

    <!-- Botão de busca -->
    <button type="submit" class="buscar">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
        </svg>
        Buscar
    </button>

    <!-- Botão Novo -->
    <a href="../src/novoproduto.php" class="novo">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
        </svg>
        Novo
    </a>
</form>


<!-- Lista de Produtos -->
<div class="produto-list">
    <?php if (!empty($produtos)) : ?>
        <?php foreach ($produtos as $produto) : ?>
            <div class="produto-box" style="border: 1px solid #ccc; padding: 20px; margin-bottom: 10px; display: flex; flex-direction: column; justify-content: center; background-color: #f9f9f9;">
                <!-- Código e Nome do Produto -->
                <div style="margin-bottom: 10px;">
                    <strong>Código:</strong> <?php echo htmlspecialchars($produto['PRODUTO']); ?><br>
                    <strong>Descrição:</strong> <?php echo htmlspecialchars($produto['DESCRICAO_PRODUTO']); ?>
                </div>

                <!-- Ações (Editar e Excluir) -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="editarproduto.php?id=<?php echo urlencode($produto['PRODUTO']); ?>" 
                       style="text-decoration: none; color: #007bff; font-weight: bold;">Editar</a>
                    
                    <!-- Ícone de exclusão -->
                    <form method="POST" action="home.php" style="margin: 0;">
                        <input type="hidden" name="produto_id" value="<?php echo htmlspecialchars($produto['PRODUTO']); ?>">
                        <button type="submit" class="delete-btn" name="confirm_delete" style="background: none; border: none; cursor: pointer; color: red; font-weight: bold;">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Nenhum produto encontrado.</p>
    <?php endif; ?>
</div>


    <!-- Paginação -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="home.php?page=<?php echo $page - 1; ?>" class="pagination-link">Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="home.php?page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="home.php?page=<?php echo $page + 1; ?>" class="pagination-link">Próxima</a>
        <?php endif; ?>
    </div>
</div>

</html>

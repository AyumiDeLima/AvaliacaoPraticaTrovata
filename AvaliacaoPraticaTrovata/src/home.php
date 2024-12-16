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

// Consulta Empresa
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

// Dados da Empresa
$nomeFantasia = $dadosEmp['NOME_FANTASIA'] ?? 'Informação indisponível';
$razaoSocial = $dadosEmp['RAZAO_SOCIAL'] ?? 'Informação indisponível';
$endereco = $dadosEmp['ENDERECO'] ?? 'Informação indisponível';
$bairro = $dadosEmp['BAIRRO'] ?? 'Informação indisponível';
$cepEmpresa = $dadosEmp['CEP'] ?? 'Informação indisponível'; 
$telefone = $dadosEmp['TELEFONE'] ?? 'Informação indisponível';
$fax = $dadosEmp['FAX'] ?? 'Informação indisponível';
$cnpj = $dadosEmp['CNPJ'] ?? 'Informação indisponível';
$ie = $dadosEmp['IE'] ?? 'Informação indisponível';

// Consulta Cidades dessa Empresa
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

// Dados Cidade Selecionada
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

// Dados Cidade Selecionada
$cidade = $dadosCidade['NOME_CIDADE'] ?? 'Informação indisponível';
$estado = $dadosCidade['ESTADO'] ?? 'Informação indisponível';
$pais = $dadosCidade['PAIS'] ?? 'Informação indisponível';
$cepCidade = $dadosCidade['CEP_CIDADE'] ?? 'Informação indisponível';
$dddCidade = $dadosCidade['DDD'] ?? 'Informação indisponível';


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
    <!-- Navbar -->
    <nav>
        <span class="texto-nav">Bem-vindo!</span>
    </nav>

    <!-- Conteúdo -->
    <div class="container">
        <!-- Cadastro da Empresa -->
        <div class="box">
            <div class="icon-container">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-circle profile-icon" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
                </svg>
            </div>
                 
            <!-- Empresa -->
            <h3 id="razaoSocial"><?php echo $razaoSocial; ?></h3>
            <div id="info" class="info">
                <p><strong>Nome Fantasia:</strong> <span><?php echo $nomeFantasia; ?></span></p>
                <p><strong>Razão Social:</strong> <span><?php echo $razaoSocial; ?></span></p>
                <p><strong>Endereço:</strong> <span><?php echo $endereco; ?></span></p>
                <p><strong>Bairro:</strong> <span><?php echo $bairro; ?></span></p>
                <p><strong>CEP:</strong> <span><?php echo $cepEmpresa; ?></span></p>
                <p><strong>Cidades:</strong> 
                    <span>
                        <?php 
                            $nomesCidades = [];
                            foreach ($cidades as $cidadesnum) {
                                $nomesCidades[] = $cidadesnum['NOME_CIDADE'];
                            }
                            echo implode(', ', $nomesCidades);
                        ?>
                    </span>
                </p>
                <p><strong>Telefone:</strong> <span><?php echo $telefone; ?></span></p>
                <p><strong>Fax:</strong> <span><?php echo $fax; ?></span></p>
                <p><strong>CNPJ:</strong> <span><?php echo $cnpj; ?></span></p>
                <p><strong>Inscrição Estadual:</strong> <span><?php echo $ie; ?></span></p>
            </div>

            
        </div>

        <!-- Cidade Selecionada -->
        <div class="box">
            <h2>Informações da Cidade</h2>
            <div id="info" class="info">
                <p><strong>Cidade:</strong> <span><?php echo $cidade; ?></span></p>
                <p><strong>Estado (UF):</strong> <span><?php echo $estado; ?></span></p>
                <p><strong>País:</strong> <span><?php echo $pais; ?></span></p>
                <p><strong>CEP da Cidade:</strong> <span><?php echo $cepCidade; ?></span></p>
                <p><strong>DDD:</strong> <span><?php echo $dddCidade; ?></span></p>
            </div>
        </div>

        <!-- Botão sair -->
        <form method="POST" action="logout.php" style="display: inline;">
            <button type="submit" class="sair-btn">
                Sair
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-arrow-right logout-icon" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5.5 0 0 0 9.5 2h-8A1.5.5 0 0 0 0 3.5v9A1.5.5 0 0 0 1.5 14h8a.5.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                </svg>
            </button>
        </form>
    </div>
</body>
</html>

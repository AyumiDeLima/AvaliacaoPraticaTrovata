<?php
/**
 * Tela Home
 *
 * Objetivo: Mostrar informações de Cadastro da Empresa e da Cidade Selecionada
 */

session_start();

include("../classes/db.php");
$db = new db();
$db->conectar();
$conn = $db->conexao();

// Sessões
if (!isset($_SESSION['empresa_id']) || !isset($_SESSION['cidade_id'])) {
    header("Location: ../index.php");
    exit;
}

$empresaId = $_SESSION['empresa_id'];
$cidadeId = $_SESSION['cidade_id'];

// Consulta Cidade
$queryCidade = $conn->prepare("
    SELECT 
        cid.DESCRICAO_CIDADE AS NOME_CIDADE,
        cid.UF AS ESTADO,
        cid.PAIS AS PAIS,
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

// Dados cidade
$cidade = $dadosCidade['NOME_CIDADE'] ?? 'Informação indisponível';
$estado = $dadosCidade['ESTADO'] ?? 'Informação indisponível';
$pais = $dadosCidade['PAIS'] ?? 'Informação indisponível';
$cepCidade = $dadosCidade['CEP_CIDADE'] ?? 'Informação indisponível';
$dddCidade = $dadosCidade['DDD'] ?? 'Informação indisponível';

// Consulta empresa
$queryEmp = $conn->prepare("
    SELECT 
        NOME_FANTASIA,
        RAZAO_SOCIAL,
        ENDERECO,
        BAIRRO,
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

// Dados Empresa
$nomeFantasia = $dadosEmp['NOME_FANTASIA'] ?? 'Informação indisponível';
$razaoSocial = $dadosEmp['RAZAO_SOCIAL'] ?? 'Informação indisponível';
$endereco = $dadosEmp['ENDERECO'] ?? 'Informação indisponível';
$bairro = $dadosEmp['BAIRRO'] ?? 'Informação indisponível';
$fax = $dadosEmp['FAX'] ?? 'Informação indisponível';
$cnpj = $dadosEmp['CNPJ'] ?? 'Informação indisponível';
$ie = $dadosEmp['IE'] ?? 'Informação indisponível';

$telefoneCompleto = $dddCidade !== 'Informação indisponível' ? "($dddCidade) {$telefone}" : 'Informação indisponível';
$cepCompleto = $cepCidade !== 'Informação indisponível' ? $cepCidade : 'Informação indisponível';

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

    <!-- Conteudo -->
    <div class="container">
        <!-- Box cadastro-->
        <div class="box">
          <div class="icon-container">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-person-circle profile-icon" viewBox="0 0 16 16">
                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/>
                  <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1"/>
            </svg>
         </div>
             
         <!-- Todas as informações cadastro-->
         <h2 id="razaoSocial"><?php echo $razaoSocial; ?></h2>
         <div id="info" class="info">
            <p><strong>Nome Fantasia:</strong> <span><?php echo $nomeFantasia; ?></span></p>
            <p><strong>Endereço:</strong> <span><?php echo $endereco; ?></span></p>
            <p><strong>Bairro:</strong> <span><?php echo $bairro; ?></span></p>
            <p><strong>CEP:</strong> <span><?php echo $cepCompleto; ?></span></p>
            <p><strong>Cidade:</strong> <span><?php echo $cidade; ?></span></p>
            <p><strong>Telefone:</strong> <span><?php echo $telefoneCompleto; ?></span></p>
            <p><strong>Fax:</strong> <span><?php echo $fax; ?></span></p>
            <p><strong>CNPJ:</strong> <span><?php echo $cnpj; ?></span></p>
            <p><strong>Inscrição Estadual:</strong> <span><?php echo $ie; ?></span></p>
</div>

            <!-- Botão sair -->
            <form method="POST" action="logout.php" style="display: inline;">
                <button type="submit" class="sair-btn">
                     Sair
                     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-arrow-right logout-icon" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                      <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
                     </svg>
                 </button>
            </form>
            </div>
        
        <!-- Produtos -->
        <div class="produto-container">
            <div class="produto-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="24" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708"/>
                </svg>
                <span class="produto-titulo">Produtos</span>
            </div>

            <!-- Barra de pesquisa -->
            <div class="pesquisa-container">
                <div class="pesquisa-icon">
                    <input type="text" placeholder="Pesquisar por código, descrição, grupo...">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-search search-icon" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </div>

                <!-- Botão Novo -->
                <button class="novo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
                    </svg>
                    Novo
                </button>
            </div>
        </div>
    </div>

</body>
</html>

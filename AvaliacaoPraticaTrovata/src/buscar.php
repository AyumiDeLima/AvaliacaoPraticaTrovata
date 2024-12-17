<?php
/**
 * Primeira Tela
 *
 * Objetivo : 
 *             Executa o filtro da Barra de Pesquisa e Box Descrição ou Codigo
 *            
 */

session_start();

if (!isset($_SESSION['empresa_id'])) {
    header("Location: ../index.php");
    exit;
}

$empresaId = $_SESSION['empresa_id'];

include("../classes/db.php");
$db = new db();
$db->conectar();
$conn = $db->conexao();

// Barra Pesquisa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search'])) {
    $searchTerm = '%' . trim($_POST['search']) . '%';

    $sql = "
            SELECT 
            p.PRODUTO,
            p.DESCRICAO_PRODUTO,
            p.APELIDO_PRODUTO,
            p.CODIGO_BARRAS,
            COALESCE(g.DESCRICAO_GRUPO_PRODUTO, 'Informação indisponível') AS DESCRICAO_GRUPO_PRODUTO,
            COALESCE(tc.DESCRICAO_TIPO_COMPLEMENTO, 'Informação indisponível') AS DESCRICAO_TIPO_COMPLEMENTO
        FROM 
            PRODUTO p
        LEFT JOIN 
            GRUPO_PRODUTO g ON p.EMPRESA = g.EMPRESA AND p.GRUPO_PRODUTO = g.GRUPO_PRODUTO
        LEFT JOIN 
            TIPO_COMPLEMENTO tc ON g.EMPRESA = tc.EMPRESA AND g.TIPO_COMPLEMENTO = tc.TIPO_COMPLEMENTO
        WHERE 
            p.EMPRESA = :empresaId
            AND (
                TRIM(p.PRODUTO) LIKE :search1 OR
                TRIM(p.DESCRICAO_PRODUTO) LIKE :search2 OR
                TRIM(p.APELIDO_PRODUTO) LIKE :search3 OR
                TRIM(p.CODIGO_BARRAS) LIKE :search4 OR
                TRIM(g.DESCRICAO_GRUPO_PRODUTO) LIKE :search5 OR
                TRIM(tc.DESCRICAO_TIPO_COMPLEMENTO) LIKE :search6
            )

    ";

    try {
        $sth = $conn->prepare($sql);
        $sth->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
        $sth->bindParam(':search1', $searchTerm, PDO::PARAM_STR); 
        $sth->bindParam(':search2', $searchTerm, PDO::PARAM_STR); 
        $sth->bindParam(':search3', $searchTerm, PDO::PARAM_STR); 
        $sth->bindParam(':search4', $searchTerm, PDO::PARAM_STR); 
        $sth->bindParam(':search5', $searchTerm, PDO::PARAM_STR); 
        $sth->bindParam(':search6', $searchTerm, PDO::PARAM_STR); 
        
        $sth->execute();

        $resultados = $sth->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['resultados_pesquisa'] = $resultados;

        header("Location: ../src/home.php");
        exit;

    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        exit;
    }
}

// Descrição e Codigo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtro']) && in_array($_POST['filtro'], ['DESCRICAO_PRODUTO', 'PRODUTO'])) {
    $filtro = $_POST['filtro'];

    $filtro = in_array($filtro, ['DESCRICAO_PRODUTO', 'PRODUTO']) ? $filtro : 'DESCRICAO_PRODUTO';

    if ($filtro === 'DESCRICAO_PRODUTO') {
        $sql = "
            SELECT 
                p.PRODUTO,
                p.DESCRICAO_PRODUTO,
                p.APELIDO_PRODUTO,
                p.CODIGO_BARRAS,
                COALESCE(g.DESCRICAO_GRUPO_PRODUTO, 'Informação indisponível') AS DESCRICAO_GRUPO_PRODUTO,
                COALESCE(tc.DESCRICAO_TIPO_COMPLEMENTO, 'Informação indisponível') AS DESCRICAO_TIPO_COMPLEMENTO
            FROM 
                PRODUTO p
            LEFT JOIN 
                GRUPO_PRODUTO g ON p.EMPRESA = g.EMPRESA AND p.GRUPO_PRODUTO = g.GRUPO_PRODUTO
            LEFT JOIN 
                TIPO_COMPLEMENTO tc ON g.EMPRESA = tc.EMPRESA AND g.TIPO_COMPLEMENTO = tc.TIPO_COMPLEMENTO
            WHERE 
                p.EMPRESA = :empresaId
            ORDER BY 
                p.DESCRICAO_PRODUTO ASC;
        ";
    } else {
        $sql = "
           SELECT 
            p.PRODUTO,
            p.DESCRICAO_PRODUTO,
            p.APELIDO_PRODUTO,
            p.CODIGO_BARRAS,
            COALESCE(g.DESCRICAO_GRUPO_PRODUTO, 'Informação indisponível') AS DESCRICAO_GRUPO_PRODUTO,
            COALESCE(tc.DESCRICAO_TIPO_COMPLEMENTO, 'Informação indisponível') AS DESCRICAO_TIPO_COMPLEMENTO
        FROM 
            PRODUTO p
        LEFT JOIN 
            GRUPO_PRODUTO g ON p.EMPRESA = g.EMPRESA AND p.GRUPO_PRODUTO = g.GRUPO_PRODUTO
        LEFT JOIN 
            TIPO_COMPLEMENTO tc ON g.EMPRESA = tc.EMPRESA AND g.TIPO_COMPLEMENTO = tc.TIPO_COMPLEMENTO
        WHERE 
            p.EMPRESA = :empresaId
        ORDER BY 
            CONVERT(p.PRODUTO, UNSIGNED) ASC;
        ";
    }

    try {
        $query = $conn->prepare($sql);
        $query->bindValue(':empresaId', $empresaId, PDO::PARAM_INT);
        $query->execute();
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);

        $_SESSION['resultados_pesquisa'] = $resultados;

        header("Location: ../src/home.php");
        exit;

    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        exit;
    }
}

header("Location: ../src/home.php");
exit;
?>


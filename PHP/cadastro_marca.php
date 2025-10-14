<?php
// Conectando este arquivo ao banco de dados
require_once __DIR__ . "/conexao.php";

// função para capturar os dados passados de uma página a outra
function redirecWith($url, $params = []) {
  // verifica se os os paramentros não vieram vazios
  if (!empty($params)) {
    // separar os parametros em espaços diferentes
    $qs  = http_build_query($params);
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    $url .= $sep . $qs;
  }
  // joga a url para o cabeçalho no navegador
  header("Location: $url");
  // fecha o script
  exit;
}

/* Lê arquivo de upload como blob (ou null) */
function readImageToBlob(?array $file): ?string {
  if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $content = file_get_contents($file['tmp_name']);
  return $content === false ? null : $content;
}

// Listagem de marcas com imagem
// LISTAGEM DE MARCAS COM IMAGEM
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])){
// Define o tipo de resposta: JSON e com codificação UTF-8
  header('Content-Type: application/json; charset=utf-8');

  try {
    // Faz a consulta no banco — busca id, nome e imagem (blob)
    $stmt = $pdo->query("SELECT idMarcas, nome, imagem FROM Marcas ORDER BY idMarcas DESC");

    // Pega todas as linhas retornadas como array associativo
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapeia cada linha para o formato desejado:
    //  - converte o id para inteiro
    //  - mantém o nome como texto
    //  - converte o blob da imagem para base64 (ou null se não houver imagem)
    $marcas = array_map(function ($r) {
      return [
        'idMarcas' => (int)$r['idMarcas'],
        'nome'     => $r['nome'],
        'imagem'   => !empty($r['imagem']) ? base64_encode($r['imagem']) : null
      ];
    }, $rows);

    // Retorna o JSON com:
    //  - ok: true  → indica sucesso
    //  - count: quantidade de marcas encontradas
    //  - marcas: array com todos os dados
    echo json_encode(
      ['ok'=>true,'count'=>count($marcas),'marcas'=>$marcas],
      JSON_UNESCAPED_UNICODE // mantém acentos corretamente
    );

  } catch (Throwable $e) {
    // Se acontecer qualquer erro (ex: problema no banco),
    // envia código HTTP 500 e o erro no formato JSON
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
  }

  //  Interrompe a execução do restante do arquivo.
  
  exit;

}




try {
  // SE O METODO DE ENVIO FOR DIFERENTE DO POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro_marca" => "Método inválido"]);
  }

  // jogando os dados dentro de váriaveis (conforme seu HTML)
  $nomemarca = trim($_POST["nomemarca"] ?? "");
  $imgBlob   = readImageToBlob($_FILES["imagemmarca"] ?? null);

  // VALIDANDO OS CAMPOS
  $erros_validacao = [];
  if ($nomemarca === "") {
    $erros_validacao[] = "Preencha o nome da marca.";
  }

  // se houver erros, volta para a tela com a mensagem
  if (!empty($erros_validacao)) {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro_marca" => implode(" ", $erros_validacao)]);
  }

  // INSERT
  $sql  = "INSERT INTO Marcas (nome, imagem) VALUES (:nome, :imagem)";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(":nome", $nomemarca, PDO::PARAM_STR);

  if ($imgBlob === null) {
    $stmt->bindValue(":imagem", null, PDO::PARAM_NULL);
  } else {
    $stmt->bindValue(":imagem", $imgBlob, PDO::PARAM_LOB);
  }

  $ok = $stmt->execute();

  if ($ok) {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["cadastro_marca" => "ok"]);
  } else {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro_marca" => "Falha ao cadastrar marca."]);
  }

} catch (Exception $e) {
  redirecWith("../paginas_logista/cadastro_produtos_logista.html",
    ["erro_marca" => "Erro no banco de dados: " . $e->getMessage()]);
}

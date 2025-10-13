<?php
require_once __DIR__ . "/conexao.php";

function redirecWith($url, $params = [])
{
  if (!empty($params)) {
    $qs = http_build_query($params);
    $sep = (strpos($url, '?') === false) ? '?' : '&';
    $url .= $sep . $qs;
  }
  header("Location: $url");
  exit;
}


/* Função para ler imagem e converter em BLOB */
function readImageToBlob(?array $file): ?string
{
  if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $content = file_get_contents($file['tmp_name']);
  return $content === false ? null : $content;
}

try {

  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html", [
      "erro_marca" => "Método inválido"
    ]);
  }

  // Campos do formulário
  $nome = $_POST["nomeproduto"];
  $descricao = $_POST["Descricao"];
  $quantidade = (int)$_POST["quantidade"];
  $preco = (double)$_POST["preco"];
  $codigo = (int)$_POST["codigo"];
  $preco_promocional = (double)$_POST["precopromocional"];
  $marcas_idMarcas = 1;

  // Imagens
  $img1 = readImageToBlob($_FILES["imgproduto1"] ?? null);
  $img2 = readImageToBlob($_FILES["imgproduto2"] ?? null);
  $img3 = readImageToBlob($_FILES["imgproduto3"] ?? null);

  // Validação
  $erros_validacao = [];
  if ($nome === "" || $descricao === "" || $quantidade <= 0 || $preco <= 0 || $marcas_idMarcas <= 0) {
    $erros_validacao[] = "Preencha todos os campos obrigatórios.";
  }

  if (!empty($erros_validacao)) {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html", [
      "erro_marca" => implode(" ", $erros_validacao)
    ]);
  }

  // Início da transação
  $pdo->beginTransaction();

  // ✅ Inserir produto
  $sqlProdutos = "INSERT INTO Produtos 
    (nome, descricao, quantidade, preco, codigo, preco_promococional, marcas_idMarcas) 
    VALUES 
    (:nome, :descricao, :quantidade, :preco, :codigo, :preco_promocional, :marcas_idMarcas)";

  $stmProdutos = $pdo->prepare($sqlProdutos);

  $inserirProdutos = $stmProdutos->execute([
    ":nome" => $nome,
    ":descricao" => $descricao,
    ":quantidade" => $quantidade,
    ":preco" => $preco,
    ":codigo" => $codigo,
    ":preco_promocional" => $preco_promocional,
    ":marcas_idMarcas" => $marcas_idMarcas,
  ]);

  if (!$inserirProdutos) {
    $pdo->rollBack();
    redirecWith("../paginas_logista/cadastro_produtos_logista.html", [
      "Erro" => "Falha ao cadastrar produto"
    ]);
  }

  $idProduto = (int)$pdo->lastInsertId();

  // ✅ Inserir imagens (corrigido para uma inserção em sequência)
  $sqlImagens = "INSERT INTO Imagem_produtos (foto) VALUES (:imagem)";
  $stmImagens = $pdo->prepare($sqlImagens);

  $idsImagens = [];

  foreach ([$img1, $img2, $img3] as $img) {
    if ($img !== null) {
      $stmImagens->bindParam(':imagem', $img, PDO::PARAM_LOB);
      $stmImagens->execute();
      $idsImagens[] = (int)$pdo->lastInsertId();
    }
  }

  if (empty($idsImagens)) {
    $pdo->rollBack();
    redirecWith("../paginas_logista/cadastro_produtos_logista.html", [
      "Erro" => "Nenhuma imagem válida enviada."
    ]);
  }

  // ✅ Vincular produto com imagens
  $sqlVincular = "INSERT INTO Produtos_e_ImagemProduto 
    (Produtos_idProdutos, Imagem_produtos_idImagemProdutos) 
    VALUES (:idpro, :idimg)";

  $stmVincular = $pdo->prepare($sqlVincular);

  foreach ($idsImagens as $idImg) {
    $stmVincular->execute([
      ":idpro" => $idProduto,
      ":idimg" => $idImg
    ]);
  }

  // Tudo certo — confirmar transação
  $pdo->commit();

  redirecWith("../paginas_logista/cadastro_produtos_logista.html", [
    "sucesso" => "Produto cadastrado com sucesso!"
  ]);
} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  redirecWith("../paginas_logista/cadastro_produtos_logista.html", [
    "erro_marca" => "Erro no banco de dados: " . $e->getMessage()
  ]);
}
?>

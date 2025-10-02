<?php

// Conectando este arquivo ao banco de dados
require_once __DIR__ ."/conexao.php";

// função para capturar os dados passados de uma página a outra
function redirecWith($url,$params=[]){
// verifica se os os paramentros não vieram vazios
 if(!empty($params)){
// separar os parametros em espaços diferentes
$qs= http_build_query($params);
$sep = (strpos($url,'?') === false) ? '?': '&';
$url .= $sep . $qs;
}
// joga a url para o cabeçalho no navegador
header("Location:  $url");
// fecha o script
exit;
}

/* Lê arquivo de upload como blob (ou null) */
function readImageToBlob(?array $file): ?string {
  if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
  $content = file_get_contents($file['tmp_name']);
  return $content === false ? null : $content;
}

try{

 // SE O METODO DE ENVIO FOR DIFERENTE DO POST
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro_marca" => "Método inválido"]);
  }

  $nome = $_POST["nomeproduto"];
  $descricao = $_POST["Descricao"];
  $quantidade = (int)$_POST["quantidade"];
  $preco = (double)$_POST["preco"];
  $codigo = (int)$_POST["codigo"];
  $preco_promococional = (double)$_POST["precopromocional"];
  $marcas_idMarcas = 1;
  //Criar as váriaveis das imagens
$img1 readImageToBlob($_FILES["imgproduto1"] ?? null);
$img2 readImageToBlob($_FILES["imgproduto2"] ?? null);
$img3 readImageToBlob($_FILES["imgproduto3"] ?? null);

//Validando os campos
  $erros_validacao = [];

    if ($nome === "" || $descricao === "" || $quantidade === "" || $preco === "" ||
        $codigo === "" || $marcas_idMarcas === "" || $codigo === ""){
            $erros_validacao[] = "Preencha os campos obrigatórios.";
}

//Se houver erros, volta para a tela com a mensagem
if (!empty($erros_validacao)){
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
    ["erro_marca" => implode(" ", $erros_validacao)]);
}

//É utilizado para fazer vinculos de transações
$pdo ->beginTransaction();

// Fazer o comando de inserir dentro da tabela de produtos
$sqlProdutos ="INSERT INTO Produtos(namoe,descricao,quantidade,preco,codigo,preco_promocional,marcas_idMarcas),
VALUES (:nome,:descricao,:quantidade,:preco,:codigo,:preco_promocional,:marcas_idMarcas)";

$stmProdutos = $pdo -> prepare($sqlProdutos);

$inserirProdutos=$stmProdutos->execute([

]);


}catch(Exception $e){
redirecWith("../paginas_logista/cadastro_produtos_logista.html",
    ["erro_marca" => "Erro no banco de dados: " . $e->getMessage()]);

}







?>
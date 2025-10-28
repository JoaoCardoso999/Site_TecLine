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

// LISTAGEM DE FORMAS_PAGAMENTO
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
  try {
    // Comando de listagem
    $sqllistar = "SELECT idformas_pagamento AS id, nome 
                  FROM Formas_pagamento 
                  ORDER BY nome";

    // Executa
    $stmtlistar = $pdo->query($sqllistar);
    $listar = $stmtlistar->fetchAll(PDO::FETCH_ASSOC);

    // Formato do retorno
    $formato = isset($_GET["format"]) ? strtolower($_GET["format"]) : "option";

    if ($formato === "json") {
      header("Content-Type: application/json; charset=utf-8");
      echo json_encode(["ok" => true, "formas_pagamento" => $listar], JSON_UNESCAPED_UNICODE);
      exit;
    }

    // RETORNO PADRÃO (options)
    header("Content-Type: text/html; charset=utf-8");
    foreach ($listar as $lista) {
      $id   = (int)$lista["id"];
      $nome = htmlspecialchars($lista["nome"], ENT_QUOTES, "UTF-8");
      echo "<option value=\"{$id}\">{$nome}</option>\n";
    }
    exit;

  } catch (Throwable $e) {
    // Erro na listagem
    if (isset($_GET["format"]) && strtolower($_GET["format"]) === "json") {
      header("Content-Type: application/json; charset=utf-8", true, 500);
      echo json_encode(
        ["ok" => false, "error" => "Erro ao listar formas de pagamento", "detail" => $e->getMessage()],
        JSON_UNESCAPED_UNICODE
      );
    } else {
      header("Content-Type: text/html; charset=utf-8", true, 500);
      echo "<option disabled>Erro ao carregar formas de pagamento</option>";
    }
    exit;
  }
}


/*  ============================ATUALIZAÇÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
  try {

// alterar para os nomes que vem do 'name' dos campos inputs do html
    $id        = (int)($_POST['id'] ?? 0); // este dados não precisa mudar
    $nomecategoria = trim($_POST['nomecategoria'] ?? '');
    $desconto = trim($_POST['desconto'] ?? '');
  
    if ($id <= 0) {
// alterar para o nome da página html que você está utilizando
      redirect_with('../PAGINAS_LOGISTA/banners_logista.html', ['erro_banner' => 'ID inválido para edição.']);
    }

    // validações mínimas (iguais ao cadastro)
    $erros = [];
    if ($descricao === '') { $erros[] = 'Informe a descrição.'; }
    elseif (mb_strlen($descricao) > 45) { $erros[] = 'Descrição deve ter no máximo 45 caracteres.'; }

// validação para datas
    $dt = DateTime::createFromFormat('Y-m-d', $dataVal);
    if (!($dt && $dt->format('Y-m-d') === $dataVal)) { $erros[] = 'Data de validade inválida (use YYYY-MM-DD).'; }

// utilizado especificamente para links
    if ($link !== '' && mb_strlen($link) > 45) { $erros[] = 'Link deve ter no máximo 45 caracteres.'; }

    if ($erros) {
// alterar para o nome da página html que você está utilizando

      redirect_with('../PAGINAS_LOGISTA/cadastro_produtos_logista.html', ['erro_banner' => implode(' ', $erros)]);
    }

    /* Monta UPDATE dinâmico (atualiza imagem só se uma nova foi enviada), campos do BANCO DE DADOS*/
    $setSql = "descricao = :desc, data_validade = :dt, link = :lnk, CategoriasProdutos_id = :cat";
    if ($imgBlob !== null) {
      $setSql = "imagem = :img, " . $setSql;
    }
// ALTERAR CONFORME O BANCO DE DADOS
    $sql = "UPDATE Banners
              SET $setSql
            WHERE idBanners = :id";

    $st = $pdo->prepare($sql);

// UTILIZADO APENAS SE TIVER IMAGEM
    if ($imgBlob !== null) {
      $st->bindValue(':img', $imgBlob, PDO::PARAM_LOB);
    }
// UTILIZADO PARA TODOS OS CAMPOS OBRIGATORIOS
    $st->bindValue(':desc', $descricao, PDO::PARAM_STR);
    $st->bindValue(':dt',   $dataVal,   PDO::PARAM_STR);

// UTILIZADO APENAS PARA CAMPOS NÃO OBRIGATORIOS
    if ($link === '') {
      $st->bindValue(':lnk', null, PDO::PARAM_NULL);
    } else {
      $st->bindValue(':lnk', $link, PDO::PARAM_STR);
    }

// UTILIZADO PARA FOREIGN KEYS
    if ($categoria === null) {
      $st->bindValue(':cat', null, PDO::PARAM_NULL);
    } else {
      $st->bindValue(':cat', $categoria, PDO::PARAM_INT);
    }

    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();

// alterar para o nome da página html que você está utilizando

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['editar_banner' => 'ok']);

  } catch (Throwable $e) {

// alterar para o nome da página html que você está utilizando

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_banner' => 'Erro ao editar: ' . $e->getMessage()]);
  }
}

/*  ============================EXCLUSÃO=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
  try {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {

// alterar para o nome da página html que você está utilizando
      redirect_with('../PAGINAS_LOGISTA/banners_logista.html', ['erro_banner' => 'ID inválido para exclusão.']);
    }

// alterar os dados para os nomes que vem do seu banco de dados
    $st = $pdo->prepare("DELETE FROM Banners WHERE idBanners = :id");
    $st->bindValue(':id', $id, PDO::PARAM_INT);
    $st->execute();
// alterar para o nome da página html que você está utilizando

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['excluir_banner' => 'ok']);

  } catch (Throwable $e) {
// alterar para o nome da página html que você está utilizando

    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_banner' => 'Erro ao excluir: ' . $e->getMessage()]);
  }
}





// códigos de cadastro
try{
// SE O METODO DE ENVIO FOR DIFERENTE DO POST
    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        //VOLTAR À TELA DE CADASTRO E EXIBIR ERRO
        redirecWith("../paginas_logista/cadastro_produtos_logista.html",
           ["erro"=> "Metodo inválido"]);
    }
    // jogando os dados da dentro de váriaveis
    $nomepagamento = $_POST["nomepagamento"];

     // VALIDANDO OS CAMPOS
// criar uma váriavel para receber os erros de validação
    $erros_validacao=[];
    //se qualquer campo for vazio
    if($nomepagamento === "" ){
        $erros_validacao[]="Preencha todos os campos";
    }

    /* Inserir a categoria no banco de dados */
    $sql ="INSERT INTO Formas_pagamento (nome)
     Values (:nomepagamento)";
     // executando o comando no banco de dados
     $inserir = $pdo->prepare($sql)->execute([
        ":nomepagamento" => $nomepagamento, 
     ]);
     /* Verificando se foi cadastrado no banco de dados */
     if($inserir){
        redirecWith("../paginas_logista/frete_pagamento_logista.html",
        ["cadastro" => "ok"]) ;
     }else{
        redirecWith("../paginas_logista/frete_pagamento_logista.html",["erro" 
        =>"Erro ao cadastrar no banco de dados"]);
     }


}catch(Exception $e){
 redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro" => "Erro no banco de dados: "
      .$e->getMessage()]);
}


?>
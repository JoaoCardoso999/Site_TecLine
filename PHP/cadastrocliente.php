<?php
// Conectando ao banco de dados
require_once __DIR__ . "/conexao.php";

// Função para redirecionar com parâmetros
function redirectWith($url, $params = []) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

try {
    // Validando o método de requisição
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirectWith("../paginas/cadastro.html", ["erro" => "Método inválido"]);
    }

    // Capturando os dados do formulário
    $nome = trim($_POST["nome"]);
    $sobrenome = trim($_POST["sobrenome"]);
    $dataNascimento = trim($_POST["dataNascimento"]); // não está no banco ainda
    $telefone = trim($_POST["telefone"]);
    $cpf = trim($_POST["cpf"]);
    $email = trim($_POST["email"]);
    $emailNovamente = trim($_POST["emailNovamente"]);
    $senha = $_POST["senha"];
    $senhaNovamente = $_POST["senhaNovamente"];

    // Validações
    $erros = [];

    if (
        $nome === "" || $sobrenome === "" || $dataNascimento === "" || $telefone === "" ||
        $cpf === "" || $email === "" || $emailNovamente === "" || $senha === "" || $senhaNovamente === ""
    ) {
        $erros[] = "Preencha todos os campos.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido.";
    }

    if ($email !== $emailNovamente) {
        $erros[] = "Os e-mails não coincidem.";
    }

    if ($senha !== $senhaNovamente) {
        $erros[] = "As senhas não coincidem.";
    }

    if (strlen($senha) < 8) {
        $erros[] = "A senha deve ter pelo menos 8 caracteres.";
    }

    if (strlen($telefone) < 11) {
        $erros[] = "Telefone inválido. Use DDD + número.";
    }

    if (strlen($cpf) < 11) {
        $erros[] = "CPF inválido.";
    }

    // Redirecionar se houver erros
    if (!empty($erros)) {
        redirectWith("../paginas/cadastro.html", ["erro" => $erros[0]]);
    }

    // Verifica se CPF já existe
    $stmt = $pdo->prepare("SELECT 1 FROM Cliente WHERE cpf = :cpf LIMIT 1");
    $stmt->execute([':cpf' => $cpf]);
    if ($stmt->fetch()) {
        redirectWith('../paginas/cadastro.html', ["erro" => "CPF já cadastrado."]);
    }

    

    // Inserindo no banco
    $sql = "INSERT INTO Cliente (nome, sobrenome, cpf, telefone, email, senha)
            VALUES (:nome, :sobrenome, :cpf, :telefone, :email, :senha)";
    
    $inserir = $pdo->prepare($sql)->execute([
        ":nome" => $nome,
        ":sobrenome" => $sobrenome,
        ":cpf" => $cpf,
        ":telefone" => $telefone,
        ":email" => $email,
        ":senha" => $senha
    ]);

    if ($inserir) {
        redirectWith("../paginas/login.html", ["cadastro" => "ok"]);
    } else {
        redirectWith("../paginas/cadastro.html", ["erro" => "Erro ao inserir no banco de dados."]);
    }

} catch (PDOException $e) {
    redirectWith("../paginas/cadastro.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>

# Exemplo de um Quiz simples desenvolvido em PHP e MySQL

O tema do quiz deste exemplo é José Saramago.

## Base de dados

A base de dados deste exemplo é bastante simples e contêm apenas 2 tabelas, uma para as perguntas e outras para as respetivas opções de resposta.

### Criar tabela de perguntas

```sql
CREATE TABLE `perguntas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pergunta` varchar(255) CHARACTER SET latin1 NOT NULL
);
```

### Criar tabela de respostas

```sql
CREATE TABLE `respostas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pergunta` int(11) NOT NULL,
  `resposta` varchar(255) CHARACTER SET latin1 NOT NULL,
  `v_f` tinyint(1) NOT NULL
);
```

Adicionamos a relação entre a tabela de `perguntas` e `respostas`.

```sql
ALTER TABLE `respostas`
  ADD CONSTRAINT `perguntas_respostas` FOREIGN KEY (`id_pergunta`) REFERENCES `perguntas` (`id`);
COMMIT;
```

### Dados de teste

Por fim, introduzimos alguns dados de teste.

Os dados aqui introduzidos são apenas um exemplo.

```sql
INSERT INTO `perguntas` (`id`, `pergunta`) VALUES
(1, 'Qual a data de falecimento de José Saramago?'),
(2, 'Em que ano José Saramago recebeu o Prémio Nobel da Literatura?');

INSERT INTO `respostas` (`id`, `id_pergunta`, `resposta`, `v_f`) VALUES
(1, 1, '18 de junho de 2010', 1),
(2, 1, '20 de março de 2014', 0),
(3, 1, '18 de junho de 2011', 0),
(4, 1, '18 de julho de 2010', 0),
(5, 2, '1999', 0),
(6, 2, '1998', 1),
(7, 2, '1997', 0),
(8, 2, '2000', 0);
```

## Código

Neste capitulo sera explicada cada ficheiro e incluído o código do mesmo.  
O código dos ficheiros está amplamente comentado com uma explicação sucinta de cada etapa.

#### config/db.php

Neste ficheiro temos as configurações de acesso à base de dados MySQL e a respetiva conexão.

```php
<?php
// Definimos variáveis com as configurações de acesso à base de dados
$hostname = "localhost";
$username = "root";
$password = "";
$dbName = "projeto_si";

// Inicializamos a conexão à base de dados
$connection = mysqli_connect($hostname, $username, $password, $dbName) or die("Conexão à base de dados não estabelecida");

// Definimos o charset da conexão à base de dados como utf8 de modo a suportar acentos nos dados provenientes da base dados
mysqli_set_charset($connection, "utf8");
```

#### index.php

Neste ficheiro apresentamos um botão para iniciar o jogo.

Funciona como uma página inicial para o jogo, onde poderiam ser apresentados outros dados, como o total de jogos realizados até ao momento, um top 10 de jogadores, etc.

```php
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - José Saramago</title>
</head>

<body>
    <h1>Quiz - José Saramago</h1>

    <!-- Mostramos um botão ao utilizador que permite iniciar o jogo -->
    <a href="play.php">Jogar</a>
</body>

</html>
```

#### play.php

Neste ficheiro é onde está o código de toda a lógica do jogo. Este ficheiro é responsável por obter as questões e respostas da base de dados, apresentar as mesmas, e controlar o fluxo do jogo, tal como a pontuação.

```php
<?php
// Importamos o ficheiro de configurações da base de dados
require_once("config/db.php");

// Inicializamos a sessão para podermos utilizar o $_SESSION
session_start();

// Verifica que já existe um jogo a decorrer, caso negativo inicializa as variáveis de sessão necessárias para gerir o jogo
if (!isset($_SESSION["questionIndex"])) {
  // Define o índice da primeira questão para 0
  $_SESSION["questionIndex"] = 0;

  // Define o número de respostas corretas do jogador para 0
  $_SESSION["correctAnswers"] = 0;
}

// Verifica se o jogador selecionou alguma opção de resposta
if (isset($_POST["resposta"])) {
  // Verifica se o jogador escolheu a resposta correta
  if (isset($_SESSION["rightAnswer"]) && $_POST["resposta"] === $_SESSION["rightAnswer"]) {
    // Se a resposta estiver correta aumenta 1 valor ao contador de respostas corretas do jogador
    ++$_SESSION["correctAnswers"];
  }

  // Independentemente do resultado avança para a proxima questão
  ++$_SESSION["questionIndex"];
}

// Obtém a proxima questão da base de dados
// Utiliza o LIMIT para obter apenas 1 questão a partir do índice da questão atual, ou seja se queremos a questão número 4, seria 'LIMIT 3, 1'
$questionSQL = "SELECT id, pergunta FROM perguntas LIMIT {$_SESSION['questionIndex']}, 1";
$questionQuery = mysqli_query($connection, $questionSQL);
$questionRow = mysqli_fetch_array($questionQuery);

// Verifica se foi possível obter a proxima questão da base de dados
// Caso não tenha sido possível (valor de null) significa que não existem mais questões
if ($questionRow === null) {
  // Redirecionamos o jogador para a página de resultados
  header("Location: results.php");

  // Devemos parar a execução do código a partir deste ponto para evitar comportamentos inesperados
  return;
}

// Caso existam ainda questões, definimos as variáveis necessárias com os dados da proxima questão
$questionId   = $questionRow['id'];
$questionText = $questionRow['pergunta'];

// Define a array que vai conter as opções de resposta à questão
$answers = array();

// Obtém a lista das respostas correspondentes à questão atual da base de dados
$answersSQL = "SELECT id, resposta, v_f FROM respostas WHERE id_pergunta = {$questionId}";
$answersQuery = mysqli_query($connection, $answersSQL);

// Processamos todas as opções de respostas provenientes da query anterior
while ($row = mysqli_fetch_array($answersQuery)) {
  // Criamos uma array associativa para a opção de resposta atual e associamos os respetivos valores provenientes da base dados
  $answer = array();
  $answer["id"] = $row["id"]; // id único da resposta
  $answer["resposta"] = $row["resposta"]; // texto da resposta
  $answer["v_f"] = $row["v_f"]; // 0 - resposta incorreta; 1 - resposta correta

  // Validamos se a resposta atualmente a ser processada se é a correta
  if ($answer["v_f"] === "1") {
    // Caso afirmativo definimos na variável de sessão rightAnswer o id da resposta correta
    // Utilizamos uma variável de sessão aqui para que depois ao submeter o formulário possamos na linha 18 validar se a resposta dada pelo jogador é a da correta
    $_SESSION["rightAnswer"] = $answer["id"];
  }

  // Adicionamos a opção de resposta atual à array de respostas criada anteriormente na linha 50
  array_push($answers, $answer);
}
?>

<!-- Documento HTML -->

<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz - José Saramago</title>
</head>

<body>
  <h1>Quiz - José Saramago</h1>

  <!-- Mostra qual o número da questão atual -->
  <h2>Questão <?php echo $_SESSION["questionIndex"] + 1 ?></h2>

  <!-- Mostra o texto da questão atual -->
  <h4><?php echo $questionText ?></h4>

  <!-- Criamos um formulário para as respostas, que será submetido ao clicar num dos botões de opção de resposta -->
  <form method="POST">
    <!-- Geramos os botões das respostas à questão com os dados que obtivemos acima da base de dados -->
    <?php
    // Fazemos um ciclo for para cada opção de resposta
    for ($i = 0; $i < count($answers); ++$i) {
      echo "<button name='resposta' value='{$answers[$i]['id']}'>{$answers[$i]['resposta']}</button>";
      echo "<br /><br />";
    }
    ?>
  </form>

  <br /><br />

  <!-- Mostramos um botão ao jogador que permite desistir do jogo se quiser -->
  <a href="giveup.php">Desistir do jogo</a>
</body>

</html>
```

#### resulsts.php

Neste ficheiro apresentamos ao jogador uma mensagem com o resultado do jogo, no formato de `respostasCertas / totalPerguntas`, e um botão que chama o ficheiro `reset.php` para reiniciar as variáveis de controlo do jogo.

```php
<?php
// Inicializamos a sessão para podermos utilizar o $_SESSION
session_start();

// Formatamos o texto de resultado da seguinte forma: respostasCertas / totalQuestões
$resultText = "{$_SESSION["correctAnswers"]} / {$_SESSION["questionIndex"]}";
?>

<!-- Documento HTML -->

<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz - José Saramago</title>
</head>

<body>
  <h1>Quiz - José Saramago</h1>

  <h2>Terminou o jogo!</h2>

  <!-- Mostramos o resultado do jogo -->
  <h3>Resultado: <?php echo $resultText ?></h3>

  <!-- Mostramos um botão que permite reiniciar o jogo e voltar à página inicial -->
  <a href="reset.php">Voltar ao menu inicial</a>
</body>

</html>
```

#### giveup.php

Neste ficheiro apresentamos ao jogador uma mensagem a informar que desistiu do jogo e um botão que chama o ficheiro `reset.php` para reiniciar as variáveis de controlo do jogo.

```php
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - José Saramago</title>
</head>

<body>
    <h1>Quiz - José Saramago</h1>

    <h2>Desistiu do jogo!</h2>

    <h3>Poderá tentar novamente quando quiser.</h3>

    <!-- Mostramos um botão que permite reiniciar o jogo e voltar à página inicial -->
    <a href="reset.php">Voltar ao menu inicial</a>
</body>

</html>
```

#### reset.php

Neste ficheiro começamos por destruir a sessão do PHP que estava a ser utilizada para persistir os dados de controlo do jogo corrente, em seguida redirecionamos o jogador de volta ao menu inicial.

Este ficheiro é muito semelhante ao de um `logout.php` de um sistema de autenticação em PHP.

```php
<?php
// Inicializa a sessão.
session_start();

// Apaga todas as variáveis da sessão
$_SESSION = array();

// Se é preciso matar a sessão, então os cookies de sessão também devem ser apagados.
// Nota: Isto destruirá a sessão, e não apenas os dados!
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

// Por último, destrói a sessão
session_destroy();

// Redirecionamos o utilizador de volta para a página inicial
header("Location: index.php");
```

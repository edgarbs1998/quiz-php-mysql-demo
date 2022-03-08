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
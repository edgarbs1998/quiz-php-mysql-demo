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
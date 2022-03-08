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

<?php
$hostname = "localhost";
$bancodedados = "banca";
$usuario = "root";
$senha = "";

$mysqli = new mysqli($hostname, $usuario, $senha, $bancodedados);
if ($mysqli->connect_errno) {
    echo "falha ao conectar: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
} else 
    echo "Conectado ao bancos de dados"

?>
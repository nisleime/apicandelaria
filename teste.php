<?php
$banco = "candelaria";
$usuario = "root";
$senha = "Ncm@647534";
$hostname = "104.234.173.105";

$conn = new mysqli($hostname, $usuario, $senha, $banco );

if ($conn->connect_errno) {
    die('Falhou em conecta: (' . $conn->connect_errno . ') ' . $conn->connect_error);
}

if (!$conn) {echo "Não foi possível conectar ao banco MySQL.
"; exit;}
else {echo "Parabéns!! A conexão ao banco de dados ocorreu normalmente!.
";}
mysql_close(); 
?>

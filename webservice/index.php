<?php
header("Content-type: text/html; charset=utf-8");

if(isset($_POST['cpf'])){
    $cpftitular       = $_POST['cpf'];
    $versao        = '1.0';    
    $status        = 'Disponivel';
	$conn = new mysqli("104.234.173.105", "root", "Ncm@647534", "candelaria");

    // Consultar as chaves
    $Plano = $conn->query("SELECT * FROM plano  WHERE cpf = '$cpftitular' AND situacao = 'N'");

    if(mysqli_num_rows($Plano) > 0){
        while ($RowPlano = $Plano->fetch_object()) {
		   $id  = $RowPlano->id;
           $nome = $RowPlano->nome;
           $email = $RowPlano->email;
           $cpf = $RowPlano->cpf;
           $nome_mae = $RowPlano->nome_mae;
           $endereco = $RowPlano->endereco;
           $telefone = $RowPlano->telefone;
           $telefone2 = $RowPlano->telefone2;
           $data_nascimento = $RowPlano->data_nascimento;
           $bairro = $RowPlano->data_nascimento;
           $cidade = $RowPlano->cidade;
           $cep = $RowPlano->cep;
           $uf = $RowPlano->uf;
           $sexo = $RowPlano->sexo;
           $plano = $RowPlano->plano;
           $numero = $RowPlano->numero;
           $complemento = $RowPlano->complemento;
           $dt_cad = $RowPlano->dt_cad;
           $situacao = $RowPlano->situacao;
           
        }

		$Deps = $conn->query("SELECT * FROM dependentes  WHERE cpf_titular = '$cpf' AND situacao = 'N'");

		if(mysqli_num_rows($Deps) > 0){
			while ($RowDeps = $Deps->fetch_object()) {
			   $id  = $RowDeps->id;
			   $nome = $RowDeps->nome;
			   $parentesco = $RowDeps->parentesco
			   $cpf = $RowDeps->cpf;
			   $data_nascimento = $RowDeps->data_nascimento;
			   $cpf_titular =  $RowDeps->cpf_titular;
			   $dt_cadastro = $RowDeps->dt_cadastro;
			   $situacao = $RowDeps->situacao;
			   
			}
      
		
      
    }

    mysqli_close($conn);
}

if(isset($_POST['utilizado'])){
	$id          = $_POST['id'];
    $nome        = $_POST['nome'];
    $email       = $_POST['email'];
    $cpf         = $_POST['cpf'];
    $nome_mae    = $_POST['nome_mae'];
    $endereco    = $_POST['endereco'];
    $telefone    = $_POST['telefone'];
    $telefone2   = $_POST['telefone2'];
    $data_nasc   = $_POST['data_nascimento'];
    $bairro      = $_POST['bairro'];
    $cidade      = $_POST['cidade'];
    $cep         = $_POST['cep'];
    $uf          = $_POST['uf'];
    $sexo        = $_POST['sexo'];
    $plano       = $_POST['plano'];
    $numero      = $_POST['numero'];
    $complemento = $_POST['complemento'];
    $situacao    = 'N'; // Novo cliente tem a situação 'N'    
    $conn = new mysqli("104.234.173.105", "root", "Ncm@647534", "candelaria");
	$result = $conn->query("UPDATE plano SET nome = '$nome', email = '$email', cpf = '$cpf', nome_mae = '$nome_mae', 
	endereco = '$endereco', telefone = '$telefone', telefone2 = '$telefone2', data_nascimento = '$data_nasc',
	 bairro = '$bairro', cidade = '$cidade', cep = '$cep', uf = '$uf', sexo = '$sexo', plano = '$plano', numero ='$numero', complemento = '$complemento', situacao = '$situacao'
	 WHERE id = '$id'");


    $result = $conn->query("UPDATE chaves SET situacao = 'S' 
    mysqli_close($conn);
}

if(isset($_POST['cadastrar'])){
    $nome        = $_POST['nome'];
    $email       = $_POST['email'];
    $cpf         = $_POST['cpf'];
    $nome_mae    = $_POST['nome_mae'];
    $endereco    = $_POST['endereco'];
    $telefone    = $_POST['telefone'];
    $telefone2   = $_POST['telefone2'];
    $data_nasc   = $_POST['data_nascimento'];
    $bairro      = $_POST['bairro'];
    $cidade      = $_POST['cidade'];
    $cep         = $_POST['cep'];
    $uf          = $_POST['uf'];
    $sexo        = $_POST['sexo'];
    $plano       = $_POST['plano'];
    $numero      = $_POST['numero'];
    $complemento = $_POST['complemento'];
    $situacao    = 'N'; // Novo cliente tem a situação 'N'
   

    $conn = new mysqli("104.234.173.105", "root", "Ncm@647534", "candelaria");


    // Inserir novo plano
    $result = $conn->query("INSERT INTO plano (nome, email, cpf, nome_mae, endereco, telefone, telefone2, data_nascimento, bairro, cidade, cep, uf, sexo, plano, numero, complemento, situacao) 
                            VALUES ('$nome', '$email', '$cpf', '$nome_mae', '$endereco', '$telefone', '$telefone2', '$data_nasc', '$bairro', '$cidade', '$cep', '$uf', '$sexo', '$plano', '$numero', '$complemento', '$situacao')");
   
    // Inserir dependentes (se houver)
    if (isset($_POST['dependentes'])) {
        $dependentes = $_POST['dependentes']; // Array de dependentes
        foreach ($dependentes as $dependente) {
            $result = $conn->query("INSERT INTO dependentes (nome, parentesco, cpf, data_nascimento, cpf_titular, situacao) 
                                    VALUES ('{$dependente['nome']}', '{$dependente['parentesco']}', '{$dependente['cpf']}', '{$dependente['data_nascimento']}', '$cpf', 'N')");
        }
    }

    mysqli_close($conn);
}

?>

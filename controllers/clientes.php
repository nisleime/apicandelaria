<?php
namespace controllers{
	/*
	Classe pessoa
	*/
	class clientes{
		//Atributo para banco de dados
		private $PDO;

		/*
		__construct
		Conectando ao banco de dados
		*/
		function __construct(){
			$this->PDO = new \PDO('mysql:host=192.185.215.164;dbname=objeti55_fatura', 'objeti55_fatura', '9625asdf@'); //Conexão
			$this->PDO->setAttribute( \PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION ); //habilitando erros do PDO
		}
		/*
		lista
		*/
		public function lista(){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM empresas");
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}

		public function FiltrarLicenca($cnpj) {
			function formatarCNPJ($cnpj) {
				// Remove caracteres não numéricos
				$cnpj = preg_replace("/[^0-9]/", "", $cnpj);
		
				// Adiciona pontos e barras de acordo com o formato padrão
				$cnpjFormatado = substr($cnpj, 0, 2) . '.' .
					substr($cnpj, 2, 3) . '.' .
					substr($cnpj, 5, 3) . '/' .
					substr($cnpj, 8, 4) . '-' .
					substr($cnpj, 12, 2);
		
				return $cnpjFormatado;
			}
		
			function formatarData($data) {
				// Converte a data para o formato "dd/mm/yyyy"
				return date('d/m/Y', strtotime($data));
			}
		
			global $app;
			$sth = $this->PDO->prepare("SELECT data_licenca, status, mensagem_bloqueio FROM empresas WHERE cnpj = :cnpj");
			
			$sth->bindValue(':cnpj', formatarCNPJ($cnpj));
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
		
			// Iterar sobre os resultados e formatar a data
			foreach ($result as &$row) {
				if (isset($row['data_licenca'])) {
					$row['data_licenca'] = formatarData($row['data_licenca']);
				}
			}
		
			// Renderizar a resposta JSON
			$app->render('default.php', ["data" => $result], 200);
		
		}

		public function nova(){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$keys = array_keys($dados); //Paga as chaves do array
			/*
			O uso de prepare e bindValue é importante para se evitar SQL Injection
			*/
			$sth = $this->PDO->prepare("INSERT INTO empresas (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			$sth->execute();
			//Retorna o id inserido
			$app->render('default.php',["data"=>['id'=>$this->PDO->lastInsertId()]],200); 
		}
		
	/*  Cadastros de pagamentos */

		public function cadastrapagamento(){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$keys = array_keys($dados); //Paga as chaves do array
			/*
			O uso de prepare e bindValue é importante para se evitar SQL Injection
			*/
			$sth = $this->PDO->prepare("INSERT INTO boleto_inters (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			$sth->execute();
			//Retorna o id inserido
			$app->render('default.php',["data"=>['id'=>$this->PDO->lastInsertId()]],200); 
		}

		//


		public function listapay(){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM pessoa WHERE status_pagamento = 'PENDENTE'");
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}

		public function listapayatrasados(){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM pessoa WHERE status_pagamento = 'ATRASADO'");
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}

		public function listapaypagos(){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM pessoa WHERE status_pagamento = 'PAGO'");
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}
		
		public function notas(){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM configuracoes");
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}
		/*
		get
		param $id
		*/
		
		public function getCnpj($cnpj){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM empresas WHERE cnpj = :cnpj");
 			$cnpj = preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "\$1.\$2.\$3/\$4-\$5", $cnpj);
			$sth ->bindValue(':cnpj',$cnpj);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}	
		
		public function GetUniqueCNPJ($cnpj){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM pessoa WHERE cnpj = :cnpj");
			$sth ->bindValue(':cnpj',$cnpj);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}

		public function BuscaToken(){
			global $app;
			$sth = $this->PDO->prepare("SELECT token_bearer FROM configuracoes");
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}
		
		public function get($id){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM clientes WHERE razao like Concat(:id,'%')");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}

		public function parcelas($id){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM parcelas WHERE situacao = 'Pendente' and id_cliente = :id");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}

        public function editalicenca($id){
            global $app;
            $sth = $this->PDO->prepare("UPDATE clientes SET data_licensa = 'Utilizada' WHERE id = :id");
            $sth ->bindValue(':id',$id);
            $app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200);
        }

        public function sumparpagas($id){
			global $app;
			$sth = $this->PDO->prepare("SELECT SUM(valor) as TotPagas FROM parcelas WHERE situacao = 'Paga' and id_cliente = :id");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_OBJ);
			$app->render('default.php',["data"=>$result],200); 
		}	
		public function sumparpendentes($id){
			global $app;
			$sth = $this->PDO->prepare("SELECT SUM(valor) as TotPendentes FROM parcelas WHERE situacao = 'Pendente' and id_cliente = :id");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_OBJ);
			$app->render('default.php',["data"=>$result],200); 
		}						
			
		public function boletos($id){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM boletos WHERE id_cliente = :id");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}		
		public function boletospagar($id){
			global $app;
			$sth = $this->PDO->prepare("SELECT * FROM boletos WHERE situacao = 'Pagar' and id_cliente = :id");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}				
		/*
		nova
		*/
		
		
		public function criaparcelas(){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$keys = array_keys($dados); //Paga as chaves do array
			/*
			O uso de prepare e bindValue é importante para se evitar SQL Injection
			*/
			$sth = $this->PDO->prepare("INSERT INTO parcelas (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			$sth->execute();
			//Retorna o id inserido
			$app->render('default.php',["data"=>['id'=>$this->PDO->lastInsertId()]],200); 
		}

		public function editaparcela($id){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$sets = [];
			foreach ($dados as $key => $VALUES) {
				$sets[] = $key." = :".$key;
			}

			$sth = $this->PDO->prepare("UPDATE clientes SET ".implode(',', $sets)." WHERE id = :id");
			$sth ->bindValue(':id',$id);
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			//Retorna status da edição
			$app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200); 
		}
		public function gravaboleto(){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$keys = array_keys($dados); //Paga as chaves do array
			/*
			O uso de prepare e bindValue é importante para se evitar SQL Injection
			*/
			$sth = $this->PDO->prepare("INSERT INTO boletos (".implode(',', $keys).") VALUES (:".implode(",:", $keys).")");
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			$sth->execute();
			//Retorna o id inserido
			$app->render('default.php',["data"=>['id'=>$this->PDO->lastInsertId()]],200); 
		}			
		public function editaboleto($id){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$sets = [];
			foreach ($dados as $key => $VALUES) {
				$sets[] = $key." = :".$key;
			}

			$sth = $this->PDO->prepare("UPDATE boleto SET ".implode(',', $sets)." WHERE id = :id");
			$sth ->bindValue(':id',$id);
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			//Retorna status da edição
			$app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200); 
		}
		/*
		editar
		param $id
		*/
		public function editar($id) {
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados) == 0) ? $_POST : $dados;
		
			// Formatando o CNPJ
			$idFormatado = $this->formatarCNPJ($id);
		
			$sets = [];
			foreach ($dados as $key => $VALUES) {
				$sets[] = $key . " = :" . $key;
			}
		
			$sth = $this->PDO->prepare("UPDATE empresas SET " . implode(',', $sets) . " WHERE cnpj = :id");
			$sth->bindValue(':id', $idFormatado);
		
			foreach ($dados as $key => $value) {
				$sth->bindValue(':' . $key, $value);
			}
		
			// Executa a atualização
			$executouAtualizacao = $sth->execute();
		
			// Verifica se a atualização foi bem-sucedida
			if ($executouAtualizacao) {
				$app->render('default.php', ["data" => ['status' => true, 'cnpj' => $idFormatado]], 200);
			} else {
				$app->render('default.php', ["data" => ['status' => false, 'cnpj' => $idFormatado]], 200);
			}
		}
		
		// Função para formatar o CNPJ
		private function formatarCNPJ($cnpj) {
			$cnpjLimpo = preg_replace("/[^0-9]/", "", $cnpj);
			return substr($cnpjLimpo, 0, 2) . '.' . substr($cnpjLimpo, 2, 3) . '.' . substr($cnpjLimpo, 5, 3) . '/' . substr($cnpjLimpo, 8, 4) . '-' . substr($cnpjLimpo, 12, 2);
		}
		
		

		/*
		excluir
		param $id

		*/
        public function editarcl($id){
            global $app;
            $dados = json_decode($app->request->getBody(), true);
            $dados = (sizeof($dados)==0)? $_POST : $dados;
            $sets = [];
            foreach ($dados as $key => $VALUES) {
                $sets[] = $key." = :".$key;
            }

            $sth = $this->PDO->prepare("UPDATE clientes SET ".implode(',', $sets)." WHERE id = :id");
            $sth ->bindValue(':id',$id);
            foreach ($dados as $key => $value) {
                $sth ->bindValue(':'.$key,$value);
            }
            //Retorna status da edição
            $app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200);
        }

        /*

        */

        public function excluir($id){
			global $app;
			$sth = $this->PDO->prepare("DELETE FROM clientes WHERE id = :id");
			$sth ->bindValue(':id',$id);
			$app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200);
		}
	}
}
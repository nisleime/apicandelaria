<?php
namespace controllers;

class Plano {
    private $PDO;
    private $app;

    // Conexão com o banco de dados
    public function __construct($app) {
        $host = $_ENV['CODEEASY_GERENCIADOR_MYSQL_HOST'];
        $dbname = $_ENV['CODEEASY_GERENCIADOR_MYSQL_DBNAMO'];
        $user = $_ENV['CODEEASY_GERENCIADOR_MYSQL_USER'];
        $password = $_ENV['CODEEASY_GERENCIADOR_MYSQL_PASSWORD'];
             
        $this->PDO = new \PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->app = $app;
    }

    // Lista todos os registros da tabela plano
    public function lista($request, $response, $args) {
        try {
            // Realiza a consulta para pegar todos os registros da tabela 'plano'
            $query = $this->PDO->query('SELECT * FROM plano');
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);  // Usar fetchAll para pegar todos os registros
    
            // Se há resultados, retorne com sucesso
            if ($result) {
                $data = [
                  'data' => $result
                ];
            } else {
                // Caso não haja resultados
                $data = [
                    'success' => false,
                    'message' => 'No plans found in the database.'
                ];
            }
    
            // Escreve a resposta no corpo e retorna com o cabeçalho apropriado
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
    
        } catch (\PDOException $e) {
            // Caso ocorra um erro na consulta
            $error = [
                'success' => false,
                'message' => 'Database query failed: ' . $e->getMessage()
            ];
    
            // Escreve o erro no corpo da resposta
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    
     
  

    public function listar($request, $response, $args) {
        // Obtendo os parâmetros de consulta diretamente do objeto request
        $data_inicial = $request->getQueryParams()['data_inicial'] ?? null;
        $data_final = $request->getQueryParams()['data_final'] ?? null;
        $palavra_chave = $request->getQueryParams()['querystring'] ?? null;
    
        // Verificando se as datas foram fornecidas
        if (!$data_inicial || !$data_final) {
            $error = [
                'success' => false,
                'message' => 'Datas são obrigatórias'
            ];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        try {
            // Consulta para listar os planos no intervalo de datas
            $sthPlano = $this->PDO->prepare(
                "SELECT * 
                 FROM plano 
                 WHERE situacao = 'N' 
                   AND dt_cad BETWEEN :data_inicial AND :data_final"
            );
            $sthPlano->bindValue(':data_inicial', $data_inicial);
            $sthPlano->bindValue(':data_final', $data_final);
            $sthPlano->execute();
            $planos = $sthPlano->fetchAll(\PDO::FETCH_ASSOC);
    
            // Verifica se há planos encontrados
            if (empty($planos)) {
                $responseData = [
                    'success' => false,
                    'message' => 'Nenhum plano encontrado no intervalo de datas fornecido.'
                ];
                $response->getBody()->write(json_encode($responseData));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
    
            // Atualização caso a palavra-chave seja 'spl'
            if ($palavra_chave === 'spl') {
                $sthUpdate = $this->PDO->prepare(
                    "UPDATE plano 
                     SET situacao = 'S' 
                     WHERE situacao = 'N' 
                       AND dt_cad BETWEEN :data_inicial AND :data_final"
                );
                $sthUpdate->bindValue(':data_inicial', $data_inicial);
                $sthUpdate->bindValue(':data_final', $data_final);
                $sthUpdate->execute();
            }
    
            // Associa os dependentes a cada plano
            foreach ($planos as &$plano) {
                $cpf = $plano['cpf'];
                $sthDependentes = $this->PDO->prepare(
                    "SELECT * 
                     FROM dependentes 
                     WHERE cpf_titular = :cpf"
                );
                $sthDependentes->bindValue(':cpf', $cpf);
                $sthDependentes->execute();
                $plano['dependentes'] = $sthDependentes->fetchAll(\PDO::FETCH_ASSOC);
            }
    
            // Monta a resposta final
            $responseData = [
                'success' => true,
                'message' => 'Registros encontrados com sucesso!',
                'data' => $planos
            ];
    
            $response->getBody()->write(json_encode($responseData));
            return $response->withHeader('Content-Type', 'application/json');
    
        } catch (\PDOException $e) {
            // Retorna erro em caso de falha
            $error = [
                'success' => false,
                'message' => 'Erro na consulta ao banco de dados: ' . $e->getMessage()
            ];
    
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    
    
    // Obtém um registro específico pelo ID
    public function busca($request, $response, $args) {
        try {
            $id = $args['id'];  // Pegando o parâmetro 'id' da URL
            $sth = $this->PDO->prepare("SELECT * FROM plano WHERE id = :id");
            $sth->bindValue(':id', $id, \PDO::PARAM_INT);
            $sth->execute();
            $result = $sth->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                // Retorna o resultado encontrado com o cabeçalho correto
                $response->getBody()->write(json_encode($result));  // Escreve o resultado como JSON
                return $response->withHeader('Content-Type', 'application/json');  // Define o tipo de conteúdo como JSON
            } else {
                $error = ["error" => "Registro não encontrado"];
                $response->getBody()->write(json_encode($error));  // Escreve o erro como JSON
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);  // Retorna status 404
            }
        } catch (\PDOException $e) {
            $error = ["error" => $e->getMessage()];
            $response->getBody()->write(json_encode($error));  // Escreve o erro de banco de dados
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);  // Retorna status 500
        }
    }
    
     
    public function novo($request, $response) {
        // Pega os dados enviados no corpo da requisição (JSON)
        $dados = $request->getParsedBody();

        // Se os dados estiverem vazios, tenta pegar os dados de $_POST (caso o cliente envie como formulário)
        if (empty($dados)) {
            $dados = $_POST;
        }

        // Verifica se o campo 'nome' está presente
        if (empty($dados['nome'])) {
            $error = ['error' => 'Campo "nome" é obrigatório'];
            $response->getBody()->write(json_encode($error));  // Escreve o erro no corpo da resposta
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');  // Retorna o status 400
        }

        // Depuração: exibe os dados recebidos no log
        error_log(print_r($dados, true));

        try {
            // Monta a query de inserção dinamicamente com os dados recebidos
            $keys = array_keys($dados);
            $placeholders = array_map(function($key) { return ':' . $key; }, $keys);
            $query = "INSERT INTO plano (" . implode(',', $keys) . ") VALUES (" . implode(',', $placeholders) . ")";

            // Depuração: exibe a consulta SQL gerada no log
            error_log("SQL Query: " . $query);

            // Prepara a consulta
            $sth = $this->PDO->prepare($query);

            // Faz o binding dos valores dos dados
            foreach ($dados as $key => $value) {
                // Faz o binding de cada valor de dados para a query preparada
                $sth->bindValue(':' . $key, $value);
            }

            // Executa a query
            $sth->execute();

            // Retorna o ID do novo registro inserido
            $responseData = ["id" => $this->PDO->lastInsertId()];

            // Escreve a resposta com o ID inserido e o tipo de conteúdo JSON
            $response->getBody()->write(json_encode($responseData));  // Escreve os dados no corpo da resposta
            return $response->withHeader('Content-Type', 'application/json');  // Define o tipo de conteúdo como JSON

        } catch (\PDOException $e) {
            // Caso ocorra algum erro, retorna o erro com código 500
            $error = ["error" => $e->getMessage()];
            $response->getBody()->write(json_encode($error));  // Escreve o erro no corpo da resposta
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');  // Retorna o status 500
        }
    }
    
    public function receberDados($request, $response) {
        // Pega os dados enviados no corpo da requisição (JSON)
        $dados = $request->getParsedBody();

        // Verifica se os dados foram recebidos
        if (empty($dados)) {
            $dados = ['error' => 'Nenhum dado recebido'];
        }

        // Exibe os dados recebidos no log para depuração
        error_log(print_r($dados, true));

        // Retorna os dados recebidos como resposta JSON
        $response->getBody()->write(json_encode($dados));

        // Retorna a resposta com o tipo de conteúdo como JSON
        return $response->withHeader('Content-Type', 'application/json');
    }
     
    

    // Atualiza um registro específico
    public function editar($request, $response, $args) {
        // Pega os dados enviados no corpo da requisição (JSON)
        $dados = $request->getParsedBody();
    
        // Verifica se o campo 'id' está presente e se o campo 'nome' também está presente
        if (empty($dados['id']) || empty($dados['nome'])) {
            $error = ['error' => 'Campos "id" e "nome" são obrigatórios'];
            $response->getBody()->write(json_encode($error));  // Escreve o erro no corpo da resposta
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');  // Retorna o status 400
        }
    
        // Verifica se o registro com o 'id' fornecido existe
        $query = "SELECT * FROM plano WHERE id = :id";
        $sth = $this->PDO->prepare($query);
        $sth->bindValue(':id', $dados['id']);
        $sth->execute();
    
        // Se o registro não for encontrado
        if ($sth->rowCount() == 0) {
            $error = ['error' => 'Registro não encontrado'];
            $response->getBody()->write(json_encode($error));  // Escreve o erro no corpo da resposta
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');  // Retorna o status 404
        }
    
        // Monta a query de atualização dinamicamente com os dados recebidos
        $keys = array_keys($dados);
        $sets = array_map(function($key) { return $key . ' = :' . $key; }, $keys);
        $query = "UPDATE plano SET " . implode(',', $sets) . " WHERE id = :id";
    
        try {
            // Prepara a consulta
            $sth = $this->PDO->prepare($query);
    
            // Faz o binding dos valores dos dados
            foreach ($dados as $key => $value) {
                $sth->bindValue(':' . $key, $value);
            }
    
            // Faz o binding do ID do registro que será atualizado
            $sth->bindValue(':id', $dados['id']);
    
            // Executa a query de atualização
            $sth->execute();
    
            // Responde com o status 200 (OK)
            $responseData = ["message" => "Registro atualizado com sucesso"];
            $response->getBody()->write(json_encode($responseData));  // Escreve a mensagem no corpo da resposta
            return $response->withHeader('Content-Type', 'application/json');  // Define o tipo de conteúdo como JSON
    
        } catch (\PDOException $e) {
            // Caso ocorra algum erro, retorna o erro com código 500
            $error = ["error" => $e->getMessage()];
            $response->getBody()->write(json_encode($error));  // Escreve o erro no corpo da resposta
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');  // Retorna o status 500
        }
    }
    

    // Remove um registro específico
    public function excluir($request, $response, $args) {
        // Pega os dados enviados no corpo da requisição (JSON)
        $dados = $request->getParsedBody();
    
        // Verifica se o campo 'id' está presente
        if (empty($dados['id'])) {
            $error = ['error' => 'Campo "id" é obrigatório'];
            $response->getBody()->write(json_encode($error));  // Escreve o erro no corpo da resposta
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');  // Retorna o status 400
        }
    
        // Verifica se o registro com o 'id' fornecido existe
        $query = "SELECT * FROM plano WHERE id = :id";
        $sth = $this->PDO->prepare($query);
        $sth->bindValue(':id', $dados['id']);
        $sth->execute();
    
        // Se o registro não for encontrado
        if ($sth->rowCount() == 0) {
            $error = ['error' => 'Registro não encontrado'];
            $response->getBody()->write(json_encode($error));  // Escreve o erro no corpo da resposta
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');  // Retorna o status 404
        }
    
        try {
            // Prepara a query de exclusão
            $query = "DELETE FROM plano WHERE id = :id";
            $sth = $this->PDO->prepare($query);
            $sth->bindValue(':id', $dados['id']);
    
            // Executa a query de exclusão
            $sth->execute();
    
            // Responde com o status 200 (OK) e uma mensagem de sucesso
            $responseData = ["message" => "Registro excluído com sucesso"];
            $response->getBody()->write(json_encode($responseData));  // Escreve a mensagem no corpo da resposta
            return $response->withHeader('Content-Type', 'application/json');  // Define o tipo de conteúdo como JSON
    
        } catch (\PDOException $e) {
            // Caso ocorra algum erro, retorna o erro com código 500
            $error = ["error" => $e->getMessage()];
            $response->getBody()->write(json_encode($error));  // Escreve o erro no corpo da resposta
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');  // Retorna o status 500
        }
    }
    
    // Responde com JSON
    private function respond($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    // Responde com erro e status apropriado
    private function respondError($status, $message) {
        $this->app->response->setStatus($status);
        $this->respond(["error" => $message]);
    }
}

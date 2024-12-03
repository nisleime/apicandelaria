<?php
namespace controllers;

class Dependentes {
    private $PDO;
    private $app;

    // Construtor para inicializar o PDO e o Slim
    public function __construct($app) {
        $host = $_ENV['CODEEASY_GERENCIADOR_MYSQL_HOST'];
        $dbname = $_ENV['CODEEASY_GERENCIADOR_MYSQL_DBNAMO'];
        $user = $_ENV['CODEEASY_GERENCIADOR_MYSQL_USER'];
        $password = $_ENV['CODEEASY_GERENCIADOR_MYSQL_PASSWORD'];
             
        $this->PDO = new \PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->app = $app;
    }

    // Lista registros filtrados pelo cpf_titular
    public function buscaPorCpf($request, $response) {
        try {
            // Obtém os dados enviados no corpo da requisição
            $dados = $request->getParsedBody();
    
            // Verifica se o CPF foi enviado
            if (empty($dados['cpf_titular'])) {
                $error = ['error' => 'O campo "cpf_titular" é obrigatório'];
                $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json'); // Retorna status 400
            }
    
            $cpf_titular = $dados['cpf_titular'];
    
            // Prepara a consulta SQL
            $sth = $this->PDO->prepare("SELECT * FROM dependentes WHERE cpf_titular = :cpf_titular");
            $sth->bindValue(':cpf_titular', $cpf_titular, \PDO::PARAM_STR);
            $sth->execute();
    
            // Obtém o resultado da consulta
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
    
            if ($result) {
                // Retorna os dados encontrados
                $response->getBody()->write(json_encode($result)); // Escreve os dados como JSON
                return $response->withHeader('Content-Type', 'application/json'); // Define o tipo de conteúdo como JSON
            } else {
                // Caso não encontre resultados
                $error = ['error' => 'Nenhum registro encontrado para o CPF fornecido'];
                $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json'); // Retorna status 404
            }
        } catch (\PDOException $e) {
            // Caso ocorra algum erro de banco de dados
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json'); // Retorna status 500
        }
    }
    

    // Obtém um registro específico pelo ID
    public function getDependentes($request, $response) {
        try {
            // Obtém os dados enviados no corpo da requisição
            $dados = $request->getParsedBody();
    
            // Verifica se o CPF foi enviado
            if (empty($dados['cpf'])) {
                $error = ['error' => 'O campo "cpf" é obrigatório'];
                $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json'); // Retorna status 400
            }
    
            $cpf = $dados['cpf'];
    
            // Prepara a consulta SQL
            $sth = $this->PDO->prepare("SELECT * FROM dependentes WHERE cpf = :cpf");
            $sth->bindValue(':cpf', $cpf, \PDO::PARAM_STR);
            $sth->execute();
    
            // Obtém os resultados
            $result = $sth->fetchAll(\PDO::FETCH_ASSOC);
    
            if ($result) {
                // Retorna os dados encontrados
                $response->getBody()->write(json_encode($result)); // Escreve os dados no corpo da resposta
                return $response->withHeader('Content-Type', 'application/json'); // Define o tipo de conteúdo como JSON
            } else {
                // Caso não encontre resultados
                $error = ['error' => 'Nenhum registro encontrado para o CPF fornecido'];
                $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json'); // Retorna status 404
            }
        } catch (\PDOException $e) {
            // Caso ocorra algum erro de banco de dados
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json'); // Retorna status 500
        }
    }
    

    // Adiciona um novo registro
    public function novo($request, $response) {
        try {
            // Obtém os dados enviados no corpo da requisição
            $dados = $request->getParsedBody();
    
            // Verifica se os dados foram enviados corretamente
            if (empty($dados) || !is_array($dados)) {
                $error = ['error' => 'Os dados enviados são inválidos ou estão ausentes'];
                $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json'); // Retorna status 400
            }
    
            // Monta os campos e valores para a query
            $keys = array_keys($dados);
            $placeholders = array_map(function($key) { return ':' . $key; }, $keys);
            $query = "INSERT INTO dependentes (" . implode(',', $keys) . ") VALUES (" . implode(',', $placeholders) . ")";
    
            // Prepara a consulta SQL
            $sth = $this->PDO->prepare($query);
    
            // Faz o binding dos valores
            foreach ($dados as $key => $value) {
                $sth->bindValue(':' . $key, $value);
            }
    
            // Executa a query
            $sth->execute();
    
            // Retorna o ID do novo registro inserido
            $responseData = ['id' => $this->PDO->lastInsertId()];
            $response->getBody()->write(json_encode($responseData)); // Escreve os dados no corpo da resposta
            return $response->withHeader('Content-Type', 'application/json'); // Define o tipo de conteúdo como JSON
        } catch (\PDOException $e) {
            // Caso ocorra algum erro, retorna o erro com código 500
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json'); // Retorna status 500
        }
    }
    

    // Atualiza um registro específico
    public function alterar($request, $response) {
        try {
            // Obtém os dados enviados no corpo da requisição
            $dados = $request->getParsedBody();
    
            // Verifica se o CPF foi enviado
            if (empty($dados['cpf'])) {
                $error = ['error' => 'O campo "cpf" é obrigatório para a atualização'];
                $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json'); // Retorna status 400
            }
    
            $cpf = $dados['cpf']; // Armazena o CPF para o filtro
            unset($dados['cpf']); // Remove o CPF dos dados a serem atualizados
    
            // Verifica se há outros campos para atualização
            if (empty($dados)) {
                $error = ['error' => 'Nenhum campo fornecido para atualização'];
                $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json'); // Retorna status 400
            }
    
            // Monta a query de atualização dinamicamente
            $fields = array_map(function($key) {
                return "$key = :$key";
            }, array_keys($dados));
            $query = "UPDATE dependentes SET " . implode(', ', $fields) . " WHERE cpf = :cpf";
    
            // Prepara a consulta SQL
            $sth = $this->PDO->prepare($query);
    
            // Faz o binding dos valores
            foreach ($dados as $key => $value) {
                $sth->bindValue(':' . $key, $value);
            }
            $sth->bindValue(':cpf', $cpf); // Faz o binding do CPF
    
            // Executa a query
            $sth->execute();
    
            // Verifica se a atualização foi realizada
            if ($sth->rowCount() > 0) {
                $responseData = ['message' => 'Registro atualizado com sucesso'];
                $response->getBody()->write(json_encode($responseData)); // Escreve os dados no corpo da resposta
                return $response->withHeader('Content-Type', 'application/json'); // Define o tipo de conteúdo como JSON
            } else {
                $error = ['error' => 'Nenhum registro foi encontrado com o CPF fornecido'];
                $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json'); // Retorna status 404
            }
        } catch (\PDOException $e) {
            // Caso ocorra algum erro, retorna o erro com código 500
            $error = ['error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json'); // Retorna status 500
        }
    }
    

    // Remove um registro específico
    public function excluir($request, $response, $args) {
        // Pega os dados enviados no corpo da requisição (JSON)
        $dados = $request->getParsedBody();
    
        // Verifica se o campo 'cpf' está presente
        if (empty($dados['cpf'])) {
            $error = ['error' => 'Campo "cpf" é obrigatório'];
            $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json'); // Retorna o status 400
        }
    
        // Verifica se o registro com o 'cpf' fornecido existe
        $query = "SELECT * FROM dependentes WHERE cpf = :cpf";
        $sth = $this->PDO->prepare($query);
        $sth->bindValue(':cpf', $dados['cpf']);
        $sth->execute();
    
        // Se o registro não for encontrado
        if ($sth->rowCount() == 0) {
            $error = ['error' => 'Registro não encontrado'];
            $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json'); // Retorna o status 404
        }
    
        try {
            // Prepara a query de exclusão
            $query = "DELETE FROM dependentes WHERE cpf = :cpf";
            $sth = $this->PDO->prepare($query);
            $sth->bindValue(':cpf', $dados['cpf']);
    
            // Executa a query de exclusão
            $sth->execute();
    
            // Responde com o status 200 (OK) e uma mensagem de sucesso
            $responseData = ["message" => "Registro excluído com sucesso"];
            $response->getBody()->write(json_encode($responseData)); // Escreve a mensagem no corpo da resposta
            return $response->withHeader('Content-Type', 'application/json'); // Define o tipo de conteúdo como JSON
    
        } catch (\PDOException $e) {
            // Caso ocorra algum erro, retorna o erro com código 500
            $error = ["error" => $e->getMessage()];
            $response->getBody()->write(json_encode($error)); // Escreve o erro no corpo da resposta
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json'); // Retorna o status 500
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

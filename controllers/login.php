<?php
namespace controllers;

use DateTime;
use Firebase\JWT\JWT;

class Login {
    private $PDO;
    private $app;

    // Construtor para inicializar o PDO e o Slim
    public function __construct($app) {
        // Conexão com o banco de dados (credenciais devem estar em um local seguro)
        $host = $_ENV['CODEEASY_GERENCIADOR_MYSQL_HOST'];
        $dbname = $_ENV['CODEEASY_GERENCIADOR_MYSQL_DBNAMO'];
        $user = $_ENV['CODEEASY_GERENCIADOR_MYSQL_USER'];
        $password = $_ENV['CODEEASY_GERENCIADOR_MYSQL_PASSWORD'];
       
             
        $this->PDO = new \PDO("mysql:host=$host;dbname=$dbname", $user, $password);
        $this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->app = $app;
    }

    public function novo($request, $response)
{
    try {
        // Obtém os dados enviados no corpo da requisição
        $dados = $request->getParsedBody();

        // Verifica se os dados foram enviados corretamente
        if (empty($dados) || !is_array($dados)) {
            $error = ['success' => false, 'message' => 'Os dados enviados são inválidos ou estão ausentes.'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Validação: Verifica se campos obrigatórios estão presentes
        $camposObrigatorios = ['nome', 'email', 'senha']; // Exemplo de campos obrigatórios
        foreach ($camposObrigatorios as $campo) {
            if (empty($dados[$campo])) {
                $error = ['success' => false, 'message' => "O campo '{$campo}' é obrigatório."];
                $response->getBody()->write(json_encode($error));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }
        }

        // Gera o hash da senha antes de armazenar
        $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);

        // Monta os campos e valores para a query
        $keys = array_keys($dados);
        $placeholders = array_map(fn($key) => ':' . $key, $keys);
        $query = "INSERT INTO usuarios (" . implode(',', $keys) . ") VALUES (" . implode(',', $placeholders) . ")";

        // Prepara a consulta SQL
        $sth = $this->PDO->prepare($query);

        // Faz o binding dos valores
        foreach ($dados as $key => $value) {
            $sth->bindValue(':' . $key, $value);
        }

        // Executa a query
        $sth->execute();

        // Retorna o ID do novo registro inserido
        $responseData = [
            'success' => true,
            'message' => 'Usuário cadastrado com sucesso.',
            'id' => $this->PDO->lastInsertId()
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\PDOException $e) {
        // Caso ocorra algum erro, retorna o erro com código 500
        $error = [
            'success' => false,
            'message' => 'Erro ao processar a solicitação.',
            'error' => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}

public function verificarLogin($request, $response)
{
    try {
        // Obtém os dados do corpo da requisição
        $dados = $request->getParsedBody();

        // Verifica se o e-mail e a senha foram fornecidos
        if (empty($dados['email']) || empty($dados['senha'])) {
            $error = ['error' => 'E-mail e senha são obrigatórios'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $email = $dados['email'];
        $senha = $dados['senha'];

        // Consulta para verificar se o e-mail existe
        $sth = $this->PDO->prepare("SELECT * FROM usuarios WHERE email = :email");
        $sth->bindValue(':email', $email, \PDO::PARAM_STR);
        $sth->execute();
        $usuario = $sth->fetch(\PDO::FETCH_ASSOC);

        // Verifica se o usuário foi encontrado
        if (!$usuario) {
            $error = ['error' => 'E-mail não encontrado'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Verifica se a senha está correta
        if (!password_verify($senha, $usuario['senha'])) {
            $error = ['error' => 'Senha incorreta'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        $validadedias = $_ENV['CODEEASY_GERENCIADOR_TOKEN_DIAS']; 

        //var_dump($validadedias);
        // Geração de tokens
        $experedAt = (new \DateTime())->modify('+'.$validadedias.' days')->format('Y-m-d H:i:s'); // CODEEASY_GERENCIADOR_TOKEN_DIAS
       
        $tokenPayload = [
            'sub' => $usuario['id'],
            'name' => $usuario['nome'],
            'email' => $usuario['email'],
            'expired_at' => $experedAt
        ];

        $token = JWT::encode($tokenPayload, $_ENV['JWT_SECRET_KEY']);
        $refreshTokenPayload = ['email' => $usuario['email']];
        $refreshToken = JWT::encode($refreshTokenPayload,  $_ENV['JWT_SECRET_KEY']);

        // Verifica se o token já existe para este usuário
        $sthToken = $this->PDO->prepare("SELECT * FROM tokens WHERE usuarios_id = :usuarios_id");
        $sthToken->bindValue(':usuarios_id', $usuario['id'], \PDO::PARAM_INT);
        $sthToken->execute();
        $existingToken = $sthToken->fetch(\PDO::FETCH_ASSOC);

        if ($existingToken) {
            // Atualiza o token existente
            $sthUpdate = $this->PDO->prepare("
                UPDATE tokens 
                SET token = :token, refresh_token = :refresh_token, expired_at = :expired_at, active = 1 
                WHERE usuarios_id = :usuarios_id
            ");
        } else {
            // Insere um novo token
            $sthUpdate = $this->PDO->prepare("
                INSERT INTO tokens (usuarios_id, token, refresh_token, expired_at, active) 
                VALUES (:usuarios_id, :token, :refresh_token, :expired_at, 1)
            ");
        }

        // Bind dos parâmetros para inserção/atualização
        $sthUpdate->bindValue(':usuarios_id', $usuario['id'], \PDO::PARAM_INT);
        $sthUpdate->bindValue(':token', $token, \PDO::PARAM_STR);
        $sthUpdate->bindValue(':refresh_token', $refreshToken, \PDO::PARAM_STR);
        $sthUpdate->bindValue(':expired_at', $experedAt, \PDO::PARAM_STR);
        $sthUpdate->execute();

        unset($usuario['senha']); // Remove a senha dos dados retornados

        $responseData = [
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'usuario' => $usuario,
            'Token' => $token,
            'RefreshToken' => $refreshToken,
        ];

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (\PDOException $e) {
        // Trata erros no banco de dados
        $error = ['error' => 'Erro no servidor: ' . $e->getMessage()];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}

public function alterarSenha($request, $response)
{
    try {
        // Obtém os dados enviados no corpo da requisição
        $dados = $request->getParsedBody();

        // Verifica se os dados foram enviados corretamente
        if (empty($dados) || !is_array($dados)) {
            $error = ['success' => false, 'message' => 'Os dados enviados são inválidos ou estão ausentes.'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Verifica se o campo 'senha_nova' está presente nos dados
        if (empty($dados['senha_nova'])) {
            $error = ['success' => false, 'message' => 'O campo "senha_nova" é obrigatório.'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Obtém o token decodificado diretamente da requisição (feito automaticamente pelo middleware)
        $token = $request->getAttribute('jwt');  // O token já foi validado pelo middleware

        // Verifica se o token contém o ID do usuário (geralmente 'sub' no payload do JWT)
        if (empty($token) || empty($token['sub'])) {
            $error = ['success' => false, 'message' => 'Token inválido ou ausente.'];
            $response->getBody()->write(json_encode($error));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // O 'sub' do token é o ID do usuário
        $usuarioId = $token['sub'];

        // Gera o hash da nova senha
        $novaSenhaHash = password_hash($dados['senha_nova'], PASSWORD_DEFAULT);

        // Atualiza a senha do usuário na tabela 'usuarios'
        $sqlUpdate = "UPDATE usuarios SET senha = :senha WHERE id = :usuario_id";
        $sthUpdate = $this->PDO->prepare($sqlUpdate);
        $sthUpdate->bindValue(':senha', $novaSenhaHash);
        $sthUpdate->bindValue(':usuario_id', $usuarioId);
        $sthUpdate->execute();

        // Retorna uma resposta de sucesso
        $responseData = [
            'success' => true,
            'message' => 'Senha atualizada com sucesso.'
        ];
        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\PDOException $e) {
        // Caso ocorra algum erro na consulta ao banco
        $error = [
            'success' => false,
            'message' => 'Erro ao processar a solicitação.',
            'error' => $e->getMessage()
        ];
        $response->getBody()->write(json_encode($error));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}



}

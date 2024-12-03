<?php

require __DIR__ . '/vendor/autoload.php';
//require_once './vendor/autoload.php';
/*require_once './env.php';
require_once './src/slimConfiguration.php';
//require_once './src/basicAuth.php';
require_once './src/jwtAuth.php';
//require_once './routes/index.php';
*/

use controllers\Dependentes;
use controllers\plano;
use controllers\Login;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;



use Dotenv\Dotenv;
use Tuupola\Middleware\JwtAuthentication;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();



$app = AppFactory::create();

//$app->add($jwtMiddleware);

// Configura o middleware JwtAuthentication
$app->add(new JwtAuthentication([
    "secret" => $_ENV['JWT_SECRET_KEY'], // Substitua pela sua chave secreta
    "attribute" => "jwt",               // Atributo para armazenar os dados do token decodificado
    "secure" => false,                  // Permite conexões HTTP para testes (altere para 'true' em produção)
    "path" => ["/teste"],               // Define as rotas que requerem autenticação
    "ignore" => [                       // Define as rotas que não requerem autenticação
        "/auth/novo",
        "/auth/login",
        "/auth/recovery"
    ],
    "error" => function (Response $response, $arguments) {
        $data = ["error" => "Token inválido ou ausente"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader("Content-Type", "application/json")->withStatus(401);
    }
]));


$app->addBodyParsingMiddleware(); 

// Rota protegida (requer token JWT válido)
$app->get('/teste', function (Request $request, Response $response, array $args) {
    // Recuperar dados do token decodificado
    $token = $request->getAttribute("jwt");

    // Caso o token não esteja presente ou válido
    if ($token === null) {
        $data = ["error" => "Token não encontrado ou inválido"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader("Content-Type", "application/json")->withStatus(401);
    }

    // Dados da resposta
    $data = [
        "message" => "Hello World com autenticação",
        "token_data" => $token
    ];

    // Retorna os dados em formato JSON
    $response->getBody()->write(json_encode($data));
    return $response->withHeader("Content-Type", "application/json");
});

$app->group('/api', function () use ($app) {
    // Rota protegida (token JWT obrigatório)
    $app->get('/', function(Request $request, Response $response, $args) {
        $response->getBody()->write("Hello, World!");
        return $response;
    });

    $app->get('/fruits', function(Request $request, Response $response) {
        $controller = new \controllers\Plano();
        $fruits = $controller->frutas();
        $response->getBody()->write(json_encode($fruits));
        return $response->withHeader('Content-type', 'application/json');
    });

    $app->get('/test-connection', function($request, $response, $args) {
        $controller = new \controllers\TestConnection();
        return $controller->test($request, $response, $args);
    });

    $app->get('/plano/lista', [plano::class, 'lista']);
    $app->get('/plano/listar', function($request, $response, $args) {
        $controller = new \controllers\Plano($app);  // Ou o nome correto do seu controlador
        return $controller->listar($request, $response, $args);
    });

    $app->get('/plano/{id}', function($request, $response, $args) use ($app) {
        $controller = new \controllers\Plano($app);  // Passando $app para o controlador
        return $controller->busca($request, $response, $args);  // Chamando a função 'busca'
    });

    // Rota para adicionar um novo plano
    $app->post('/plano/novo', [plano::class, 'novo']);
    // Adiciona a rota POST para /dados
    $app->post('/dados', [plano::class, 'receberDados']);
    $app->put('/plano/editar', [plano::class, 'editar']);
    $app->delete('/plano/excluir', [plano::class, 'excluir']);
    $app->post('/dependentes/busca-por-cpf', [dependentes::class, 'buscaPorCpf']);
    $app->post('/dependentes/dependente', [dependentes::class, 'getDependentes']);
    $app->post('/dependentes/novo', [dependentes::class, 'novo']);
    $app->put('/dependentes/alterar', [dependentes::class, 'alterar']);
    $app->delete('/dependentes/excluir', [dependentes::class, 'excluir']);
    $app->post('/auth/recovery', [Login::class, 'alterarSenha']);

    
})->add(new JwtAuthentication([
    "secret" => $_ENV['JWT_SECRET_KEY'], // Chave secreta
    "attribute" => "jwt",               // Atributo para armazenar os dados do token decodificado
    "secure" => false,                  // Permite conexões HTTP para testes (altere para 'true' em produção)
    "error" => function (Response $response, $arguments) {
        $data = ["error" => "Token inválido ou ausente"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader("Content-Type", "application/json")->withStatus(401);
    }
]));

// Rota pública (não exige autenticação)
$app->get('/public', function (Request $request, Response $response) {
    $response->getBody()->write("This is a public route");
    return $response;
});


$app->post('/auth/novo', [login::class, 'novo']);

$app->post('/auth/login', [Login::class, 'verificarLogin']);


$app->run();
<?php

// Autoloader manual para a pasta 'controllers'
spl_autoload_register(function ($class) {
    $prefix = 'controllers\\';
    $base_dir = __DIR__ . '/controllers/';

    // Verifica se a classe usa o namespace esperado
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Obtém o nome da classe relativo ao namespace
    $relative_class = substr($class, $len);

    // Substitui os separadores de namespace por separadores de diretório
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Inclui o arquivo, se ele existir
    if (file_exists($file)) {
        require $file;
    } else {
        // Log ou erro para facilitar o debug
        error_log("Arquivo da classe não encontrado: $file");
    }
});

require 'vendor/autoload.php'; // Apenas se necessário para bibliotecas externas

$app = new \Slim\Slim();

// Rota para listar todos os planos
$app->get('/plano/', function () use ($app) {
    $controller = new \controllers\Plano($app);
    $controller->lista();
});

// Rota para listar planos por datas
$app->get('/plano/listar/', function () use ($app) {
    $controller = new \controllers\Plano($app);
    $controller->listar();
});

// Rota para buscar um plano por ID
$app->get('/plano/:id', function ($id) use ($app) {
    $controller = new \controllers\Plano($app);
    $controller->get($id);
});

// Rota para criar um novo plano
$app->post('/plano/', function () use ($app) {
    $controller = new \controllers\Plano($app);
    $controller->novo();
});

// Rota para editar um plano
$app->put('/plano/:id', function ($id) use ($app) {
    $controller = new \controllers\Plano($app);
    $controller->editar($id);
});

// Rota para excluir um plano
$app->delete('/plano/:id', function ($id) use ($app) {
    $controller = new \controllers\Plano($app);
    $controller->excluir($id);
});

// Rota para listar dependentes por CPF do titular
$app->get('/dependentes/', function () use ($app) {
    $controller = new \controllers\Dependentes($app);
    $controller->lista();
});

// Rota para obter um dependente por ID
$app->get('/dependentes/:id', function ($id) use ($app) {
    $controller = new \controllers\Dependentes($app);
    $controller->get($id);
});

// Rota para adicionar um novo dependente
$app->post('/dependentes/', function () use ($app) {
    $controller = new \controllers\Dependentes($app);
    $controller->novo();
});

// Rota para editar um dependente
$app->put('/dependentes/:id', function ($id) use ($app) {
    $controller = new \controllers\Dependentes($app);
    $controller->editar($id);
});

// Rota para excluir um dependente
$app->delete('/dependentes/:id', function ($id) use ($app) {
    $controller = new \controllers\Dependentes($app);
    $controller->excluir($id);
});

$app->run();

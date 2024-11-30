<?php
//Autoload
$loader = require 'vendor/autoload.php';

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

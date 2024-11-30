<?php
//Autoload
$loader = require 'vendor/autoload.php';

//Instanciando objeto
$app = new \Slim\Slim(array(
    'templates.path' => 'templates'
));

$app->get('/dependentes/', function() use ($app) {
    (new \controllers\Dependentes($app))->lista();
});

$app->get('/dependentes/:id', function($id) use ($app) {
    (new \controllers\Dependentes($app))->get($id);
});

$app->post('/dependentes/', function() use ($app) {
    (new \controllers\Dependentes($app))->novo();
});

$app->put('/dependentes/:id', function($id) use ($app) {
    (new \controllers\Dependentes($app))->editar($id);
});

$app->delete('/dependentes/:id', function($id) use ($app) {
    (new \controllers\Dependentes($app))->excluir($id);
});

$app->get('/plano/', function() use ($app) {
    (new \controllers\Plano($app))->lista();
});

$app->get('/plano/filtro/', function() use ($app) {
    (new \controllers\Plano($app))->listar();
});

$app->get('/plano/:id', function($id) use ($app) {
    (new \controllers\Plano($app))->get($id);
});

$app->post('/plano/', function() use ($app) {
    (new \controllers\Plano($app))->novo();
});

$app->put('/plano/:id', function($id) use ($app) {
    (new \controllers\Plano($app))->editar($id);
});

$app->delete('/plano/:id', function($id) use ($app) {
    (new \controllers\Plano($app))->excluir($id);
});

//Rodando aplicação
$app->run();

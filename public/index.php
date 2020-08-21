<?php

//Encontrar as classes sem ter que fazer require
//em cada arquivo da classe
require_once __DIR__ . '/../vendor/autoload.php';

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

$caminho = $_SERVER['PATH_INFO'];

//Nessa variável $rotas está o retorno do array implementado
//contido nesse endereço
$rotas = require_once  __DIR__ . '/../config/routes.php';

//A função array_key_exists() serve para verificar
//se uma chave/índice de um array associativo existe
if (!array_key_exists($caminho, $rotas)) {
    http_response_code(404);
    die();
}

//Criada a fábrica de msg http
$psr17Factory = new Psr17Factory();

//ServerRequestCreator() cria requisições utilizando a fábrica
//que está em seu interior
$creator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

//Usando as váriáveis super globais do php para criar um $request
$request = $creator->fromGlobals();

$classeControladora = $rotas[$caminho];

//Pegando o arquivo que contém o container
/** @var ContainerInterface $container */
$container = require __DIR__ . '/../config/dependencies.php';

/** @var RequestHandlerInterface $controlador */
$controlador = $container->get($classeControladora);
//Váriável $request sendo passada para o método processaRequisicao()
//devolve uma $resposta
$resposta = $controlador->handle($request);

//
foreach ($resposta->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);//sprintf (string print formatted) escreve um texto formatado para uma string
    }
}
//Pegando o corpo da resposta
echo $resposta->getBody();
<?php

//Encontrar as classes sem ter que fazer require
//em cada arquivo da classe
require_once __DIR__ . '/../vendor/autoload.php';

use Alura\Cursos\Controller\InterfaceControladorRequisicao;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

//Esse arquivo index.php é um front-controller/dispatcher
//que faz todo o roteamento entre endereços e controllers
//Se não informarmos nenhum recurso na url o arquivo padrão
//é index.php. Para termos um ponto único de entrada no sistema,
//criamos o arquivo index.php, o que nos dá várias possibilidades
//como por exemplo gerar relatórios,controlar requisições e até
//realizar alguma ação se necessário nessas requisições etc

//Através da variável global $_SERVER e a constante PATH_INFO
//temos o caminho do recurso que pode ser por exemplo uma dessas
//chaves do array $rotas em routes.php /listar-cursos /novo-curso
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

//Iniciando a sessão
session_start();

//A função stripos() retorna a posição da palavra
//login dentro de $caminho e retorna true se encontrar
//e false não encontrar. Lembrando que quando falamos
//de $caminho, estamos falando do endereço digitado
//na url, partindo desse raciocínio, vamos imaginar
//que o usuário digita tudo menos a palavra login,
//isso significa que $caminho não irá encontrar a
//palavra login, o que resultará em falso, então
//irá ser feito o redirecionamento para login
//$ehRotaDeLogin = stripos($caminho, 'login');
////Se o $_SESSION['logado'] não existir e $ehRotaDeLogin
////for igual a false, é feito o redirecionamento para o
////script login
//if (!isset($_SESSION['logado']) && $ehRotaDeLogin === false) {
//    header('Location: /login');
//    die();
//}

$psr17Factory = new Psr17Factory();

$creator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$request = $creator->fromGlobals();

//Pegando a $classeControladora/valor do array na variável $rotas
// que faz referência a chave/índice $caminho
//Pegando a classe Controller/valor em $rotas/arquivo e nesse $caminho/índice
$classeControladora = $rotas[$caminho];

//Aqui estamos dando um new em uma variável porque o PHP
//reconhece que na verdade ela está fazendo referência a
//uma classe muito bom isso. Lembrando que a referência
//precisa ser feita ao nome da classe. Lembrando que
//a referencia fala que é necessário implementar o
//método assinado na interface e todas as classes
//do controller irão possuir esse método
/** @var InterfaceControladorRequisicao $controlador */
$controlador = new $classeControladora();
$resposta = $controlador->processaRequisicao($request);

foreach ($resposta->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $resposta->getBody();
<?php declare(strict_types = 1);

$injector = new \Auryn\Injector;

/*
* Request Handler
*/
$injector->define('Symfony\Component\HttpFoundation\Request', [
    ':query' => $_GET,
    ':request' => $_POST,
    ':cookies' => $_COOKIE,
    ':files' => $_FILES,
    ':server' => $_SERVER 
]);
$injector->share('Symfony\Component\HttpFoundation\Request');
$injector->share('Symfony\Component\HttpFoundation\Response');

/*
* Template engine
*/
$injector->delegate('Twig_Environment', function() use ($injector) {
    $loader = new Twig_Loader_Filesystem(__DIR__ . '/Templates');
    $twig = new Twig_Environment($loader);
    return $twig;
});
$injector->alias('BookManager\Templates\Renderer', 'BookManager\Templates\TwigRenderer');
$injector->alias('Bookmanager\Templates\FrontendRenderer', 'BookManager\Templates\FrontendTwigRenderer');

/*
* BookReader view
*/
$injector->define('BookManager\Book\FileBookReader', [
    ':bookFolder' => __DIR__ . '/../books'
]);
$injector->alias('BookManager\Book\BookReader', 'BookManager\Book\FileBookReader');
$injector->share('BookManager\Book\FileBookReader');

$injector->alias('BookManager\Menu\MenuReader', 'BookManager\Menu\ArrayMenuReader');
$injector->share('BookManager\Menu\ArrayMenuReader');

return $injector;
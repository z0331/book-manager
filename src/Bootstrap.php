<?php declare(strict_types = 1);

namespace BookManager;

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL);

$environment = 'development';

/**
* Register error handler
**/
$whoops = new \Whoops\Run;
if ($environment != 'production') {
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
} else {
    $whoops->pushHandler(function($e) {
        echo 'Todo: Friendly error page and send email to the developer';
    });
}
$whoops->register();


/**
* Create request/response variables
**/
$injector = include('Dependencies.php');

$request = $injector->make('Symfony\Component\HttpFoundation\Request');
$response = $injector->make('Symfony\Component\HttpFoundation\Response');


/**
* Register routes
**/
$routeDefinitionCallback = function(\FastRoute\RouteCollector $r) {
    $routes = include('Routes.php');
    foreach($routes as $route) {
        $r->addRoute($route[0], $route[1], $route[2]);
    }
};

$dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback);

$routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

switch ($routeInfo[0]) {
    case \FastRoute\Dispatcher::NOT_FOUND:
        $response->setContent('404 - Page Not Found');
        $response->setStatusCode(404);
        break;
    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        $response->setContent('404 - Method Not Allowed');
        $response->setStatusCode(405);
        break;
    case \FastRoute\Dispatcher::FOUND:
        $className = $routeInfo[1][0];
        $method = $routeInfo[1][1];
        $vars = $routeInfo[2];

        $class = $injector->make($className);
        $class->$method($vars);
        break;
}

/**
* Load Doctrine DB classes
**/
$couchPath = __DIR__ . '/../';
require_once $couchPath . 'vendor/doctrine/common/lib/Doctrine/Common/ClassLoader.php';
include $couchPath . 'vendor/doctrine/annotations/lib/Doctrine/Common/Annotations/AnnotationRegistry.php';

$loader = new \Doctrine\Common\ClassLoader('Doctrine\Common', $couchPath . 'vendor/doctrine/common/lib');
$loader->register();

$loader = new \Doctrine\Common\ClassLoader('Doctrine\ODM\CouchDB', $couchPath);
$loader->register();

$loader = new \Doctrine\Common\ClassLoader('Doctrine\CouchDB', $couchPath);
$loader->register();

$loader = new \Doctrine\Common\ClassLoader('Symfony', $couchPath . 'vendor');
$loader->register();

$annotationNs = 'Doctrine\\ODM\\CouchDB\\Mapping\\Annotations';
$annotationRegistery = new \Doctrine\Common\Annotations\AnnotationRegistry;
$annotationRegistery::registerAutoloadNamespace($annotationNs, $couchPath);
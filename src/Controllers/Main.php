<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Response;

class Main {
    private $response;

    public function __construct(Response $response) {
        $this->response = $response;
    }

    public function show() {
        $this->response->setContent('Hello World');
        $this->response->send();
    }
}
<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class Main {
    private $request;
    private $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    public function show() {
        $content = 'Hello world!<br><br>';
        $content .= 'Hello ' . $this->request->query->get('name', 'stranger');
        $this->response->setContent($content);
        $this->response->send();
    }
}
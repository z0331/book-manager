<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    BookManager\Templates\FrontendRenderer;

class NewBook {
    private $request;
    private $response;
    private $frontendRenderer;

    public function __construct(Request $request, Response $response, FrontendRenderer $frontendRenderer) {
        $this->request = $request;
        $this->response = $response;
        $this->frontendRenderer = $frontendRenderer;
    }

    public function newBook() {
       $html = $this->frontendRenderer->render('NewBook');
       $this->response->setContent($html);
       $this->response->send();
    }

    public function editBook($bookId) {
        //Look up book and populate form with information
    }
}
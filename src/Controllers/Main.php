<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    BookManager\Templates\FrontendRenderer,
    BookManager\Models\Book;

class Main {
    private $request;
    private $response;
    private $frontendRenderer;

    public function __construct(Request $request, Response $response, FrontendRenderer $frontendRenderer) {
        $this->request = $request;
        $this->response = $response;
        $this->frontendRenderer = $frontendRenderer;
    }

    public function show() {
        $data = $this->request->request->all();
        echo "<div class='content'>";
        var_dump($data);
        echo "</div>";
        $html = $this->frontendRenderer->render('Main', $data);
        $this->response->setContent($html);
        $this->response->send();
    }

    public function addBook(Book $book) {
        $data = $this->request->query->all();
        $html = $this->frontendRenderer->render('Book', $data);
        $this->response->setContent($html);
        $this->response->send();
    }
}
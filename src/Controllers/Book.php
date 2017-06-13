<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    BookManager\Templates\FrontendRenderer,
    BookManager\Book\BookReader,
    BookManager\Book\InvalidBookException;

class Book {
    private $request;
    private $response;
    private $frontendRenderer;
    private $bookReader;

    public function __construct(Request $request, 
                                Response $response,
                                FrontendRenderer $frontendRenderer,
                                BookReader $bookReader) {
        $this->request = $request;
        $this->response = $response;
        $this->frontendRenderer = $frontendRenderer;
        $this->bookReader = $bookReader;
    }

    public function view($params) {
        $slug = $params['slug'];
        try {
            $data['content'] = $this->bookReader->readBySlug($slug);
        } catch (InvalidBookException $e) {
            $this->response->setStatusCode(404);
            $this->response->setContent('404 - Book Not Found');
            return $this->response->send();
        }
        $html = $this->frontendRenderer->render('Book', $data);
        $this->response->setContent($html);
        $this->response->send();
    }
}
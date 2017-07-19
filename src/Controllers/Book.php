<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    BookManager\Templates\FrontendRenderer,
    BookManager\Book\BookReader,
    BookManager\Models\BookRepository,
    BookManager\Models\BooksDesignDocument,
    BookManager\Book\InvalidBookException;

class Book {
    private $request;
    private $response;
    private $frontendRenderer;
    private $bookRepository;

    public function __construct(Request $request, 
                                Response $response,
                                FrontendRenderer $frontendRenderer,
                                BookRepository $bookRepository) {
        $this->request = $request;
        $this->response = $response;
        $this->frontendRenderer = $frontendRenderer;
        $this->bookRepository = $bookRepository;
    }

    public function view($isbn) {
        var_dump($isbn);
        try {
            $book = $this->bookRepository->allDataByBook($isbn);
        } catch (InvalidBookException $e) {
            $this->response->setStatusCode(404);
            $this->response->setContent('404 - Book Not Found');
            return $this->response->send();
        }
        //var_dump($book);
        $html = $this->frontendRenderer->render('Book', $book);
        $this->response->setContent($html);
        $this->response->send();
    }
}
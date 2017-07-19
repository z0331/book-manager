<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    BookManager\Templates\FrontendRenderer,
    BookManager\Models\Book,
    BookManager\Models\BookRepository,
    BookManager\Models\BooksDesignDocument;

class Main {
    private $request;
    private $response;
    private $frontendRenderer;
    private $bookRepository;

    public function __construct(Request $request,
                                Response $response,
                                FrontendRenderer $frontendRenderer,
                                BookRepository $bookRepository) 
    {
        $this->request = $request;
        $this->response = $response;
        $this->frontendRenderer = $frontendRenderer;
        $this->bookRepository = $bookRepository;
    }

    public function show() {
        $fullBookList = array();
        $allSeasons = $this->bookRepository->getAllSeasons();
        foreach($allSeasons as $season) {
            //Turn season into a string to use as array key
            $thisSeason = implode($season['key'], ' ');
            $fullBookList[$thisSeason] = $this->bookRepository->getBooksWithDeadlinesBySeason($season['key']);
        }
        $html = $this->frontendRenderer->render('Main', $fullBookList);
        $this->response->setContent($html);
        $this->response->send();
    }

    public function addBook() {
        $data = $this->request->request->all();

        //Create book from request data and save; then render normally

        $html = $this->frontendRenderer->render('Book', $data);
        $this->response->setContent($html);
        $this->response->send();
    }

    public function updateBook(Book $book) {
        //Update book with new information; then render normally
    }
}
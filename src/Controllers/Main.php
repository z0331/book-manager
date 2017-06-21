<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    BookManager\Templates\FrontendRenderer,
    BookManager\Models\DatabaseManager,
    BookManager\Models\Book,
    BookManager\Models\BooksDesignDocument;

class Main {
    private $request;
    private $response;
    private $frontendRenderer;
    private $databaseManager;

    public function __construct(Request $request,
                                Response $response,
                                FrontendRenderer $frontendRenderer,
                                DatabaseManager $databaseManager) 
    {
        $this->request = $request;
        $this->response = $response;
        $this->frontendRenderer = $frontendRenderer;
        $this->databaseManager = $databaseManager;
    }

    public function show() {
        //Get data for all books and render
        $dataSet = array(
            'books' => array(),
            'seasons' => array()
        );
        $this->databaseManager->getDbClient()->createDesignDocument('books', new BooksDesignDocument);
        $queryBooks = $this->databaseManager->getDbClient()->createViewQuery('books', 'allBooks');
        $querySeasonYears = $this->databaseManager->getDbClient()->createViewQuery('books', 'seasonYear');

        $result = $queryBooks->execute();
                foreach($result as $row) {
            $dataSet['books'][] = $row['value'];
        }

        $result = $querySeasonYears->execute();
        foreach($result as $row) {
            $dataSet['seasons'][] = $row;
        }

        $html = $this->frontendRenderer->render('Main', $dataSet);
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
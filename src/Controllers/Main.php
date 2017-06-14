<?php declare(strict_types = 1);

namespace BookManager\Controllers;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    BookManager\Templates\FrontendRenderer,
    BookManager\Models\DatabaseManager,
    BookManager\Models\Book;

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
        $book = $this->databaseManager->getDbClient()->findDocument('9781628727807');
        $dm = $this->databaseManager->getDocumentManager();
        $bookTest = new Book();
        echo "<div class='content'>";
        var_dump($dm);
        var_dump($book);
        echo "</div>";
        $data = ['name' => 'Maxim'];
        $html = $this->frontendRenderer->render('Main', $data);
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
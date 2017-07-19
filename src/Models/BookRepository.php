<?php declare(strict_types = 1);

namespace BookManager\Models;

use BookManager\Models\DatabaseManager;

class BookRepository implements BookRepositoryInterface {
    private $databaseManager;

    public function __construct(DatabaseManager $databaseManager) {
        $this->databaseManager = $databaseManager;
    }

    public function getBooksWithDeadlinesBySeason($season) {
        $seasonBooks = array();
        $this->databaseManager->getDbClient()->createDesignDocument('books', new BooksDesignDocument);
        try {
            $query = $this->databaseManager->getDbClient()->createViewQuery('books', 'booksWithDeadlinesBySeason');
            $query->setKey($season);
            $result = $query->execute();
        } catch (Exception $e) {
            echo $e;
        }
        foreach ($result as $row) {
            $seasonBooks[] = $row;
        }
        return $seasonBooks;
    }

    public function getAllSeasons() {
        $allSeasons = array();
        $this->databaseManager->getDbClient()->createDesignDocument('books', new BooksDesignDocument);
        try {
            $query = $this->databaseManager->getDbClient()->createViewQuery('books', 'allSeasons');
            $query->setReduce(true);
            $query->setGroup(true);
            $result = $query->execute();
        } catch (Exception $e) {
            echo $e;
        }
        
        foreach ($result as $row) {
            $allSeasons[] = $row;
        }
        return $allSeasons;
    }

    public function allDataByBook($isbn) {
        /*
        $book = array();
        $this->databaseManager->getDbClient()->createDesignDocument('books', new BooksDesignDocument);
        try {
            $query = $this->databaseManager->getDbClient()->createViewQuery('books', 'allDataByBook');
            $query->setKey($isbn);
            $result = $query->execute();
        } catch (Exception $e) {
            echo $e;
        }
        var_dump($result);
        foreach ($result as $row) {
            $book[] = $row;
        }
        //var_dump($book);
        */
        $book = $this->databaseManager->getDbClient()->findDocument($isbn);
        var_dump($book);
        return $book;
    }
}
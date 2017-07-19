<?php declare(strict_types = 1);

namespace BookManager\Book;

use InvalidArgumentException;

class ArrayBookReader implements BookReader {

    public function readByIsbn(array $book) : array {
        $bookArray = array();
        
        if (!is_array($book)) {
            throw new InvalidBookException($book);
        }

        return $bookArray;
    }
}
<?php declare(strict_types = 1);

namespace BookManager\Book;

use Exception;

class InvalidBookListException extends Exception {
    public function __construct($slug, $code = 0, Exception $previous = null) {
        $message = "Provided book list was not array.";
        parent::__construct($message, $code, $previous);
    }
}
<?php declare(strict_types = 1);

namespace BookManager\Book;

use Exception;

class InvalidBookException extends Exception {
    public function __construct($slug, $code = 0, Exception $previous = null) {
        $message = "No book with the slug '$slug' was found.";
        parent::__construct($message, $code, $previous);
    }
}
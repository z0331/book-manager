<?php declare(strict_types = 1);

namespace BookManager\Book;

interface BookReader {
    public function readByIsbn(array $book) : array;
}


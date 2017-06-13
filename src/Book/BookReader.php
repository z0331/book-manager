<?php declare(strict_types = 1);

namespace BookManager\Book;

interface BookReader {
    public function readBySlug(string $slug) : string;
}


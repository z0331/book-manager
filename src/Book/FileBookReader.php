<?php declare(strict_types = 1);

namespace BookManager\Book;

use InvalidArgumentException;

class FileBookReader implements BookReader {
    private $bookFolder;

    public function __construct(string $bookFolder) {
        $this->bookFolder = $bookFolder;
    }

    public function readBySlug(string $slug) : string {
        $path = "$this->bookFolder/$slug.md";

        if (!file_exists($path)) {
            throw new InvalidBookException($slug);
        }

        return file_get_contents($path);
    }
}
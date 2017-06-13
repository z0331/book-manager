<?php declare(strict_types = 1);

namespace BookManager\Menu;

class ArrayMenuReader implements MenuReader {

    public function readMenu() : array {
        return [
            ['href' => '/projects/book_manager/public/', 'text' => 'Main'],
            ['href' => 'new', 'text' => 'New Book'],
        ];
    }
}
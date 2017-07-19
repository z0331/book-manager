<?php declare(strict_types = 1);

namespace BookManager\Templates;

use BookManager\Menu\MenuReader,
    BookManager\BookList\BookListReader,
    BookManager\Book\BookReader;

class FrontendTwigRenderer implements FrontendRenderer {
    private $renderer;
    private $menuReader;
    private $bookListReader;
    private $bookReader;

    public function __construct(Renderer $renderer,
                                MenuReader $menuReader,
                                BookListReader $bookListReader,
                                BookReader $bookReader) {
        $this->renderer = $renderer;
        $this->menuReader = $menuReader;
        $this->bookListReader = $bookListReader;
        $this->bookReader = $bookReader;
    }

    public function render($template, $data = []) : string {
        $data = array_merge($data, [
            'menuItems' => $this->menuReader->readMenu(),
            'bookList' => $this->bookListReader->readBookList($data),
            'book' => $this->bookReader->readByIsbn($data)
        ]);
        return $this->renderer->render($template, $data);
    }
}
<?php declare(strict_types = 1);

namespace BookManager\Templates;

use BookManager\Menu\MenuReader,
    BookManager\BookList\BookListReader;

class FrontendTwigRenderer implements FrontendRenderer {
    private $renderer;
    private $menuReader;
    private $bookListReader;

    public function __construct(Renderer $renderer, MenuReader $menuReader, BookListReader $bookListReader) {
        $this->renderer = $renderer;
        $this->menuReader = $menuReader;
        $this->bookListReader = $bookListReader;
    }

    public function render($template, $data = []) : string {
        $data = array_merge($data, [
            'menuItems' => $this->menuReader->readMenu(),
            'bookList' => $this->bookListReader->readBookList($data)
        ]);
        return $this->renderer->render($template, $data);
    }
}
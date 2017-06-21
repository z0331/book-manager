<?php declare(strict_types = 1);

namespace BookManager\BookList;

interface BookListReader {

    public function readBookList($list) : array;
}
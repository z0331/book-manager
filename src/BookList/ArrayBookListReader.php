<?php declare(strict_types = 1);

namespace BookManager\BookList;

class ArrayBookListReader implements BookListReader {

    public function readBookList($list) : array {
        if(!is_array($list)) {
            throw new InvalidBookListException;
        }
        else {
            $bookList = array();
            foreach ($list as $book) {
                $bookList[] = $book;
            }
        return $bookList;
        }
    }
}
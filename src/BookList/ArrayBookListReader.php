<?php declare(strict_types = 1);

namespace BookManager\BookList;

class ArrayBookListReader implements BookListReader {

    /*
     * Generates standardized array from CouchDB view results.
     */
    public function readBookList($list) : array {
        if(!is_array($list)) {
            throw new InvalidBookListException;
        }
        else {
            $bookList = array();
            foreach($list as $season => $books) {
                foreach($books as $book) {
                    $bookList[$season][] = array(
                        'isbn' => $book['value'][0],
                        'title' => $book['value'][1],
                        'deadlines' => $book['value'][2]
                    );
                }
            }
        return $bookList;
        }
    }
}
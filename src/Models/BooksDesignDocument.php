<?php declare(strict_types = 1);

namespace BookManager\Models;

use \Doctrine\CouchDB\View;

class BooksDesignDocument implements \Doctrine\CouchDB\View\DesignDocument {
    
    public function getData() {
        return array(
            'language' => 'javascript',
            'views' => array(
                'allBooks' => array(
                    'map' => 'function(doc) {
                        if(\'book\' == doc.type) {
                            emit(doc._id, doc);
                        }
                    }'
                ),
                'seasonYear' => array(
                    'map' => 'function(doc) {
                        emit(doc.season_year, null);
                    }'
                )
            ),
        );
    }
}
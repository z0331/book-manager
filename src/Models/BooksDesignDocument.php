<?php declare(strict_types = 1);

namespace BookManager\Models;

use \Doctrine\CouchDB\View;

class BooksDesignDocument implements \Doctrine\CouchDB\View\DesignDocument {
    
    public function getData() {
        return array(
            'language' => 'javascript',
            'views' => array(
                'booksWithDeadlinesBySeason' => array(
                    'map' => 'function(doc) {
                        if(doc.type == \'book\' && doc.season && doc.season_year) {
                            emit([doc.season, doc.season_year], [doc._id, doc.title, doc.deadlines]);
                        }
                    }'
                ),
                'allDataByBook' => array(
                    'map' => 'function(doc) {
                        if(doc.type == \'book\') {
                            emit(doc._id, doc);
                        }
                    }'
                ),
                'allSeasons' => array(
                    'map' => 'function(doc) {
                        if(doc.type == \'book\' && doc.season && doc.season_year) {
                            emit([doc.season, doc.season_year], null);
                        }
                    }',
                    'reduce' => 'function(keys, values) {
                        return true;
                    }'
                )
            ),
        );
    }
}
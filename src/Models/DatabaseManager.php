<?php declare(strict_types = 1);

namespace BookManager\Models;

use Doctrine\CouchDB\CouchDBClient,
    Doctrine\ODM\CouchDB\DocumentManager,
    Doctrine\CouchDB\View\FolderDesignDocument;

/**
 * DatabaseManager class to encapsulate both Doctrine CouchDB API wrapper and Object Document Mappers
 */ 
class DatabaseManager {
    private $dbClient;
    private $documentManager;

    public function __construct(CouchDBClient $dbClient, DocumentManager $documentManager) {
        $this->dbClient = $dbClient;
        $this->documentManager = $documentManager;
    }

    public function getDbClient() {
        return $this->dbClient;
    }

    public function getDocumentManager() {
        return $this->documentManager;
    }
}
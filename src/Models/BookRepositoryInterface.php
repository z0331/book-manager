<?php declare(strict_types = 1);

namespace BookManager\Models;

interface BookRepositoryInterface {
    public function getBooksWithDeadlinesBySeason($season);

    public function getAllSeasons();

    public function allDataByBook($isbn);
}
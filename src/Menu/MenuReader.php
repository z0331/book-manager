<?php declare(strict_types = 1);

namespace BookManager\Menu;

interface MenuReader {

    public function readMenu() : array;
    
}
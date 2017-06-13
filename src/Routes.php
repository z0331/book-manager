<?php declare(strict_types = 1);

return [
    ['GET', '/', ['BookManager\Controllers\Main', 'show']],
    ['POST', '/', ['BookManager\Controllers\Main', 'show']],
    ['GET', '/new', ['BookManager\Controllers\NewBook', 'newBook']],
    ['GET', '/{slug}', ['BookManager\Controllers\Book', 'view']],
];
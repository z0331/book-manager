<?php declare(strict_types = 1);

namespace BookManager\Templates;

interface Renderer {
    public function render($template, $data = []) : string;
}
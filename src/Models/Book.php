<?php declare(strict_types = 1);

namespace BookManager\Models;

/** @Document */
class Book {

    /** @Id */
    private $id;

    /** @Field(type="string") */
    private $title;

    /** @Field(type="string") */
    private $subtitle;

    /** @Field(type="string") */
    private $isbn;

    /** @Field(type="string") */
    private $eisbn;

    /** @Field(type="mixed") */
    private $contributor;

    /** @Field(type="string") */
    private $imprint;

    /** @Field(type="string") */
    private $season;

    /** @Field(type="string") */
    private $season_year;

    /** @Field(type="boolean") */
    private $arc;

    /** @Field(type="string") */
    private $pub_date;

    /** @Field(type="mixed") */
    private $custom_fields;

    /** @Column(type="mixed") */
    public $deadlines;
}
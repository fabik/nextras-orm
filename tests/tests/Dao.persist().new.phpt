<?php

use Tester\Assert;
require __DIR__ . '/bootstrap.php';



$book = new Book;

$book->title = 'Super new ORM';

$book->author = new Author;
$book->author->name = 'Jan Skrasek';
$book->author->born = new DateTime;

$book->translator = $book->author;



$bookDao = new BookDao($connection, $selectionFactory);
$bookDao->persist($book);



$book = $bookDao->findById($book->id);
Assert::equal('Super new ORM', $book->title);
Assert::equal('JAN SKRASEK', $book->author->name);

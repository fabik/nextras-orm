<?php

use Tester\Assert;
require __DIR__ . '/bootstrap.php';



$bookDao = new BookDao($connection, $selectionFactory);
$book = $bookDao->findById(1);

Assert::equal('JAKUB VRANA', $book->author->name);



$book->author->name = 'Eddard Stark';
Assert::equal('EDDARD STARK', $book->author->name);



$bookDao->persist($book);
$book = $bookDao->findById(1);
Assert::equal('EDDARD STARK', $book->author->name);

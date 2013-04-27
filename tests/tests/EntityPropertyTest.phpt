<?php

use Tester\Assert;
require __DIR__ . '/bootstrap.php';



$author = new Author;



// read-only
Assert::throws(function() use ($author) {
	$author->fullName = 'string';
}, 'Nextras\Orm\MemberAccessException');



$bookDao = new BookDao($connection, $selectionFactory);
$book = $bookDao->findById(1);

Assert::equal('1001 tipu a triku pro PHP', $book->title);
Assert::equal('JAKUB VRANA', $book->author->name);

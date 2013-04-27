<?php

use Tester\Assert;
require __DIR__ . '/bootstrap.php';



$author = new Author;



// allow NULLs
$author->web = NULL;
$author->web = 'http://example.com';



// disallow int
Assert::throws(function() use ($author) {
	$author->web = 123;
}, 'Nextras\Orm\InvalidArgumentException');



// disallow NULL
Assert::throws(function() use ($author) {
	$author->name = NULL;
}, 'Nextras\Orm\InvalidArgumentException');



// allow DateTime & Nette\DateTime
$author->born = new DateTime('2000-01-01');
$author->born = new Nette\DateTime('2000-01-01');



$book = new Book;



// allow Entity
$book->author = new Author;



// disallow NULL
Assert::throws(function() use ($book) {
	$book->author = NULL;
}, 'Nextras\Orm\InvalidArgumentException');



// allow null for Entity
$book->translator = NULL;

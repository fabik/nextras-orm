<?php

use Tester\Assert;
require __DIR__ . '/bootstrap.php';



$bookDao = new BookDao($connection, $selectionFactory);
$book = $bookDao->findById(1);

$tags = array();
foreach ($book->tags as $tag) {
	$tags[] = $tag->name;
}


Assert::equal(array('PHP', 'MySQL'), $tags);



$authorDao = new AuthorDao($connection, $selectionFactory);
$author = $authorDao->findById(11);

$b = array();
foreach ($author->books as $book) {
	$b[] = $book->title;
}
Assert::equal(array(
	'1001 tipu a triku pro PHP',
	'JUSH',
), $b);



$b = array();
foreach ($author->translatedBooks as $book) {
	$b[] = $book->title;
}
Assert::equal(array(
	'1001 tipu a triku pro PHP',
), $b);

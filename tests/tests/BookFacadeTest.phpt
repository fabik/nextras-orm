<?php

use Tester\Assert;
require __DIR__ . '/bootstrap.php';



$bookDao = new BookDao($connection, $selectionFactory);
$bookFacade = new BookFacade($bookDao);


$books = $bookFacade->getLatest();
Assert::equal(3, $books->count());



$b = array();
foreach ($books as $book) {
	$b[$book->title] = $book->author->name;
}

Assert::equal(array(
	'Dibi' => 'DAVID GRUDL',
	'JUSH' => 'JAKUB VRANA',
	'Nette' => 'DAVID GRUDL',
), $b);

<?php

use Nextras\Orm\EntityCollection;

/**
 * @property string $name
 * @property string|NULL $web
 * @property-read Nextras\Orm\EntityCollection $books
 * @property-read Nextras\Orm\EntityCollection $translatedBooks
 */
class Author extends Person
{

	protected function getBooks()
	{
		return new Nextras\Orm\EntityCollection($this->related('book'), 'Book');
	}

	protected function getTranslatedBooks()
	{
		return new Nextras\Orm\EntityCollection($this->related('book', 'translator_id'), 'Book');
	}

}

<?php


/**
 * @property string $name
 * @property string|NULL $web
 */
class Author extends Person
{

	protected function getBooks()
	{
		return new Nextras\Orm\EntityCollection($this->row->related('book'), 'Book');
	}

}

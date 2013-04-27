<?php

use Nextras\Orm\EntityCollection;

/**
 * @method Book findById()
 */
class BookDao extends Nextras\Orm\Dao
{

	public function delete(Book $book)
	{
		$this->begin();
		$book->toActiveRow()->delete();
		$this->commit();
	}



	/**
	 * @param string|array
	 * @return EntityCollection
	 */
	public function findByTags($name)
	{
		return $this->createCollection($this->getTable()->where('book_tag:tag.name', (array) $name));
	}

}

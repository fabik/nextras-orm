<?php


use Nextras\Orm\EntityCollection;

final class BookFacade
{

	/** @var BookDao */
	protected $dao;



	public function __construct(BookDao $dao)
	{
		$this->dao = $dao;
	}



	/** @return Nextras\Orm\EntityCollection */
	function getLatest()
	{
		return $this->dao
			->findAll()
			->orderBy('id', EntityCollection::DESC)
			->limit(3);
	}

}

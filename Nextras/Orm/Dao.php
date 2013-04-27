<?php

/**
 * This file is part of the Nextras\ORM library
 * This file is based on YetORM library (https://github.com/uestla/YetORM)
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 * @author     Petr Kessler (http://kesspess.1991.cz)
 */

namespace Nextras\Orm;

use Nette;
use Nette\Database\Connection;
use Nette\Database\Table\Selection;
use Nette\Utils\Strings;



abstract class Dao extends Nette\Object
{

	/** @var array */
	private static $transactionCounter = array();

	/** @var Connection */
	protected $connection;

	/** @var SelectionFactory */
	protected $selectionFactory;

	/** @var string */
	protected $entity = NULL;



	public function __construct(Connection $connection, SelectionFactory $selectionFactory)
	{
		$this->connection = $connection;
		$this->selectionFactory = $selectionFactory;
		if (!$this->entity) {
			if (preg_match('#([a-z0-9]+)dao#i', get_class($this), $matches)) {
				$this->entity = $matches[1];
			} else {
				throw new InvalidStateException('Undefined entity name');
			}
		}

		if (!isset(self::$transactionCounter[$connection->dsn])) {
			self::$transactionCounter[$connection->dsn] = 0;
		}
	}


	/**
	 * @return EntityCollection
	 */
	public function findAll()
	{
		return $this->createCollection($this->getTable($this->entity));
	}



	/**
	 * @return Entity
	 */
	public function findById($id)
	{
		$class = $this->entity;
		return new $class($this->getTable($class)->get($id));
	}



	public function persist(Entity $entity)
	{
		$this->begin();
		$entity->isPersisting(TRUE);
		foreach ($entity->getEntities() as $subEntity) {
			$this->persist($subEntity);
		}

		if (!$entity->isPersisted()) {
			$activeRow = $entity->toActiveRow();
			if ($activeRow) {
				$activeRow->update($entity->getModifiedData());
			} else {
				$activeRow = $this->getTable($entity)->insert($entity->getModifiedData());
			}
			$entity->setPersisted($activeRow);
		}
		$entity->isPersisting(FALSE);
		$this->commit();
	}



	/**
	 * @param Selection
	 * @param string|NULL
	 * @param string|NULL
	 * @param string|NULL
	 * @return EntityCollection
	 */
	protected function createCollection(Selection $selection, $entity = NULL, $refTable = NULL, $refColumn = NULL)
	{
		return new EntityCollection($selection, $entity ?: $this->entity, $refTable, $refColumn);
	}



	/**
	 * @param  string
	 * @param  bool
	 * @return Selection
	 */
	protected function getTable($entityName, $isTableName = FALSE)
	{
		if ($isTableName) {
			return $this->selectionFactory->table($entityName);
		} else {
			return $this->selectionFactory->tableByEntity(is_object($entityName) ? get_class($entityName) : $entityName);
		}
	}



	// === transactions ================================================================================================



	final protected function begin()
	{
		if (self::$transactionCounter[$this->connection->dsn]++ === 0) {
			$this->connection->beginTransaction();
		}
	}



	final protected function commit()
	{
		if (self::$transactionCounter[$this->connection->dsn] === 0) {
			throw new InvalidStateException("No transaction started.");
		}

		if (--self::$transactionCounter[$this->connection->dsn] === 0) {
			$this->connection->commit();
		}
	}



	final protected function rollback()
	{
		$this->connection->rollBack();
		self::$transactionCounter[$this->connection->dsn] = 0;
	}

}

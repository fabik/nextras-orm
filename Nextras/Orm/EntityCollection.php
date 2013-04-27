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
use Nette\Database\Table\Selection;



class EntityCollection extends Nette\FreezableObject implements \Iterator, \Countable
{

	/** @const bool */
	const ASC = TRUE;

	/** @const bool */
	const DESC = FALSE;

	/** @var Selection */
	protected $selection;

	/** @var string */
	protected $entity;

	/** @var string|NULL */
	protected $refTable;

	/** @var string|NULL */
	protected $refColumn;

	/** @var Entity[] */
	protected $data = NULL;

	/** @var array */
	private $keys;



	/**
	 * @param  Selection
	 * @param  string|EntityLoader
	 * @param  string|NULL
	 * @param  string|NULL
	 */
	public function __construct(Selection $selection, $entity, $refTable = NULL, $refColumn = NULL)
	{
		$this->selection = $selection;
		$this->entity = $entity;
		$this->refTable = $refTable;
		$this->refColumn = $refColumn;
	}



	/**
	 * <code>
	 * $collection->orderBy('column', $collection::DESC); // ORDER BY [column] DESC
	 * // or
	 * $collection->orderBy(array(
	 *	'first'  => $collection::ASC,
	 *	'second' => $collection::DESC,
	 * ); // ORDER BY [first], [second] DESC
	 * </code>
	 *
	 * @param  string|array
	 * @param  bool
	 * @return static
	 */
	public function orderBy($column, $direction = self::ASC)
	{
		$this->updating();
		if (is_array($column)) {
			foreach ($column as $col => $d) {
				$this->orderBy($col, $d);
			}

		} else {
			$this->selection->order($column . ($direction === self::DESC ? ' DESC' : ''));
		}

		return $this;
	}



	/**
	 * @param  int
	 * @param  int|NULL
	 * @return static
	 */
	public function limit($limit, $offset = NULL)
	{
		$this->updating();
		$this->selection->limit($limit, $offset);
		return $this;
	}



	private function loadData()
	{
		if ($this->data !== NULL) {
			return;
		}

		$this->freeze();
		$this->data = array();
		foreach ($this->selection as $row) {
			if ($this->refTable !== NULL) {
				$row = $this->refColumn !== NULL
					? $row->ref($this->refTable, $this->refColumn)
					: $row->{$this->refTable};
			}

			$class = $this->entity;
			$this->data[] = new $class($row);
		}
	}



	// === interface \Iterator =========================================================================================



	public function rewind()
	{
		$this->loadData();
		$this->keys = array_keys($this->data);
		reset($this->keys);
	}



	/**
	 * @return Entity
	 */
	public function current()
	{
		$key = current($this->keys);
		return $key === FALSE ? FALSE : $this->data[$key];
	}



	public function key()
	{
		return current($this->keys);
	}



	public function next()
	{
		next($this->keys);
	}



	public function valid()
	{
		return current($this->keys) !== FALSE;
	}



	// === interface \Countable ========================================================================================



	/**
	 * @return int
	 */
	public function count()
	{
		$this->loadData();
		return count($this->data);
	}

}

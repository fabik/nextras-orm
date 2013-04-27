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
use Nette\Database\Table\ActiveRow;



abstract class Entity extends Nette\Object
{

	/** @var array */
	private static $reflections = array();

	/** @var array */
	public $onPersist = array();

	/** @var ActiveRow|NULL */
	private $row;

	/** @var bool */
	private $isPersisted = FALSE;

	/** @var bool */
	private $isPersisting = FALSE;

	/** @var bool */
	private $isSetterMode = FALSE;

	/** @var array */
	private $modified = array();

	/** @var array */
	private $cached = array();

	/** @var array */
	private $entities = array();

	/** @var array */
	private $cachedDp = array();

	/** @var string|NULL */
	private $cachedDpKey;



	public function __construct(ActiveRow $row = NULL)
	{
		if ($row !== NULL) {
			$this->setPersisted($row);
		}
	}



	public function isPersisting($isPersisting)
	{
		$this->isPersisting = $isPersisting;
	}



	/**
	 * @internal
	 */
	final public function setPersisted(ActiveRow $row)
	{
		$this->row = $row;
		$this->isPersisted = TRUE;
		$this->onPersist($this);
		$this->modified = array();
		$this->cached = array();
	}



	/**
	 * @internal
	 */
	final public function toActiveRow()
	{
		return $this->row;
	}



	public function getModifiedData()
	{
		return $this->modified;
	}



	public function getEntities()
	{
		return $this->entities;
	}



	protected function related($key)
	{
		$this->needPersist();
		return $this->row->related($key);
	}



	protected function ref($key, $throughColumn = NULL)
	{
		$this->needPersist();
		return $this->row->ref($key, $throughColumn);
	}



	public function isPersisted()
	{
		return $this->isPersisted;
	}



	private function needPersist()
	{
		if (!$this->isPersisted) {
			throw new InvalidStateException('Entity is not persisted');
		}
	}



	public function __call($name, $args)
	{
		$prefix = substr($name, 0, 3);
		if ($prefix === 'get') {
			return $this->__get(lcfirst(substr($name, 3)));
		} elseif ($prefix === 'set') {
			$this->__set(lcfirst(substr($name, 3)), reset($args));
			return $this;
		} else {
			return parent::__call($name, $args);
		}
	}



	public function & __get($name)
	{
		if ($this->cachedDpKey !== NULL) {
			$this->cachedDp[$name][] = $this->cachedDpKey;
		}

		if (isset($this->cached[$name])) {
			return $this->cached[$name];
		}

		$reflection = static::getReflection();
		if (!($prop = $reflection->getProperty($name))) {
			throw new MemberAccessException('Undefined property ' . $name . '.');
		}

		if ($reflection->hasMethod('get' . ucfirst($name))) {
			$cacheDpKey = $this->cachedDpKey;
			$this->cachedDpKey = $name;
			if (isset($this->modified[$name])) {
				$args = array($this->modified[$name]);
			} elseif ($this->isPersisted && isset($this->row[$name])) {
				$args = array($this->row[$name]);
			} else {
				$args = array();
			}
			$value = call_user_func_array(array($this, 'get' . $name), $args);
			$this->cachedDpKey = $cacheDpKey;
		} else {
			$this->needPersist();
			$value = $this->row[$name];
		}

		$value = $this->cached[$name] = $prop->setType($value);
		if ($value instanceof Entity) {
			$this->entities[$name] = $value;
		}
		return $value;
	}



	public function __set($name, $value)
	{
		unset($this->cached[$name]);

		if (!($prop = static::getReflection()->getProperty($name))) {
			throw new MemberAccessException('Undefined property ' . $name . '.');
		}

		if ($prop->readonly && !($this->isPersisting || $this->isSetterMode)) {
			throw new MemberAccessException('Property ' . $name . ' is read-only.');
		}

		if (!empty($this->cachedDp[$name])) {
			foreach ($this->cachedDp[$name] as $column) {
				unset($this->cached[$column]);
			}
		}

		$value = $prop->fixType($value);
		if (static::getReflection()->hasMethod('set' . $name)) {
			$isSetterMode = $this->isSetterMode;
			$this->isSetterMode = TRUE;
			$returned = call_user_func_array(array($this, 'set' . $name), array($value));
			if ($returned) {
				$value = $returned;
			}
			$this->isSetterMode = $isSetterMode;
		}

		if ($value instanceof Entity) {
			$this->entities[$name] = $this->cached[$name] = $value;
		} else {
			$this->modified[$name] = $value;
		}
		$this->isPersisted = FALSE;
	}



	public function __isset($name)
	{
		return parent::__isset($name) || static::getReflection()->hasProperty($name);
	}



	public function __unset($name)
	{
		throw new NotSupportedException;
	}



	/**
	 * @return Reflection\EntityType
	 */
	static public function getReflection()
	{
		$class = get_called_class();
		if (!isset(self::$reflections[$class])) {
			self::$reflections[$class] = new Reflection\EntityType($class);
		}

		return self::$reflections[$class];
	}

}

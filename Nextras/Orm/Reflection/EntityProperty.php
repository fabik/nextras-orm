<?php

/**
 * This file is part of the Nextras\ORM library
 * This file is based on YetORM library (https://github.com/uestla/YetORM)
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Petr Kessler (http://kesspess.1991.cz)
 */

namespace Nextras\Orm\Reflection;

use Nette;
use Nextras\Orm\InvalidArgumentException;



/**
 * @property-read string $type
 * @property-read bool $readonly
 * @property-read string $column
 */
class EntityProperty extends Nette\Object
{

	/** @var string */
	protected $name;

	/** @var string */
	protected $column;

	/** @var string */
	protected $type;

	/** @var bool */
	protected $nullable;

	/** @var bool */
	protected $readonly;



	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @param  bool
	 */
	public function __construct($name, $column, $type, $nullable, $readonly)
	{
		$this->name = (string) $name;
		$this->column = (string) $column;
		$this->type = (string) $type;
		$this->nullable = (bool) $nullable;
		$this->readonly = (bool) $readonly;
	}



	/** @return string */
	public function getType()
	{
		return $this->type;
	}



	/** @return bool */
	public function isReadonly()
	{
		return $this->readonly;
	}



	/** @return string */
	public function getColumn()
	{
		return $this->column;
	}



	public function fixType($value)
	{
		$type = gettype($value);
		if ($type === 'NULL') {
			if (!$this->nullable) {
				throw new InvalidArgumentException("Property '{$this->name}' cannot be NULL.");
			}

		} elseif ($type === 'object') {
			if (!($value instanceof $this->type)) {
				throw new InvalidArgumentException("Instance of '{$this->type}' expected, '$type' given.");
			}

		} elseif ($type !== $this->type) {
			throw new InvalidArgumentException("Invalid type - '{$this->type}' expected, '$type' given.");
		}

		return $value;
	}



	public function setType($value)
	{
		$type = gettype($value);
		if ($type === 'NULL') {
			if (!$this->nullable) {
				throw new InvalidArgumentException("Property '{$this->name}' cannot be NULL.");
			}

		} elseif ($type === 'object') {
			if (!($value instanceof $this->type)) {
				throw new InvalidArgumentException("Invalid instance - '{$this->type}' expected, '$type' gotten.");
			}

		} elseif (@settype($value, $this->type) === FALSE) { // intentionally @
			throw new InvalidArgumentException("Unable to set type '{$this->type}' from '$type'.");
		}

		return $value;
	}

}

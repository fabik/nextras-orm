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

namespace Nextras\Orm\Reflection;

use Nette\Utils\Strings;
use Nette\Reflection\Method;
use Nette\Reflection\ClassType;



/**
 * @property-read EntityProperty[] $properties
 * @property-read Method[] $getters
 */
class EntityType extends ClassType
{

	/** @var EntityProperty[] */
	private $properties = NULL;



	/**
	 * @return EntityProperty[]
	 */
	public function getProperties($filter = NULL)
	{
		$this->loadProperties();
		return $this->properties;
	}



	/**
	 * @param  string
	 * @return EntityProperty|NULL
	 */
	public function getProperty($name)
	{
		$this->loadProperties();
		return isset($this->properties[$name]) ? $this->properties[$name] : NULL;
	}



	/**
	 * @param  string
	 * @return bool
	 */
	public function hasProperty($name)
	{
		$this->loadProperties();
		return isset($this->properties[$name]);
	}



	private function loadProperties()
	{
		if ($this->properties !== NULL) {
			return;
		}

		$this->properties = array();
		$classTree = array($current = $this->name);

		while (TRUE) {
			if (($current = get_parent_class($current)) === FALSE || $current === 'Nextras\\Orm\\Entity') {
				break;
			}

			$classTree[] = $current;
		}

		foreach (array_reverse($classTree) as $class) {
			$this->parseAnnotations(ClassType::from($class));
		}
	}



	private function parseAnnotations(ClassType $reflection)
	{
		foreach ($reflection->getAnnotations() as $ann => $values) {
			if ($ann !== 'property' && $ann !== 'property-read') {
				continue;
			}

			foreach ($values as $tmp) {
				$split = Strings::split($tmp, '#\s+#');
				if (count($split) < 2) {
					continue;
				}

				$this->parseAnnotationValue($split, $ann);
			}
		}
	}



	private function parseAnnotationValue(array $split, $propertyKey)
	{
		list($type, $var) = $split;

		// support NULL type
		$nullable = FALSE;
		$types = explode('|', $type, 2);
		if (count($types) === 2) {
			if (strcasecmp($types[0], 'null') === 0) {
				$type = $types[1];
				$nullable = TRUE;

			} elseif (strcasecmp($types[1], 'null') === 0) {
				$type = $types[0];
				$nullable = TRUE;
			}
		}

		// unify type name
		if ($type === 'bool') {
			$type = 'boolean';

		} elseif ($type === 'int') {
			$type = 'integer';
		}

		$name = substr($var, 1);
		$readonly = $propertyKey === 'property-read';

		// parse column name
		$column = $name;
		if (isset($split[2]) && $split[2] === '->' && isset($split[3])) {
			$column = $split[3];
		}

		$this->properties[$name] = new EntityProperty($name, $column, $type, $nullable, $readonly);
	}

}

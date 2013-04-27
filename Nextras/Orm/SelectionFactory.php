<?php

/**
 * This file is part of the Nextras\ORM library
 *
 * @license    MIT
 * @link       https://github.com/nextras
 * @author     Jan Skrasek
 */

namespace Nextras\Orm;

use Nette;



class SelectionFactory extends Nette\Database\SelectionFactory
{

	/** @var array */
	private $entityMapping = array();



	public function addMap($entityName, $tableName)
	{
		$this->entityMapping[$entityName] = $tableName;
	}



	public function tableByEntity($entityName)
	{
		if (!isset($this->entityMapping[$entityName])) {
			throw new InvalidArgumentException('Unknow table for entity ' . $entityName);
		}

		return parent::table($this->entityMapping[$entityName]);
	}

}

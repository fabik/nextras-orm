<?php


/**
 * @property string $name
 * @property DateTime|NULL $born
 * @property-read string $fullName
 * @property-read int $id
 */
abstract class Person extends Nextras\Orm\Entity
{

	protected function getName($name)
	{
		return strtoupper($name);
	}

	protected function getFullName()
	{
		return 'Full name: ' . $this->name;
	}

}

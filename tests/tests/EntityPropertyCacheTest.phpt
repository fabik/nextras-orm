<?php

use Tester\Assert;
require __DIR__ . '/bootstrap.php';



/**
 * @property-read DateTime $updated
 */
class PersonEntity extends Person
{
	protected function getUpdated()
	{
		return new Nette\DateTime('2000-01-01');
	}
}



$person = new PersonEntity;
$person->name = 'Jan';

Assert::equal('JAN', $person->name);
Assert::equal(new Nette\DateTime('2000-01-01'), $updated = $person->updated);
Assert::same($updated, $person->updated);
Assert::equal('Full name: JAN', $person->fullName);

$person->name = 'Peter';

Assert::equal('PETER', $person->name);
Assert::equal('Full name: PETER', $person->fullName);

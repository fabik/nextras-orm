<?php

use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Diagnostics\Debugger;
use Nextras\Orm\SelectionFactory;

if (@!include __DIR__ . '/../../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}


// configure environment
Tester\Helpers::setup();
date_default_timezone_set('Europe/Prague');


// create temporary directory
define('TEMP_DIR', __DIR__ . '/../tmp/' . getmypid());
@mkdir(dirname(TEMP_DIR)); // @ - directory may already exist
Tester\Helpers::purge(TEMP_DIR);



function id($a) { return $a; }

$cacheStorage = new FileStorage(TEMP_DIR);

$loader = new Nette\Loaders\RobotLoader;
$loader->setCacheStorage($cacheStorage);
$loader->addDirectory(__DIR__ . '/../../Nextras');
$loader->addDirectory(__DIR__ . '/../model');
$loader->register();



$connection = new Nette\Database\Connection('mysql:host=localhost;dbname=nextras_orm_test', 'root', '');
$reflection = new Nette\Database\Reflection\DiscoveredReflection($connection, $cacheStorage);
$selectionFactory = new SelectionFactory($connection, $reflection, $cacheStorage);
$selectionFactory->addMap('Author', 'author');
$selectionFactory->addMap('Book', 'book');
$selectionFactory->addMap('Tag', 'tag');



Tester\Helpers::lock(md5('mysql:host=localhost;dbname=nextras_orm_test'), dirname(TEMP_DIR));
Nette\Database\Helpers::loadFromFile($connection, __DIR__ . "/../db/mysql-init.sql");



if (!Debugger::$consoleMode) {
	Debugger::enable(Debugger::DEVELOPMENT, FALSE);
	Debugger::$strictMode = TRUE;
	Debugger::$maxDepth = FALSE;
	Debugger::$maxLen = FALSE;
	Debugger::$bar->addPanel(new Nette\Database\Diagnostics\ConnectionPanel($connection));
}

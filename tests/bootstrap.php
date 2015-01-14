<?php

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();

$configurator = new Nette\Configurator;

$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(__DIR__ . '/temp');
$configurator->addConfig(__DIR__ . '/config.neon');
$configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/../src')
	->addDirectory(__DIR__ . '/Zax')
	->register();

return $configurator->createContainer();

<?php

$autoloader_locations = [
	__DIR__ . '/../vendor/autoload.php',          // Package is used as standalone, so vendor dir is installed below package root
	__DIR__ . '/../../../../vendor/autoload.php', // Package is installed as a dependency with composer and resides inside vendor dir
];

foreach ($autoloader_locations AS $autoloader_location) {
	if (\file_exists($autoloader_location)) {
		$autoloader = require_once($autoloader_location);
		break;
	}
}

if (!isset($autoloader)) {
	throw new \RuntimeException('Can not find composer autoloader. Run "composer update" to initialize dependencies and make sure you did not change the vendor directory name.');
}

$autoloader->addPsr4('nexxes\\tokenmatcher\\', __DIR__);

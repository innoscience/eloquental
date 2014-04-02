<?php

error_reporting(E_ALL | E_STRICT);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	$loader = require __DIR__ . '/../vendor/autoload.php';
}
elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
	$loader = require __DIR__ . '/../../../autoload.php';
}
else {
	throw new \Exception('Cannot find autoload.php captain. Run `composer install` to create autoload files or check composer.');
}

$loader->addPsr4('Innoscience\\Eloquental\\Tests\\', __DIR__);

unset($loader);

$unitTesting = true;

$testEnvironment = 'testing';

require_once __DIR__.'/../../../../bootstrap/autoload.php';
$app = require_once __DIR__.'/../../../../bootstrap/start.php';

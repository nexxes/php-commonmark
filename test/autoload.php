<?php

if (is_dir(__DIR__ . '/../vendor')) {
	$autoloader = require_once(__DIR__ . '/../vendor/autoload.php');
} else {
	$autoloader = require_once(__DIR__ . '/../../../vendor/autoload.php');
}

$autoloader->addPsr4('nexxes\\stmd\\', __DIR__);

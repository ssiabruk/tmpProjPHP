<?php

/*
 *  POSHUK electron-optical complex
 *
 *  @author       Alex Grey
 *  @copyright    Copyright © 2019 Alex Grey (alex@grey.kiev.ua)
 *  @license      https://opensource.org/licenses/GPL-3.0
 *  @since        Version 1.0
 *
 */


require __DIR__ . '/../vendor/autoload.php';

// Init app
$configs = require __DIR__ . '/configs.php';

$app = new \Slim\App($configs);

//Setup dependencies
require __DIR__ . '/dependencies.php';

// Register routes
require __DIR__ . '/routes.php';

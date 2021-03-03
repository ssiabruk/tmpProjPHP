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


define('BASE_PATH', realpath(__DIR__ . '/..'));

//session_start();

require BASE_PATH . '/config/bootstrap.php';

$app->run();

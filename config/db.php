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


$container['db'] = function ($container) {
    $cfg = $container->get('configs')['db'];
    $dsn = 'pgsql:host=' . $cfg['host'] . ';dbname=' . $cfg['dbname'];
    try {
        $db = new \PDO($dsn, $cfg['user'], $cfg['passwd']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (\PDOException $e) {
        echo 'Connection failed', PHP_EOL;
        $display_errors = $container->get('settings')['displayErrorDetails'];
        if (!$display_errors) {
            $error = $e->getMessage() . "\n";
            error_log($error, 3, BASE_PATH . '/temp/logs/db-errors.log');
        } else {
            echo '<br />', PHP_EOL, $e->getMessage();
        }
        exit();
    }
    return $db;
};

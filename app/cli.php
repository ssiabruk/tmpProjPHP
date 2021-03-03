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


//set_time_limit(65);
define('BASE_PATH', realpath(__DIR__ . '/..'));
require __DIR__ . '/../vendor/autoload.php';
//exit(0);

// Init app
$configs = require BASE_PATH . '/config/configs.php';

$command_line = getopt(null, ['c:','n:','s:']);
$command = @$command_line['c'];
$cid = @$command_line['n'];
$sid = @$command_line['s'];

if (!$command) {
    echo 'Try cli.php --c <command> --n <param>', "\r\n";
    exit();
}

if ($command !== 'test' && !$cid) {
    echo 'Try cli.php --c <command> --n <param>', "\r\n";
    exit();
}

$class = new \ReflectionClass('App\\Controllers\\ConsoleController');
if (!$class->isInstantiable() || !$class->hasMethod('wc2' . $command)) {
    echo 'Command does not exist', "\r\n";
    exit();
}

$configs['environment'] = \Slim\Http\Environment::mock([
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
    'REQUEST_URI' => '/api/' . $command,
    //'QUERY_STRING' => 'cid=' . $cid,
    'HTTP_HOST' => 'localhost',
    'SERVER_PORT' => '80'
]);

$app = new \Slim\App($configs);

//Setup dependencies
$container = $app->getContainer();
require BASE_PATH . '/config/db.php';

$app->add(function($request, $response, $next) use ($container, $cid) {
    try {
        return $next($request, $response);
    } catch(\App\Libs\AppException $e) {
        $error = '';
        $error_message = $e->getMessage() . "\n\n";
        $error.= $error_message . 'Code: ' . $e->getCode() . "\n";
        $error.= 'File: ' . $e->getFile() . "\n";
        $error.= 'Line: ' . $e->getLine();
        $log_file_name = 'cli-' . $cid . '-' . date('YmdHis') . '.log';
        error_log($error, 3, BASE_PATH . '/temp/logs/' . $log_file_name);
        echo $error_message;
        exit();
    }
    return $next($request, $response);
});

$container['ConsoleController'] = function ($container) use ($cid, $sid) {
    //return new \App\Controllers\ConsoleController($container, $cid); // dirty hack
    $console_obj = new \App\Controllers\ConsoleController($container);
    $console_obj->setComplex($cid);
    if ($sid) {
        $console_obj->setSession($sid);
    }
    return $console_obj;
};

// Register routes
$app->get('/api/ticks', 'ConsoleController:wc2ticks');
$app->get('/api/detects', 'ConsoleController:wc2detects');
$app->get('/api/alarms', 'ConsoleController:wc2alarms');
$app->get('/api/images', 'ConsoleController:wc2images');
$app->get('/api/test', 'ConsoleController:wc2test');
$app->get('/api/lost', 'ConsoleController:wc2lost');

$app->run();

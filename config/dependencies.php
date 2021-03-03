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


// Dependency Injection Container init
$container = $app->getContainer();

// DB object
require __DIR__ . '/db.php';

// Sessions class
$container['sessions'] = function ($container) {
    $cfg = $container->get('configs');
    $db = $container->get('db');
    return new \App\Libs\Sessions($db, $cfg);
};
//$container->get('sessions')->start();

// CSRF protect module
$container['csrf'] = function ($container) {
    $container->get('sessions')->fix();
    return new \Maer\Security\Csrf\Csrf();
};

// Cache module
$container['cache'] = function () use ($app) {
    return new \SNicholson\SlimFileCache\Cache($app, BASE_PATH . '/cache');
};

// Custom view
$container['view'] = function ($container) {
    $cfg = $container->get('configs')['view'];
    return new \App\Libs\RenderView($cfg);
};

$container['lang'] = function ($container) {
    $current_lang = $container->get('sessions')->get('lang');
    return new \App\Libs\Languages($current_lang);
};

// Middleware. Check user access
$userMiddleware = function ($request, $response, $next) use ($container) {
    $is_logged = $container->get('sessions')->get('is_logged');
    if (!$is_logged) {
        if ($request->isXhr()) {
            $data = [
                'message' => 'user_not_logged',
                'result' => 'error'
            ];
            return $response->withJson($data, 401);
        } else {
            return $response->withRedirect('/login', 301);
        }
    }
    $response = $next($request, $response);
    return $response;
};

// Middleware. Not use for logged users
$anonMiddleware = function ($request, $response, $next) use ($container) {
    $is_logged = $container->get('sessions')->get('is_logged');
    if ($is_logged) {
        if ($request->isXhr()) {
            $data = [
                'message' => 'user_logged',
                'result' => 'error'
            ];
            return $response->withJson($data, 401);
        } else {
            return $response->withRedirect('/', 301);
        }
    }
    $response = $next($request, $response);
    return $response;
};

// Middleware. CSRF protection
$checkToken = function ($request, $response, $next) use ($container) {
    //$post_data = $request->getParsedBody();
    //$token = $post_data['csrf_token'];
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    $csrf = $container->get('csrf');
    $valid = $csrf->validateToken($token);
    if (!$valid) {
        $data = [
            'message' => 'token_not_found',
            'result' => 'error'
        ];
        return $response->withJson($data, 401);
    }
    //$csrf->resetAll();
    $response = $next($request, $response);
    return $response;
};

$checkAjax = function ($request, $response, $next) {
    if (!$request->isXhr()) {
        echo 'Not AJAX request';
        exit();
    }
    $response = $next($request, $response);
    return $response;
};

// Middleware. Trailing slash in route patterns
$app->add(function($request, $response, $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        $uri = $uri->withPath(substr($path, 0, -1));
        if($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        }
        else {
            return $next($request->withUri($uri), $response);
        }
    }
    return $next($request, $response);
});

// Middleware. App exception log
$app->add(function($request, $response, $next) use ($container) {
    try {
        return $next($request, $response);
    } catch(\App\Libs\AppException $e) {
        $display_errors = $container->get('settings')['displayErrorDetails'];
        if (!$display_errors) {
            $error = $e->getMessage() . "\n\n";
            $error.= 'Code: ' . $e->getCode() . "\n";
            $error.= 'File: ' . $e->getFile() . "\n";
            $error.= 'Line: ' . $e->getLine();
            $log_file_name = 'exc-' . date('YmdHis') . '.log';
            error_log($error, 3, BASE_PATH . '/temp/logs/' . $log_file_name);
        }
        if ($request->isXhr()) {
            $data = [
                'message' => 'system_failure',
                'result' => 'error',
                'full' => $e->getMessage()
            ];
            return $response->withJson($data, 500);
        } else {
            echo 'SYSTEM FAILURE 101';
            echo '<br />', $e->getMessage();
        }
        if (!$display_errors) {
            exit();
        }
    }
    return $next($request, $response);
});

// Pimple has controllers
$container['IndexController'] = function ($container) {
    return new \App\Controllers\IndexController($container);
};

$container['AuthController'] = function ($container) {
    return new \App\Controllers\AuthController($container);
};

$container['ProfileController'] = function ($container) {
    return new \App\Controllers\ProfileController($container);
};

$container['MapController'] = function ($container) {
    return new \App\Controllers\MapController($container);
};

$container['VideoController'] = function ($container) {
    return new \App\Controllers\VideoController($container);
};

$container['StorageController'] = function ($container) {
    return new \App\Controllers\StorageController($container);
};

$container['SettingsController'] = function ($container) {
    return new \App\Controllers\SettingsController($container);
};

$container['SystemController'] = function ($container) {
    return new \App\Controllers\SystemController($container);
};

$container['DiagController'] = function ($container) {
    return new \App\Controllers\DiagController($container);
};

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


namespace App\Controllers;

use Slim\Container;

class Controller
{
    protected $view;
    protected $db;
    protected $di;
    protected $lang;
    protected $clc; // current lang code
    protected $sessions;
    protected $site_url;
    protected $user;

    public function __construct(Container $di)
    {
        $this->di = $di;
        $this->sessions = $this->di->get('sessions');
        $this->db = $this->di->get('db');
        $this->lang = $this->di->get('lang');
        $this->clc = $this->lang->getCurrentLangCode();
        $this->site_url = $this->di->get('configs')['site_url'];
        $this->view = $this->di->get('view');
        $this->view->setVar('active_menu_item', false, true);
        $this->user = $this->sessions->get('user');
        $this->view->setVar('user', $this->user);
        $this->callInit();
    }

    public function callInit()
    {
        if (method_exists($this, 'init')){
            $this->init();
        }
    }

    public function prnJson($msg, $type, $response)
    {
        $data = [
            'message' => $msg,
            'result' => $type
        ];
        return $response->withJson($data);
    }
}

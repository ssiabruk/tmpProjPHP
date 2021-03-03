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

use App\Controllers\Controller;
use App\Models\Settings;
use App\Models\System;

class VideoController extends Controller
{
    //private $user;
    private $settings;
    private $system;

    public function init()
    {
        //$this->view = $this->di->get('view');
        //$this->user = $this->sessions->get('user');
        //$this->view->setVar('user', $this->user);
        $this->settings = new Settings($this->db);
        $this->system = new System($this->db);
        $this->view->setVar('active_menu_item', 'stream');
    }

    public function index($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('video');
        $this->view->setVar('title', $lang_labels['video_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $complex_list = $this->settings->getListComplex(true);
        $this->view->setVar('clist', $complex_list);
        $cids = implode(',', array_column($complex_list, 'id'));
        $cids = '[' . $cids . ']';
        $this->view->setVar('cids', $cids);

        $this->view->setJsUrl('actionCheck', $this->site_url . '/system/streamcheck');
        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        /*$current_sessions_modes = [];
        foreach ($complex_list as $cl) {
            $current_sessions_modes[$cl['id']] = $this->system->getDeparture($cl['id'])['status'];
        }
        $this->view->setVar('modes', $current_sessions_modes);*/
        //$port = $this->di->get('configs')['stream_port'];
        //$this->view->setVar('port', $port);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        //$this->view->setCss('cabinet/mainview');
        $this->view->setCss('colors');
        $this->view->setJsFile('cabinet/video');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/stream');
    }
}

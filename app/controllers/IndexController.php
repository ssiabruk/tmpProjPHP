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
use App\Models\Events;
use App\Models\System;
use App\Libs\Helper;
use App\Libs\Services;

define('WC2_GPS_ERROR_CODE', 15);

class IndexController extends Controller
{
    //private $user;
    private $settings;
    private $events;
    private $services;

    public function init()
    {
        //$this->view = $this->di->get('view');
        //$this->user = $this->sessions->get('user');
        //$this->view->setVar('user', $this->user);
        $this->settings = new Settings($this->db);
        $this->events = new Events($this->db);
        $this->services = new Services($this->events);
    }

    public function index($request, $response)
    {
        if (isset($this->user['login']) && $this->user['login']) {
            $this->indexAuth($request, $response);
        } else {
            $this->indexAnon($request, $response);
        }
    }

    private function indexAnon($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('map');
        $this->view->setLangLabes($lang_labels, $this->clc);
        $this->view->setVar('title', $lang_labels['map_title']);
        $lang_labels = $this->lang->loadLangLabels('login');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        $this->view->setJsUrl('actionMap', $this->site_url . '/main/works');
        $this->view->setJsUrl('actionMapRefresh', $this->site_url . '/main/refresh');
        $this->view->setJsUrl('actionLastDetects', $this->site_url . '/main/detects');
        $this->view->setJsUrl('actionCheck', $this->site_url . '/main/streamcheck');
        $this->view->setJsUrl('departClient', $this->site_url . '/main/departed');

        $map_url = $this->di->get('configs')['map_url'];
        $this->view->setJsUrl('mapUrl', $map_url);

        $complex_list = $this->settings->getListComplex(true);
        $this->view->setVar('clist', $complex_list);
        $current_complex = $this->sessions->get('active_cid');
        if (!$current_complex) {
            $current_complex = $complex_list[0]['id'];
            $this->sessions->set('active_cid', $current_complex);
        }

        $active_complex = $this->sessions->get('active_cid');
        $this->view->setVar('active_complex', $active_complex);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');
        $this->view->setCss('colors');

        $this->view->setCss('/map/leaflet', true);
        $this->view->setJsFile('/map/leaflet', true);
        $this->view->setCss('/map/fullscreen/Control.FullScreen', true);
        $this->view->setJsFile('/map/fullscreen/Control.FullScreen', true);
        $this->view->setJsFile('auth/map');
        $this->view->setCss('auth/map');
        $this->view->setJsFile('/map/leaflet.textpath', true);

        $this->view->setLayout('auth');
        $this->view->render('auth/map');
    }

    public function viewDetect($request, $response)
    {
        $detect_id = $request->getAttribute('detect');
        if (!$detect_id || !is_numeric($detect_id)) {
            exit();
        }
        $lang_labels = $this->lang->loadLangLabels('map');
        $this->view->setVar('title', $lang_labels['map_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);

        $this->view->setJsUrl('actionGetImage', $this->site_url . '/main/getimage');

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        $detection = $this->events->getDetectionById($detect_id);
        if (!$detection) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('Page not found');
        }
        $this->view->setVar('dtid', $detect_id);
        if ($detection['imgfull']) {
            $detection['image'] = $this->site_url.'/detects/'.$detection['id'].'/'.$detection['session_id'].'/'.$detection['image'].'-full.jpg"';
        } else {
            $detection['image'] = $this->site_url . '/images/noimageyet.png';
        }

        $this->view->setCss('jquery.guillotine');
        $this->view->setJsFile('jquery.guillotine');

        $this->view->setVar('detect', $detection);
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');
        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');

        $this->view->setCss('/map/leaflet', true);
        $this->view->setJsFile('/map/leaflet', true);
        $this->view->setJsFile('auth/map');
        $this->view->setCss('auth/map');

        $this->view->setLayout('auth');
        $this->view->render('auth/mapviewd');
    }

    private function indexAuth($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('dashboard');
        $this->view->setVar('title', $lang_labels['dash_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        //if ($this->user['role'] == 'admin') {
            $this->view->setJsUrl('actionStart', $this->site_url . '/system/start');
            $this->view->setJsUrl('actionControl', $this->site_url . '/system/control');
        //}

        $this->view->setJsUrl('actionInfo', $this->site_url . '/system/info');
        $this->view->setJsUrl('actionTele', $this->site_url . '/system/telemetry');
        //$this->view->setJsUrl('actionModes', $this->site_url . '/client/getmodes');
        $this->view->setJsUrl('actionClient', $this->site_url . '/client/works');
        $this->view->setJsUrl('actionAlarm', $this->site_url . '/client/alarms');
        $this->view->setJsUrl('actionGetAlarms', $this->site_url . '/client/getalarms');
        $this->view->setJsUrl('actionGetGPS', $this->site_url . '/client/getgps');

        $map_url = $this->di->get('configs')['map_url'];
        $this->view->setJsUrl('mapUrl', $map_url);
        $env = $this->di->get('configs')['environment']; // dirty hack
        $this->view->setVar('cur_env', $env);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        $complex_list = $this->settings->getListComplex(true);
        $this->view->setVar('clist', $complex_list);
        $cids = implode(',', array_column($complex_list, 'id'));
        $cids = '[' . $cids . ']';
        $this->view->setVar('cids', $cids);
        /*if ($complex_list) {
            $system = new System($this->db);
            $departures = $system->getDeparturedList();
            var_dump($departures); die;
        }*/
        $data = $this->settings->getComplexModes();
        $modes = [];
        if ($data) foreach ($data as $d) {
            $modes[$d['complex_id']] = $d['modes'];
        }
        $this->view->setVar('cmodes', $modes);
        $all_modes = Helper::listModes($this->clc);
        $this->view->setVar('all_modes', $all_modes);
        //var_dump($all_modes); die;
        //var_dump($complex_list); die;
        //var_dump($modes); die;

        $this->view->setCss('/map/leaflet', true);
        $this->view->setJsFile('/map/leaflet', true);
        $this->view->setJsFile('cabinet/mapconf');
        $this->view->setCss('colors');
        $this->view->setJsFile('cabinet/dashboard');
        $this->view->setCss('cabinet/dashboard');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/dashboard');
    }

    public function clientWorks($request, $response)
    {
        $complex_list = $this->settings->getListComplex(true);
        if (!$complex_list) {
            return $this->prnJson('empty_complex_list', 'error', $response);
        }
        $result = [];

        $req_data = $request->getParsedBody();
        $command = Helper::xss($req_data['command']);
        if ($command == 'init') {
            foreach ($complex_list as $cl) {
                $this->services->startTicks($cl['id']);
                $tmp = $this->events->getTick($cl['id']);
                $result[] = [
                    'id' => $cl['id'],
                    'cid' => $cl['cid'],
                    'cip' => $cl['cip'],
                    'cc' => $cl['colour'],
                    'lat' => $tmp['latitude'],
                    'lon' => $tmp['longitude']
                ];
            }
            $result = json_encode($result);
            return $this->prnJson($result, 'success', $response);
        }
        if ($command == 'visualdata') {
            foreach ($complex_list as $cl) {
                $this->services->startTicks($cl['id']);
                $tmp = $this->events->getTick($cl['id']);
                $result[$cl['id']] = [
                    'lat' => $tmp['latitude'],
                    'lon' => $tmp['longitude'],
                    'cid' => $cl['id']
                ];
            }
            $result = json_encode($result);
            return $this->prnJson($result, 'success', $response);
        }
    }

    public function clientAlarms($request, $response)
    {
        //$alarms = $events->getAlarms();
        $alarms = $this->events->getCurrentAlarms();
        //var_dump($alarms); die;
        if (!$alarms) {
            return $response->withJson(['result' => '']);
        }
        $res = [];
        foreach ($alarms as $a) {
            $res[] = $a['id'];
        }
        $res = array_values(array_unique($res));
        if ($res) {
            foreach ($res as $r) {
                $this->services->startAlarms($r);
            }
        }
        $data = [
            'result' => 'alarm',
            'list' => $res
        ];
        return $response->withJson($data);
    }

    public function currentAlarms($request, $response)
    {
        $req_data = $request->getParsedBody();
        $cid = Helper::xss($req_data['cid']);
        if (!$cid) {
            return $this->prnJson('', 'error', $response);
        }
        //$alarms = $events->getCurrentAlarms();
        $alarms = $this->events->getComplexAlarms($cid);
        //var_dump($alarms); die;
        if (!$alarms) {
            $lang_labels = $this->lang->loadLangLabels('dashboard');
            return $response->withJson(['result' => $lang_labels['no_alarms']]);
        }
        $result = [];
        foreach ($alarms as $al) {
            $result[$al['id']]['data'][] = [
                'code' => $al['code'],
                'text' => $al['message']
            ];
            $result[$al['id']]['cc'] = $al['colour'];
            $result[$al['id']]['cid'] = $al['ccid'];
        }
        $lang_labels = $this->lang->loadLangLabels('errors');
        $alarm_info = '';
        foreach ($result as $res) {
            $alarm_info = '<span class="fgc-' . $res['cc'] . '">' . $res['cid'] . '</span>';
            $alarm_info.= '<ul class="small">';
            foreach ($res['data'] as $d) {
                $alarm_info.= '<li>' . $lang_labels[$d['code']] . '</li>';
            }
            $alarm_info.= '</ul><br />';
        }
        $data = [
            'alarms' => $alarm_info,
            'result' => 'success'
        ];
        return $response->withJson($data);
    }

    public function getmodes($request, $response)
    {
        $req_data = $request->getParsedBody();
        $cid = Helper::xss($req_data['cid']);
        $data = $this->settings->getComplexModesById($cid);
        if (!is_array($data) || !$data) {
            return $this->prnJson('error_empty_modes', 'error', $response);
        }
        $data = [
            'modes' => $data['modes'],
            'result' => 'success'
        ];
        return $response->withJson($data);
    }

    public function getGPS($request, $response)
    {
        $req_data = $request->getParsedBody();
        $cid = Helper::xss($req_data['cid']);
        if (!$cid) {
            return $this->prnJson('', 'error', $response);
        }
        $error_code = WC2_GPS_ERROR_CODE;
        //$alarms = $events->getAlarms();
        $alarms = $this->events->getCurrentAlarms();
        /*$alarms = array_column($alarms, 'code');
        if (in_array($error_code, $alarms)) {
            return $this->prnJson('', 'success', $response);
        }*/
        if (!$alarms) {
            return $this->prnJson('', 'error', $response);
        }
        $res = false;
        foreach ($alarms as $al) {
            if ($al['id'] == $cid && $al['code'] == $error_code) {
                $res = true;
            }
        }
        if ($res) {
            return $this->prnJson('', 'success', $response);
        } else {
            return $this->prnJson('', 'error', $response);
        }
    }

    public function selectComplex($request, $response)
    {
        $req_data = $request->getParsedBody();
        $cid = Helper::xss($req_data['cid']);
        if (!$cid) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $this->sessions->set('active_cid', $cid);
        return $this->prnJson('', 'success', $response);
    }
}

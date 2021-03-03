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
use App\Models\Events;
use App\Libs\ApiSockets;
use App\Libs\Services;
use App\Libs\Helper;
use App\Libs\AppException;

class SystemController extends Controller
{
    //private $user;
    private $settings;
    private $system;
    private $apikey;
    private $complex;
    private $socket_config;
    private $tick_timeout;
    private $writelog;
    private $services;
    private $events;

    public function init()
    {
        //$this->user = $this->sessions->get('user');
        $this->settings = new Settings($this->db);
        $this->system = new System($this->db);
        $this->apikey = $this->di->get('configs')['apikey'];
        $this->writelog = $this->settings->getLoggerStatus();
        $this->tick_timeout = $this->di->get('configs')['tick_timeout'];
        $this->events = new Events($this->db);
        $this->services = new Services($this->events);
    }

    public function streamcheck($request, $response)
    {
        $req_data = $request->getParsedBody();
        $cid = Helper::xss($req_data['cid']);
        $res = $this->loadComplex($cid);
        if (!$res) {
            return $this->prnJson('Wrong complex ID', 'error', $response);
        }
        //$check_socket = $this->socketWork();
        $check_socket = (new ApiSockets($this->writelog, 'stream'))->socketWork($this->socket_config);
        if (!$check_socket) {
            $data = [
                'result' => 'info',
                'cid' => $cid,
                'message' => 'error_complex_connect'
            ];
            return $response->withJson($data);
        }
        $result = $check_socket->api('status');
        $check_socket->close();

        if (@$result['response_body']['grabber']['active'] !== 'active') {
            //return $this->prnJson('error_grabber_active', 'error', $response);
            $data = [
                'result' => 'error',
                'cid' => $cid,
                'cip' => $this->complex['cip']
            ];
            return $response->withJson($data);
        }

        $status = $this->system->getDeparture($cid, true)['status'];
        //if ($status == 'record') {
            //$status_url = '/stream.mjpg?live=1&raw=0&size=30&det=10';
            $status_url = Helper::prepareUrl('stream_record', ['%size' => 30, '%det' => 10]);
        /*} elseif ($status == 'recdet') {
            //$status_url = '/stream.mjpg?live=1&raw=1&size=30&det=10';
            $status_url = Helper::prepareUrl('stream_recdet', ['%size' => 30, '%det' => 10]);
        } else {
            $data = [
                'result' => 'info',
                'cid' => $cid,
                'message' => 'error_grabber_active'
            ];
            return $response->withJson($data);
        }*/
        $port = $this->di->get('configs')['stream_port'];
        $data = [
            'result' => 'success',
            'cid' => $cid,
            'cip' => $this->complex['cip'],
            'videourl' => ':' . $port . $status_url
        ];
        return $response->withJson($data);
    }

    public function telemetry($request, $response)
    {
        //$this->sessions->fix();
        $message = [];
        $complex_list = $this->settings->getListComplex(true); // not optimal
        if (!$complex_list) {
            return $this->prnJson('Complex ID not found', 'error', $response);
        }

        $lang_labels = $this->lang->loadLangLabels('errors');
        $error_text = '<div class="col-12">' . $lang_labels['error_connect'] . '</div>';

        foreach ($complex_list as $cl) {
            $res = $this->loadComplex($cl['id']);
            if (!$res) {
                return $this->prnJson('Wrong complex ID', 'error', $response);
            }

            $tick = $this->hasTick($cl['id']);
            if (!$tick) {
                $message[$cl['id']] = $error_text;
                continue;
            }

            $tele_socket = (new ApiSockets($this->writelog, 'telemetry'))->socketWork($this->socket_config);
            if (!$tele_socket) {
                $message[$cl['id']] = $error_text;
                continue;
                //return $this->prnJson('error_complex_connect', 'error', $response);
            }
            $result = $tele_socket->api('device');
            $tele_socket->close();

            //$events = new Events($this->db);
            //$tick = $events->getTick($cl['id']);
            $result['tick'] = $tick;
            $lang_labels = $this->lang->loadLangLabels('apiresp');
            $result = Helper::parseDeviceResult($result, $lang_labels);
            if ($result['status'] == 'error') {
                $message[$cl['id']] = $error_text;
                continue;
                //return $this->prnJson($result['message'], 'error', $response);
            }
            //unset($result['status']);
            /*if (isset($tick['latitude']) && isset($tick['longitude']) && $tick['latitude'] && $tick['longitude']) {
                $result['c'] = ['lat' => $tick['latitude'], 'lon' => $tick['longitude']];
            }*/
            $message[$cl['id']] = $result['message'];
        }
        $data = [
            'message' => $message,
            'result' => 'success'
        ];
        return $response->withJson($data);
    }

    /*public function telemetryBAK($request, $response)
    {
        $req_data = $request->getParsedBody();
        $cid = Helper::xss($req_data['cid']);
        $command = Helper::xss($req_data['command']);
        $res = $this->loadComplex($cid);
        if (!$res) {
            return $this->prnJson('Wrong complex ID', 'error', $response);
        }
        $tele_socket = (new ApiSockets())->socketWork($this->socket_config);
        if (!$tele_socket) {
            return $this->prnJson('error_complex_connect', 'error', $response);
        }
        $result = $tele_socket->api($command);
        $tele_socket->close();
        $lang_labels = $this->lang->loadLangLabels('apiresp');

        $events = new Events($this->db);
        $tick = $events->getTick($cid);
        $result['tick'] = $tick;
        $result = Helper::parseDeviceResult($result, $lang_labels);
        if ($result['status'] == 'error') {
            return $this->prnJson($result['message'], 'error', $response);
        }
        if (isset($tick['latitude']) && isset($tick['longitude']) && $tick['latitude'] && $tick['longitude']) {
            $result['c'] = ['lat' => $tick['latitude'], 'lon' => $tick['longitude']];
        }
        return $this->prnJson($result, 'success', $response);
    }*/

    public function control($request, $response)
    {
        /*if ($this->user['role'] !== 'admin') {
            return $this->prnJson('access_denied', 'error', $response);
        }*/
        $req_data = $request->getParsedBody();
        $cid = Helper::xss($req_data['cid']);
        $command = Helper::xss($req_data['command']);
        $res = $this->loadComplex($cid);
        if (!$res) {
            return $this->prnJson('Wrong complex ID', 'error', $response);
        }
        $control_socket = (new ApiSockets($this->writelog, $command))->socketWork($this->socket_config);
        if (!$control_socket) {
            return $this->prnJson('error_complex_connect', 'error', $response);
        }
        $params = ($command === 'reboot')?['reboot' => 1]:false;
        $result = $control_socket->api($command, $params);
        $control_socket->close();
        $lang_labels = $this->lang->loadLangLabels('apiresp');
        $result = Helper::parseControlResult($command, $result, $lang_labels);
        if ($result['status'] == 'error') {
            return $this->prnJson($result['message'], 'error', $response);
        }
        $current_camres = $this->sessions->get('current_camres_' . $cid);
        $current_session = $this->sessions->get('current_session_' . $cid);
        if ($command == 'stop' && (!$current_camres || !$current_session)) {
            $dep = $this->system->getDeparture($cid, true);
            if (!$dep) {
                throw new AppException('Error departured complex');
            }
            $current_camres = $dep['cam_mode'];
            $current_session = $dep['session_id'];
        }
        $this->system->setDeparture(
            $req_data['command'],
            $current_camres,
            $cid,
            $this->complex['cid'],
            $this->user['login'],
            $current_session
        );
        if ($command === 'stop') {
            $this->services->getFinishLost($cid, $current_session);
        }
        if ($command === 'stop' || $command === 'reboot') {
            //@unlink(BASE_PATH . '/temp/proc/' . $cid . '.lost.tmp');
            $this->services->stopDetects($cid);
            $this->sessions->forget('current_camres_' . $cid);
            $this->sessions->forget('current_session_' . $cid);
        }
        return $this->prnJson($result['message'], 'success', $response);
    }

    public function start($request, $response)
    {
        /*if ($this->user['role'] !== 'admin') {
            return $this->prnJson('access_denied', 'success', $response);
        }*/
        $req_data = $request->getParsedBody();
        $env = $this->di->get('configs')['environment']; // dirty hack
        if ($env != 'development' && $req_data['camres'] == 'file') {
            return $this->prnJson('Not in production', 'error', $response);
        }

        $cid = Helper::xss($req_data['cid']);
        $command = Helper::xss($req_data['command']);
        $res = $this->loadComplex($cid);
        if (!$res) {
            return $this->prnJson('Wrong complex ID', 'error', $response);
        }

        /*$events = new Events($this->db);
        $last_detects_actives = (int) $events->getLastDetectionTime();
        $session_lifetime = $this->di->get('configs')['session_lifetime'];
        $current_time = time();
        if (($current_time - $last_detects_actives) > $session_lifetime) {
            Helper::servicesRestart();
        }*/

        $socket = new ApiSockets($this->writelog, 'start');
        $control_socket = $socket->socketWork($this->socket_config);
        if (!$control_socket) {
            return $this->prnJson('error_complex_connect', 'error', $response);
        }
        //var_dump($cid, $command, $req_data['startmode'], $req_data['camres']); die;
        $result = $control_socket->api($command, ['task' => $req_data['startmode'], 'mode' => $req_data['camres']]);
        $control_socket->close();
        if (!$result) {
            return $this->prnJson('error_complex_response', 'error', $response);
        }
        if ($result === 'err.command.notfound') {
            return $this->prnJson('error_complex_action', 'error', $response);
        }
        if ($result === 'err.params.notfound') {
            return $this->prnJson('error_complex_param', 'error', $response);
        }
        $lang_labels = $this->lang->loadLangLabels('apiresp');
        $result = Helper::parseControlResult($command, $result, $lang_labels);
        if ($result['status'] == 'error') {
            return $this->prnJson($result['message'], 'error', $response);
        }

        for ($i = 0; $i < 15; $i++) {
            unset($control_socket);
            sleep(1);
            $control_socket = $socket->socketWork($this->socket_config);
            $session = Helper::getSessions($control_socket, 'current');
            $session = Helper::parseCurrentSession($session);
            $control_socket->close();
            if ($session && $session !== 'false') {
                break;
            }
        }

        if (!$session || $session === 'false') {
            return $this->prnJson('error_complex_response', 'error', $response);
        }
        //error_log($session . "\n", 3, BASE_PATH . '/var/logs/current_session.txt');
        if ($req_data['startmode'] === 'recdet') {
            //@unlink(BASE_PATH . '/temp/proc/' . $cid . '.lost.tmp');
            $start_detects = $this->services->startDetects($cid);
            $complex_dir = BASE_PATH . '/public/detects/' . $cid;
            $image_dir = $complex_dir . '/' . $session;
            $created1 = $created2 = true;
            if (!is_dir($image_dir)) {
                if (!is_dir($complex_dir)) {
                    $created1 = mkdir($complex_dir, 0755);
                }
                $created2 = mkdir($image_dir, 0755);
            }
            if (!$start_detects || !$created1 || !$created2) {
                $control_socket = (new ApiSockets($this->writelog, 'emergency-stop'))->socketWork($this->socket_config);
                if (!$control_socket) {
                    return $this->prnJson('error_start', 'error', $response);
                }
                $result = $control_socket->api('stop', false);
                if (@$result['response_status'] != 200) {
                    return $this->prnJson('error_start', 'error', $response);
                }
                $control_socket->close();
                return $this->prnJson('access_denied', 'error', $response);
            }
        }

        $this->system->setDeparture(
            $req_data['startmode'],
            $req_data['camres'],
            $cid,
            $this->complex['cid'],
            $this->user['login'],
            $session
        );
        $this->sessions->set('current_camres_' . $cid, $req_data['camres']);
        $this->sessions->set('current_session_' . $cid, $session);
        return $this->prnJson($result['message'], 'success', $response);
    }

    public function info($request, $response)
    {
        //Helper::servicesZMQfix();
        //$this->sessions->fix();
        $message = [];
        $complex_list = $this->settings->getListComplex(true); // not optimal
        if (!$complex_list) {
            return $this->prnJson('Complex ID not found', 'error', $response);
        }

        $lang_labels = $this->lang->loadLangLabels('errors');
        $error_text = $lang_labels['error_connect'];

        foreach ($complex_list as $cl) {
            $res = $this->loadComplex($cl['id']);
            if (!$res) {
                return $this->prnJson('Wrong complex ID', 'error', $response);
            }

            $tick = $this->hasTick($cl['id']);
            if (!$tick) {
                $message[$cl['id']] = $error_text;
                continue;
            }

            $status_socket = (new ApiSockets($this->writelog, 'info'))->socketWork($this->socket_config);
            if (!$status_socket) {
                $message[$cl['id']] = $error_text;
                continue;
                //return $this->prnJson('error_complex_connect', 'error', $response);
            }
            $result_status = $status_socket->api('status');
            $status_socket->close();
            $complex_status = $this->system->getDeparture($cl['id']);
            $result_status['mode'] = $complex_status['cam_mode'];

            $lang_labels = $this->lang->loadLangLabels('apiresp');
            $result_status = Helper::parseStatusResult($result_status, $lang_labels);
            if ($result_status['status'] == 'error') {
                $message[$cl['id']] = $error_text;
                continue;
                //return $this->prnJson('error_complex_response', 'error', $response);
            }
            $message[$cl['id']] = $result_status['message'];
            //$this->services->startTicks($cl['id']);
        }
        $data = [
            'message' => $message,
            'result' => 'success'
        ];
        return $response->withJson($data);
    }

    /*public function infoBAK($request, $response)
    {
        if ($this->user['role'] !== 'admin') {
            return $this->prnJson('access_denied', 'success', $response);
        }
        $req_data = $request->getParsedBody();
        $cid = Helper::xss($req_data['cid']);
        $complex_status = $this->system->getDeparture($cid);
        / *if (!$complex_status || $complex_status['status'] == 'stop') {
            return $this->prnJson('error_complex_active', 'error', $response);
        }* /

        $res = $this->loadComplex($cid);
        if (!$res) {
            return $this->prnJson('Wrong complex ID', 'error', $response);
        }
        / *$info_socket = $this->socketWork();
        if (!$info_socket) {
            return $this->prnJson('error_complex_connect', 'error', $response);
        }
        $result_info = $info_socket->api('info');
        $info_socket->close();
        $result_info = Helper::parseInfoResult($result_info);
        if ($result_info['status'] == 'error') {
            return $this->prnJson('error_complex_response', 'error', $response);
        }
        $complex_id = $result_info['cid'];* /

        $status_socket = (new ApiSockets())->socketWork($this->socket_config);
        if (!$status_socket) {
            return $this->prnJson('error_complex_connect', 'error', $response);
        }
        $result_status = $status_socket->api('status');
        $status_socket->close();
        $result_status['mode'] = $complex_status['cam_mode'];

        $lang_labels = $this->lang->loadLangLabels('apiresp');
        $result_status = Helper::parseStatusResult($result_status, $lang_labels);
        if ($result_status['status'] == 'error') {
            return $this->prnJson('error_complex_response', 'error', $response);
        }
        $message = $result_status['message'];
        $data = [
            'message' => $message,
            'title' => $this->complex['cid'],
            'result' => 'success'
        ];
        return $response->withJson($data);
    }*/

    public function startServices($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('diag');
        $complex_list = $this->settings->getListComplex(true);
        $complex_ids = array_column($complex_list, 'id');
        $res = $this->services->globalStart($complex_ids);
        if ($res) {
            return $this->prnJson($lang_labels['start_succes'], 'success', $response);
        } else {
            return $this->prnJson($lang_labels['start_error'], 'error', $response);
        }
    }

    public function stopServices($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('diag');
        $complex_list = $this->settings->getListComplex(true);
        $complex_ids = array_column($complex_list, 'id');
        $res = $this->services->globalStop($complex_ids);
        if ($res) {
            return $this->prnJson($lang_labels['stop_succes'], 'success', $response);
        } else {
            return $this->prnJson($lang_labels['stop_error'], 'error', $response);
        }
    }

    private function loadComplex($complex_id)
    {
        $complex = $this->settings->getComplexByID($complex_id);
        if (!$complex) {
            return false;
        }
        $this->socket_config = [
            'host' => $complex['cip'],
            'port' => $complex['cpt'],
            'key' => $this->apikey
        ];
        $this->complex = $complex;
        return true;
    }

    private function hasTick($cid)
    {
        $tick = $this->events->getTick($cid);
        $control_time = time() - $this->tick_timeout; // check if tick by last 5 seconds
        if ($tick['time_stamp'] < $control_time) {
            $this->events->saveAlarms($cid, ['code' => 99, 'name' => 'NO_TICKS', 'message' => '', 'aux_data' => '']);
            return false;
        }
        return $tick;
    }

    /*private function socketWork()
    {
        $socket_config = [
            'host' => $this->complex['cip'],
            'port' => $this->complex['cpt'],
            'key' => $this->apikey
        ];
        $socket = new ApiSockets($socket_config);
        $result = $socket->open();
        if (!$result) {
            $error = $socket->getError();
            $socket->close();
            //var_dump(base64_encode($error)); die;
            //throw new AppException('Complex ' . $complex['cip'] . ' take error: ' . $error, 'error_complex_connect');
            return false;
        }
        return $socket;
    }*/
}

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
use App\Models\Storage;
use App\Models\System;
use App\Models\Events;
use App\Libs\ApiSockets;
use App\Libs\Helper;

class StorageController extends Controller
{
    //private $user;
    private $storage;
    private $settings;
    private $events;
    private $writelog;

    public function init()
    {
        //$this->view = $this->di->get('view');
        //$this->user = $this->sessions->get('user');
        //$this->view->setVar('user', $this->user);
        $this->storage = new Storage($this->db);
        $this->settings = new Settings($this->db);
        $this->events = new Events($this->db);
        $this->view->setVar('active_menu_item', 'sessions');
        $this->writelog = $this->settings->getLoggerStatus();
    }

    public function index($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('sessions');
        $this->view->setVar('title', $lang_labels['sessions_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_sessions_none = $this->lang->loadLangLabels('sessions')['sessions_none'];
        $this->view->setVar('lang_sessions_none', $lang_sessions_none);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);
        $this->view->setJsUrl('actionChangeStorage', $this->site_url . '/sessions/setstorage');
        $this->view->setJsUrl('actionLoadSessions', $this->site_url . '/sessions/load');
        $this->view->setJsUrl('actionDeleteSessions', $this->site_url . '/sessions/delete');

        $complex_list = $this->settings->getListComplex(true);
        $this->view->setVar('clist', $complex_list);
        $this->view->setVar('ccount', count($complex_list));
        $storage_type = $this->sessions->get('storage')?:'local';
        $this->view->setVar('stype', $storage_type);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');
        //$this->view->setCss('cabinet/mainview');
        $this->view->setCss('colors');
        $this->view->setCss('cabinet/sessions');
        $this->view->setJsFile('cabinet/sessions');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/storage');
    }

    public function setStorageType($request, $response)
    {
        $allow_types = ['local', 'complex', 'remote'];
        $req_data = $request->getParsedBody();
        $storage = Helper::xss($req_data['stype']);
        if (!in_array($storage, $allow_types)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $this->sessions->set('storage', $storage);
        return $this->prnJson('change_storage_ok', 'success', $response);
    }

    public function deleteSession($request, $response)
    {
        if ($this->user['role'] !== 'admin') {
            echo 'Access denied';
            exit();
        }
        $allow_types = ['local', 'complex', 'remote'];
        $req_data = $request->getParsedBody();
        $sessions = Helper::xss_array($req_data['sessions']);
        $storage = Helper::xss($req_data['stype']);
        $cid = Helper::xss($req_data['cid']);
        if (!in_array($storage, $allow_types) || !$sessions) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        switch($storage){
            case 'local':
                $res = $this->delLocalSessions($cid, $sessions);
                break;
            case 'complex':
                $res = false; //$this->delComplexSession($session);
                break;
            case 'remote':
                //$res = $this->delRemoteSessions($session);
                $res = false;
                break;
        }
        if (!$res) {
            return $this->prnJson('Delete error', 'error', $response);
        }
        return $this->prnJson('', 'success', $response);
    }

    public function loadSessions($request, $response)
    {
        $complex_list = $this->settings->getListComplex(true);
        $this->view->setVar('clist', $complex_list);

        $storage_type = $this->sessions->get('storage')?:'local';
        switch($storage_type){
            case 'local':
                $sessions = $this->getLocalSessions($complex_list);
                break;
            case 'complex':
                $sessions = $this->getComplexSessions($complex_list);
                break;
            case 'remote':
                $sessions = $this->getRemoteSessions();
                break;
            default:
                $sessions = $this->getLocalSessions($complex_list);
                break;
        }
        $data = [
            'sess' => $sessions,
            'result' => 'success',
            'stype' => $storage_type
        ];
        return $response->withJson($data);
    }

    public function viewSession($request, $response)
    {
        $allowed_types = ['local', 'complex', 'remote'];
        $storage_type = $request->getAttribute('stype');
        $session_id = $request->getAttribute('sid');
        if (!in_array($storage_type, $allowed_types)) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('<h2>Page not found</h2>');
        }
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('sessions');
        $this->view->setVar('title', $lang_labels['sessions_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_sessions_none = $this->lang->loadLangLabels('sessions')['sessions_none'];
        $this->view->setVar('lang_sessions_none', $lang_sessions_none);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);
        /*$this->view->setJsUrl('actionLoadTrack', $this->site_url . '/sessions/gettrack');*/
        $this->view->setJsUrl('actionGetImage', $this->site_url . '/map/getimage');

        /*$this->view->setVar('session_id', $session_id);
        $detects = $this->events->getDetectionsListBySession($session_id);
        $this->view->setVar('detects', $detects);*/
        $bg_image = $this->site_url . '/images/noimageyet.png';
        $this->view->setVar('bg_image', $bg_image);
        /*$complex = $this->storage->getComplexByLocalSession($session_id);
        $this->view->setVar('complex_id', $complex);*/

        $map_url = $this->di->get('configs')['map_url'];
        $this->view->setJsUrl('mapUrl', $map_url);
        $this->view->setCss('/map/leaflet', true);
        $this->view->setJsFile('/map/leaflet', true);
        $this->view->setJsFile('cabinet/mapconf');
        $this->view->setJsFile('cabinet/map');

        $this->view->setCss('jquery.guillotine');
        $this->view->setJsFile('jquery.guillotine');

        //$this->view->setJsFile('jquery.ba-throttle-debounce.min');
        //$this->view->setJsFile('jquery.paver.min');
        //$this->view->setCss('pan/paver.min');

        /*$this->view->setJsFile('owl.carousel.min');
        $this->view->setCss('owl/owl.carousel.min');
        $this->view->setCss('owl/owl.theme.default.min');*/
        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');
        //$this->view->setCss('cabinet/mainview');
        $this->view->setCss('colors');
        $this->view->setCss('cabinet/sessions');
        $this->view->setJsFile('cabinet/sessview');
        $this->view->setLayout('cabinet');
        switch ($storage_type) {
            case 'local': $this->viewLocalSession($session_id); break;
            case 'complex': $this->viewComplexSession($session_id); break;
        }
    }

    private function viewLocalSession($session_id)
    {
        $this->view->setJsUrl('actionLoadTrack', $this->site_url . '/sessions/gettrack');
        //$this->view->setJsUrl('actionGetImage', $this->site_url . '/map/getimage');

        $this->view->setVar('session_id', $session_id);
        $detects = $this->events->getDetectionsListBySession($session_id);
        $this->view->setVar('detects', $detects);
        $this->view->setVar('stype', 'local');

        $complex = $this->storage->getComplexByLocalSession($session_id);
        $this->view->setVar('complex_id', $complex['complex_id']);
        $this->view->setVar('complex_color', $complex['colour']);

        $this->view->setJsFile('owl.carousel.min');
        $this->view->setCss('owl/owl.carousel.min');
        $this->view->setCss('owl/owl.theme.default.min');

        $this->view->render('cabinet/sesslcview');
    }

    private function viewComplexSession($session_id)
    {
        $this->view->setJsUrl('actionLoadTracks', $this->site_url . '/sessions/gettracks');
        //$this->view->setJsUrl('actionLoadTrack', $this->site_url . '/sessions/gettrack');
        //$this->view->setJsUrl('actionGetImage', $this->site_url . '/map/getimage');

        $this->view->setVar('stype', 'complex');
        $this->view->setVar('session_id', $session_id);
        $complex = $this->storage->getComplexByComplexSession($session_id);
        $this->view->setVar('complex_id', $complex['complex_id']);
        $this->view->setVar('complex_color', $complex['colour']);
        /*$tracks = $this->getComplexSession($complex, $session_id);
        if (!$tracks) {
            $tracks = $this->lang->loadLangLabels('sessions')['sessions_empty'] . ' (502)';
        }*/
        //$no_tracks = $this->lang->loadLangLabels('sessions')['sessions_empty'] . ' (502)';
        //$this->view->setVar('notracks', $no_tracks);
        $this->view->render('cabinet/sesscxview');
    }

    public function getComplexSession($request, $response)
    {
        $req_data = $request->getParsedBody();
        $complex_id = Helper::xss($req_data['cid']);
        $session_id = Helper::xss($req_data['sid']);

        $session_data = $this->storage->getSession($session_id);
        if (!$session_data) {
            $errors = $this->lang->loadLangLabels('errors');
            $lang_error_connect = $errors['error_connect'];
            $lang_error_response = $errors['error_response'];
            $lang_error_db = $errors['error_db'];
            $lang_sessions_empty = $this->lang->loadLangLabels('sessions')['sessions_empty'];

            $apikey = $this->di->get('configs')['apikey'];
            $complex = $this->settings->getComplexByID($complex_id);
            $tick = $this->events->getTick($complex_id);
            $time = time();
            if (!$tick || ($time - $tick['time_stamp']) > 3) {
                $res = '<h4 class="ml-3">' . $lang_error_connect . '</h4>';
                return $this->prnJson($res, 'error', $response);
            }
            $socket_config = [
                'host' => $complex['cip'],
                'port' => $complex['cpt'],
                'key' => $apikey
            ];
            $sess_socket = (new ApiSockets($this->writelog, 'session', 10))->socketWork($socket_config);
            if (!$sess_socket) {
                $res = '<h4 class="ml-3">' . $lang_error_connect . ' (2)</h4>';
                return $this->prnJson($res, 'error', $response);
            }
            $result = $sess_socket->api('sessions', false, $session_id);
            $sess_socket->close();
            if (@$result['response_status'] == 200) {
                if (!isset($result['response_body']['detections'])) {
                    $res = '<h4 class="ml-3">' . $lang_error_response . '</h4>';
                    return $this->prnJson($res, 'error', $response);
                }
                $cam_mode = (new System($this->db))->getDepartureBySession($complex_id, $session_id)['cam_mode'];
                $tmp = $result['response_body']['detections'];
                if (!$tmp) {
                    $res = '<h4 class="ml-3">' . $lang_sessions_empty . '</h4>';
                    return $this->prnJson($res, 'error', $response);
                }
                $session_data = [];
                foreach ($tmp as $t) {
                    $data = [];
                    preg_match('/f_(\d+).*/', $t['file'], $matches);
                    $data['time_stamp'] = $matches[1];
                    preg_match('/(.*).txt/', $t['file'], $matches);
                    $data['imgfull'] = $matches[1];
                    //$data['url'] = '/stream.cgi?raw=0&det=25&dir=session/' . $session_id . '&file=' . $matches[1];
                    $data['url'] = Helper::prepareUrl('image_session', ['%det' => 25, '%sid' => $session_id, '%file' => $matches[1]]);
                    $data['detect_objects'] = @json_encode($t['data']);
                    $data['cam_mode'] = $cam_mode;
                    $data['gps_time'] = $t['gps']['datetime'];
                    $data['latitude'] = $t['gps']['latitude'];
                    $data['longitude'] = $t['gps']['longitude'];
                    foreach ($t['data'] as $td) {
                        $data['track_id'] = $td['track_id']??'NULL';
                        $new_id = $this->storage->addSessionData($complex_id, $session_id, $data);
                        if (!$new_id || !is_numeric($new_id)) {
                            $res = '<h4 class="ml-3">' . $lang_error_db . '</h4><br /><small>' . $new_id .'</small>';
                            return $this->prnJson($res, 'error', $response);
                        }
                    }
                    $session_data[] = [
                        'id' => $new_id,
                        'time_stamp' => $data['time_stamp'],
                        'detect_objects' => $data['detect_objects'],
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude']
                    ];
                }
            }
        }
        //$detects_conv = ['smoke' => '0(smoke)', 'fire' => '1(fire)'];
        $detects_count = [];
        $result = '';
        foreach ($session_data as $sd) {
            $time = date('d-m-Y (H:i:s)', $sd['time_stamp']);
            $tmp = @json_decode($sd['detect_objects'], true);
            $obj = null;
            if (!$tmp) {
                $obj = '&mdash;';
            } else {
                $obj = [];
                foreach ($tmp as $t) {
                    preg_match('/\d+\((.*)\)/', $t['label'], $matches);
                    $obj[]= $matches[1];
                    if (!isset($detects_count[$t['label']])) $detects_count[$t['label']]=0;
                    $detects_count[$t['label']]++;
                }
                $obj = implode(', ', $obj);
            }
            $innerHtml = 'data-id="'.$sd['id'].'" data-lat="'.$sd['latitude'].'" data-lon="'.$sd['longitude'].'"';
            $result.= '<tr class="track" ' . $innerHtml . '><td><small>'.$time.'</small></td><td><small>'.$obj.'</small></td></tr>';
        }
        if ($detects_count) {
            $dc = array_flip($detects_count);
            $detects_count = '';
            foreach ($dc as $key=>$val) {
                preg_match('/\d+\((.*)\)/', $val, $matches);
                $detects_count.= '<tr><td><small>'.$matches[1].'</small></td><td><small>'.$key.'</small></td></tr>';
            }
        }
        $data = [
            'message' => $result,
            'tcount' => $detects_count,
            'result' => 'success'
        ];
        return $response->withJson($data);
        //return $this->prnJson($result, 'success', $response);
    }

    public function getTrack($request, $response)
    {
        $allowed_types = ['local', 'complex', 'remote'];
        $req_data = $request->getParsedBody();
        $track_id = Helper::xss($req_data['tid']);
        $storage_type = Helper::xss($req_data['stype']);
        if ((!$track_id && $track_id !=='0') || !in_array($storage_type, $allowed_types)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }

        if ($storage_type == 'local') {
            $track = $this->storage->getLocalTrackById($track_id);
            if (!$track) {
                return $this->prnJson('error_complex_data', 'error', $response);
            }
            $result = '';
            $dto = json_decode($track['detect_objects'], true);
            if (is_array($dto)) {
                foreach ($dto as $d) {
                    foreach ($d as $key=>$val) {
                        if ($key == 'label') {
                            $result.= $key . ': ' . $val;
                            $result.= '<br />';
                        }
                    }
                }
            }
            $data = [
                'track' => $result,
                'dtime' => date('d/m/Y H:i:s', $track['time_stamp']),
                'result' => 'success'
            ];
            return $response->withJson($data);
        }

        if ($storage_type == 'complex') {
            
        }
    }

    /*public function loadSession($request, $response)
    {
        $allowed_types = ['local', 'complex', 'remote'];
        $storage_type = $request->getAttribute('stype');
        $session_id = $request->getAttribute('sid');
        if (!in_array($storage_type, $allowed_types)) {
            return $this->prnJson('', 'error', $response);
        }
        switch($storage_type){
            case 'local':
                $session = $this->getLocalSession($session_id);
                break;
            case 'complex':
                $session = $this->getComplexSession($session_id);
                break;
            case 'remote':
                $session = $this->getRemoteSession();
                break;
        }
        $data = [
            'sess' => $session,
            'result' => 'success',
            'stype' => $storage_type
        ];
        return $response->withJson($data);
    }*/

    /*public function download($request, $response)
    {
        
    }*/

    private function getLocalSessions($complex_list)
    {
        $lang_sessions_none = $this->lang->loadLangLabels('sessions')['sessions_none'];
        $result = [];
        foreach ($complex_list as $cl) {
            $indx = 's' .$cl['id'];
            $res = $this->storage->getLocalSessions($cl['id']);
            if ($res) {
                $result[$indx] = $this->renderSessionsList($res);
            } else {
                $result[$indx] = '<h4 class="ml-3">' . $lang_sessions_none . '</h4>';
            }
        }
        return $result;
    }

    /*private function getLocalSession($session_id)
    {
        
    }*/

    private function getComplexSessions($complex_list)
    {
        $lang_error_connect = $this->lang->loadLangLabels('errors')['error_connect'];
        $lang_sessions_none = $this->lang->loadLangLabels('sessions')['sessions_none'];
        $apikey = $this->di->get('configs')['apikey'];

        $sessions = [];
        foreach ($complex_list as $cl) {
            $complex = $this->settings->getComplexByID($cl['id']);
            $tick = $this->events->getTick($cl['id']);
            $time = time();
            $indx = 's' .$cl['id'];
            if (!$tick || ($time - $tick['time_stamp']) > 3) {
                $old_sess = $this->storage->getSessions($cl['id']);
                if ($old_sess) {
                    $sessions[$indx] = $this->renderSessionsList($old_sess);
                } else {
                    $sessions[$indx] = '<h4 class="ml-3">' . $lang_error_connect . '</h4>';
                }
                continue;
            }
            $socket_config = [
                'host' => $complex['cip'],
                'port' => $complex['cpt'],
                'key' => $apikey
            ];
            $sess_socket = (new ApiSockets($this->writelog, 'sessions'))->socketWork($socket_config);
            if (!$sess_socket) {
                $sessions[$indx] = '<h4 class="ml-3">' . $lang_error_connect . '</h4>';
                continue;
            }
            $result = $sess_socket->api('sessions');
            $sess_socket->close();
            if (@$result['response_status'] == 200) {
                $tmp = $result['response_body'];
                if ($tmp) {
                    $exist_sessions = $this->storage->getSessions($cl['id']);
                    $tmp2 = [];
                    foreach ($exist_sessions as $es) {
                        $tmp2[$es['session_id']] = true;
                    }
                    //$this->storage->resetSessions($cl['id']);
                    foreach ($tmp as $t) {
                        if (!array_key_exists($t['name'], $tmp2)) {
                            $this->storage->addSession($cl['id'], $t);
                        }
                        unset($tmp2[$t['name']]);
                    }
                    foreach ($tmp2 as $key=>$val) {
                        $this->storage->deleteSession($cl['id'], $key);
                    }
                }
                $result = $this->storage->getSessions($cl['id']);
                if (!$result) {
                    $sessions[$indx] = '<h4 class="ml-3">' . $lang_sessions_none . '</h4>';
                } else {
                    $sessions[$indx] = $this->renderSessionsList($result);
                }
            } else {
                $sessions[$indx] = '<h4 class="ml-3">' . $lang_error_connect . '</h4>';
            }
        }
        return $sessions;
    }

    private function getRemoteSessions()
    {
        $lang_storage_none = $this->lang->loadLangLabels('sessions')['storage_not_found'];
        $result = [];
        $complex_list = $this->settings->getListComplex(true);
        foreach ($complex_list as $cl) {
            $indx = 's' .$cl['id'];
            $result[$indx] = '<h4 class="ml-3">' . $lang_storage_none . '</h4>';
        }
        return $result;
    }

    /*private function getRemoteSession($session_id)
    {
        
    }*/

    private function renderSessionsList($sessions)
    {
        foreach ($sessions as $key=>$val) {
            if (@$val['session_start']) {
                $tmp = implode(' (', explode(' ', $val['session_start'])) . ')';
                $sessions[$key]['session_start'] = $tmp;
            }
            if (@$val['session_stop']) {
                $tmp = implode(' (', explode(' ', $val['session_stop'])) . ')';
                $sessions[$key]['session_stop'] = $tmp;
            }
        }
        $lang_labels = $this->lang->loadLangLabels('sessions');
        $this->view->setLangLabes($lang_labels, $this->clc);
        $storage_type = $this->sessions->get('storage')?:'local';
        $this->view->setVar('stype', $storage_type);
        $this->view->setVar('sessions', $sessions);
        return $this->view->render('cabinet/sesslist', true, false);
    }

    private function delLocalSessions($cid, $sessions)
    {
        $no_error = true;
        foreach ($sessions as $s) {
            $res = $this->storage->deleteLocalSession($s);
            if (!$res) {
                $no_error = false;
                continue;
            }
            $complex_dir = BASE_PATH . '/public/detects/' . $cid;
            $image_dir = $complex_dir . '/' . $s;
            $cache_dir = $image_dir . '/cache';
            if (is_dir($cache_dir)) {
                array_map('unlink', glob($cache_dir . '/*.*'));
                rmdir($cache_dir);
            }
            if (is_dir($image_dir)) {
                array_map('unlink', glob($image_dir . '/*.*'));
                rmdir($image_dir);
            }
        }
        return $no_error;
    }
}

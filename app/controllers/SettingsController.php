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
use App\Models\Users;
use App\Models\Events;
use App\Libs\ApiSockets;
use App\Libs\Helper;
use App\Libs\Services;

class SettingsController extends Controller
{
    //private $user;
    private $settings;
    private $writelog;

    public function init()
    {
        //$this->view = $this->di->get('view');
        //$this->user = $this->sessions->get('user');
        //$this->view->setVar('user', $this->user);
        $this->settings = new Settings($this->db);
        if ($this->user['role'] !== 'admin') {
            echo 'Access denied';
            exit();
        }
        $this->view->setVar('active_menu_item', 'settings');
        $this->writelog = $this->settings->getLoggerStatus();
    }

    /*public function saveSessionData($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('sessions');
        $this->view->setVar('title', $lang_labels['sessions_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $this->view->setCss('cabinet/sessions');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/savedata');
    }*/

    public function settings($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('settings');
        $this->view->setVar('title', $lang_labels['settings_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('colors');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);
        $this->view->setJsUrl('actionControl', $this->site_url . '/system/control');
        $this->view->setJsUrl('actionAdd', $this->site_url . '/settings/addcomplex');
        $this->view->setJsUrl('actionEdit', $this->site_url . '/settings/editcomplex');
        $this->view->setJsUrl('actionDel', $this->site_url . '/settings/delcomplex');
        $this->view->setJsUrl('actionLogger', $this->site_url . '/settings/setlogger');
        $this->view->setJsUrl('actionTest', $this->site_url . '/settings/testing');
        $complex_list = $this->settings->getListComplex();
        $this->view->setVar('clist', $complex_list);
        $ip_range = $this->di->get('configs')['ip_range'];
        $this->view->setVar('ip_range', $ip_range);

        $logger_status = $this->settings->getLoggerStatus();
        $this->view->setVar('logger', $logger_status);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        $this->view->setCss('colors');
        $this->view->setJsFile('cabinet/settings');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/settings');
    }

    public function addComplex($request, $response)
    {
        $post_data = $request->getParsedBody();
        $data = Helper::xss_array($post_data);
        if (!filter_var($data['cip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->prnJson('error_ip', 'error', $response);
        }
        // future
        /*$ip_range = $this->di->get('configs')['ip_range'];
        if (Helper::checkIpRange($data['cip'], $ip_range) !== true) {
            return $this->prnJson('error_ip_range', 'error', $response);
        }*/

        /*$chks = $this->checkSerial($data['cip'], $data['cpt'], $data['ckey']);
        switch($chks){
            case 'err.complex.connect': return $this->prnJson('error_complex_connect', 'error', $response);
                break;
            case 'err.complex.response': return $this->prnJson('error_complex_response', 'error', $response);
                break;
            case 'err.wrong.serial': return $this->prnJson('wrong_serial', 'error', $response);
                break;
            default:
                break;
        }*/

        $result = $this->settings->addComplex($data);
        if (!$result) {
            return $this->prnJson('error_complex_add', 'error', $response);
        }
        if ($result === 'err.complex.exist') {
            return $this->prnJson('complex_exist', 'error', $response);
        }
        return $this->prnJson($this->site_url . '/settings', 'redirect', $response);
    }

    public function editComplex($request, $response)
    {
        $post_data = $request->getParsedBody();
        $data = Helper::xss_array($post_data);
        if (!filter_var($data['cip'], FILTER_VALIDATE_IP)) {
            return $this->prnJson('error_ip', 'error', $response);
        }
        // future
        /*$ip_range = $this->di->get('configs')['ip_range'];
        if (Helper::checkIpRange($data['cip'], $ip_range) !== true) {
            return $this->prnJson('error_ip_range', 'error', $response);
        }*/

        /*$chks = $this->checkSerial($data['cip'], $data['cpt'], $data['ckey']);
        if ($chks !== true) {
            switch($chks){
                case 'err.complex.connect': return $this->prnJson('error_complex_connect', 'error', $response);
                    break;
                case 'err.complex.response': return $this->prnJson('error_complex_response', 'error', $response);
                    break;
                case 'err.wrong.serial': return $this->prnJson('wrong_serial', 'error', $response);
                    break;
            }
        }*/

        $cid = $data['id'];
        $status = $this->settings->getComplexByID($cid)['cstatus'];

        $result = $this->settings->editComplex($data);
        if (!$result) {
            return $this->prnJson('error_complex_edit', 'error', $response);
        }
        if ($result === 'err.complex.exist') {
            return $this->prnJson('complex_exist', 'error', $response);
        }
        if ($status == 'off' && isset($data['cstatus'])) {
            //Helper::servicesRestart();
            (new Services(new Events($this->db)))->complexStart($cid);
        }
        if ($status == 'on' && !isset($data['cstatus'])) {
            //Helper::servicesRestart();
            (new Services(new Events($this->db)))->complexStop($cid);
        }
        return $this->prnJson('complex_changed', 'success', $response);
    }

    public function deleteComplex($request, $response)
    {
        $post_data = $request->getParsedBody();
        if (isset($post_data['id']) && is_numeric($post_data['id']) && $post_data['id']) {
            $result = $this->settings->deleteComplex($post_data['id']);
            if (!$result) {
                return $this->prnJson('error_complex_del', 'error', $response);
            }
        }
        return $this->prnJson($this->site_url . '/settings', 'redirect', $response);
    }

    public function modes($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('settings');
        $this->view->setVar('title', $lang_labels['settings_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);
        $this->view->setJsUrl('actionModesSync', $this->site_url . '/settings/modes/sync');
        //$this->view->setJsUrl('actionModesSave', $this->site_url . '/settings/modes/save');
        $complex_list = $this->settings->getListComplex();
        $this->view->setVar('clist', $complex_list);
        /*$complex_modes = $this->settings->getComplexModes();
        $tmp = [];
        if ($complex_modes) foreach ($complex_modes as $cm) {
            if ($cm['modes']) {
                $tmp[$cm['complex_id']] = array_flip($cm['modes']);
            }
        }
        $this->view->setVar('cmodes', $tmp);*/
        $data = $this->settings->getComplexModes();
        $modes = [];
        foreach ($data as $d) {
            $modes[$d['complex_id']] = $d['modes'];
        }
        $this->view->setVar('cmodes', $modes);
        $all_modes = Helper::listModes($this->clc);
        $this->view->setVar('all_modes', $all_modes);
        //var_dump($all_modes); die;

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        $this->view->setCss('colors');
        $this->view->setJsFile('cabinet/settings');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/modes');
    }

    /*public function saveModes($request, $response)
    {
        $req_data = $request->getParsedBody();
        if (!isset($req_data['cid']) || !is_numeric($req_data['cid']) || !$req_data['cid']) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        if (!isset($req_data['mode']) || !$req_data['mode']) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $res = $this->settings->updateComplexModes($req_data['cid'], $req_data['mode']);
        if (!$res) {
            return $this->prnJson('error_complex_edit', 'error', $response);
        }
        return $this->prnJson('userdata_save_ok', 'success', $response);
    }*/

    public function syncModes($request, $response)
    {
        $req_data = $request->getParsedBody();
        $complex_id = Helper::xss($req_data['cid']);
        if (!is_numeric($complex_id) || !$complex_id) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $complex = $this->settings->getComplexByID($complex_id);
        if (!$complex) {
            return $this->prnJson('error_complex_action', 'error', $response);
        }
        $apikey = $this->di->get('configs')['apikey'];
        $socket_config = ['host' => $complex['cip'], 'port' => $complex['cpt'], 'key' => $apikey];
        //$socket = new ApiSockets($socket_config);
        $socket = (new ApiSockets($this->writelog, 'sync-modes'))->socketWork($socket_config);
        if (!$socket) {
            return $this->prnJson('error_complex_connect', 'error', $response);
        }
        //$result = $socket->open();
        if (!$socket) {
            $error = $socket->getError();
            //$socket->close();
            return $this->prnJson($error, 'error', $response);
        }
        $result = $socket->api('info');
        $socket->close();
        if (@$result['response_status'] != 200) {
            return $this->prnJson('error_complex_response', 'error', $response);
        }
        $modes = @$result['response_body']['grabber']['modes'];
        if (!$modes) {
            return $this->prnJson('error_complex_edit', 'error', $response);
        }
        $res = $this->settings->updateComplexModes($complex_id, $modes);
        if (!$res) {
            return $this->prnJson('error_complex_edit', 'error', $response);
        }
        //return $this->prnJson($this->site_url . '/settings/modes', 'redirect', $response);
        $all_modes = Helper::listModes($this->clc);
        $sync_result = '';
        foreach ($all_modes['res'.$complex['camres']] as $am) {
            if (in_array($am['param'], $modes)) {
            //if (in_array($key, $modes)){
                $sync_result.= '<div class="col-6">&bull; &nbsp;' . $am['title'] . '</div>' . PHP_EOL;
            }
        }
        if (!$sync_result) {
            $lang_labels = $this->lang->loadLangLabels('settings');
            $sync_result = '<div class="col-12 text-danger"> &nbsp; ' . $lang_labels['complex_no_modes'] . '!</div>';
        }
        $data = [
            'message' => 'userdata_sync_ok',
            'result' => 'success',
            'modes' => $sync_result, //$modes,
            'cid' => $complex_id
        ];
        return $response->withJson($data);
    }

    public function mailingList($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('settings');
        $this->view->setVar('title', $lang_labels['settings_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        $this->view->setJsUrl('actionAddContact', $this->site_url . '/settings/mailing/add');
        $this->view->setJsUrl('actionDelContact', $this->site_url . '/settings/mailing/del');

        $res = $this->settings->getContacts();
        $this->view->setVar('mlist', $res);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        $this->view->setJsFile('cabinet/settings');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/mailing');
    }

    public function addContact($request, $response)
    {
        $post_data = $request->getParsedBody();
        $ctype = Helper::xss($post_data['ctype']);
        $cdata = Helper::xss($post_data['cdata']);
        $allowed_ctype = ['email', 'phone'];
        if (!in_array($ctype, $allowed_ctype)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        if ($ctype == 'email') {
            $check_mail = filter_var($cdata, FILTER_VALIDATE_EMAIL);
            if (!$check_mail) {
                return $this->prnJson('error_mail', 'error', $response);
            }
        }
        $res = $this->settings->addContact($ctype, $cdata);
        if (!$res) {
            return $this->prnJson('SYSTEM FAILURE (db)', 'error', $response);
        }
        if ($res == 'err.contact.exist') {
            return $this->prnJson('contact_exist', 'error', $response);
        }
        return $this->prnJson($res, 'success', $response);
    }

    public function deleteContact($request, $response)
    {
        $post_data = $request->getParsedBody();
        $id = Helper::xss($post_data['id']);
        if (!$id || !is_numeric($id)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $res = $this->settings->deleteContact($id);
        if (!$res) {
            return $this->prnJson('SYSTEM FAILURE (db)', 'error', $response);
        }
        return $this->prnJson($id, 'success', $response);
    }

    public function users($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('users');
        $this->view->setVar('title', $lang_labels['users_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        $this->view->setJsUrl('actionSetRole', $this->site_url . '/settings/users/setrole');
        $this->view->setJsUrl('actionDelUser', $this->site_url . '/settings/users/delete');

        $users = new Users($this->db);
        $res = $users->getUsersList();
        $this->view->setVar('ulist', $res);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        $this->view->setJsFile('cabinet/settings');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/users');
    }

    public function setUserRole($request, $response)
    {
        $post_data = $request->getParsedBody();
        $id = Helper::xss($post_data['id']);
        $role = Helper::xss($post_data['role']);
        if (!$id || !is_numeric($id)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $allow_roles = ['oper', 'admin', 'disabled'];
        if (!in_array($role, $allow_roles)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $users = new Users($this->db);
        $res = $users->setUserRole($id, $role, $this->user['id']);
        if (!$res) {
            return $this->prnJson('error_db', 'error', $response);
        }
        return $this->prnJson('userdata_save_ok', 'success', $response);
    }

    public function deleteUser($request, $response)
    {
        $post_data = $request->getParsedBody();
        $id = Helper::xss($post_data['id']);
        if (!$id || !is_numeric($id)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $users = new Users($this->db);
        $res = $users->deleteUser($id, $this->user['id']);
        if (!$res) {
            return $this->prnJson('error_user_del', 'error', $response);
        }
        return $this->prnJson('', 'success', $response);
    }

    public function setLogger($request, $response)
    {
        $post_data = $request->getParsedBody();
        $status = Helper::xss($post_data['status']);
        if (!is_numeric($status)) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $status = ($status === '1')?'on':'off';
        $res = $this->settings->setLoggerStatus($status);
        if (!$res) {
            return $this->prnJson('SYSTEM FAILURE (db)', 'error', $response);
        }
        return $this->prnJson('userdata_save_ok', 'success', $response);
    }

    public function testConnect($request, $response)
    {
        $post_data = $request->getParsedBody();
        $cid = Helper::xss($post_data['id']);
        if (!is_numeric($cid) || !$cid) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $complex = $this->settings->getComplexByID($cid);
        if (!$complex) {
            return $this->prnJson('0 error_complex_response', 'error', $response);
        }
        $apikey = $this->di->get('configs')['apikey'];
        $socket_config = ['host' => $complex['cip'], 'port' => $complex['cpt'], 'key' => $apikey];
        $test_socket = (new ApiSockets($this->writelog, 'testconn.' . $cid))->socketWork($socket_config);
        if (!$test_socket) {
            return $this->prnJson('error_complex_connect', 'error', $response);
        }
        $result_test = $test_socket->api('test');
        $test_socket->close();
        if (@$result_test['response_status'] != 200) {
            return $this->prnJson('1 complex_response_error', 'error', $response);
        }
        if (@$result_test['response_body']['result'] != 'ok') {
            return $this->prnJson('2 complex_response_error', 'error', $response);
        }
        return $this->prnJson('complex_response_success', 'success', $response);
    }

    private function checkSerial($cip, $cpt, $ckey)
    {
        $apikey = $this->di->get('configs')['apikey'];
        $socket_config = ['host' => $cip, 'port' => $cpt, 'key' => $apikey];
        $serial_socket = (new ApiSockets($this->writelog, 'serial'))->socketWork($socket_config);
        if (!$serial_socket) {
            return 'err.complex.connect';
        }
        $result_serial = $serial_socket->api('serial');
        $serial_socket->close();
        if ($result_serial['response_status'] != 200) {
            return 'err.complex.response';
        }
        $serial = @$result_serial['response_body']['jetson_id'];
        $serial = trim($serial);
        if ($serial != $ckey) {
            return 'err.wrong.serial';
        }
        return true;
    }
}

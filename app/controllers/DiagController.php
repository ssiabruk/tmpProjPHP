<?php

/*
 *  POSHUK electron-optical complex
 *
 *  @author       Alex Grey
 *  @copyright    Copyright © 2020 Alex Grey (alex@grey.kiev.ua)
 *  @license      https://opensource.org/licenses/GPL-3.0
 *  @since        Version 1.0
 *
 */


namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Settings;
use App\Libs\ApiSockets;
use App\Libs\Helper;

class DiagController extends Controller
{
    private $settings;
    private $writelog;

    public function init()
    {
        //$this->view = $this->di->get('view');
        $this->settings = new Settings($this->db);
        $this->view->setVar('active_menu_item', 'diagnostic');
        $this->writelog = $this->settings->getLoggerStatus();
    }

    public function index($request, $response)
    {
        $lang_labels = $this->lang->loadLangLabels('diag');
        $this->view->setVar('title', $lang_labels['diag_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);

        $this->view->setJsUrl('actionDiag', $this->site_url . '/diagnostic');
        $this->view->setJsUrl('actionStart', $this->site_url . '/system/servstart');
        $this->view->setJsUrl('actionStop', $this->site_url . '/system/servstop');

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');
        $this->view->setJsFile('cabinet/diag');
        $this->view->setCss('colors');

        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/diagpage');
    }

    public function step1($request, $response)
    {
        sleep(1);
        $test_dir = BASE_PATH . '/public/detects/test';
        $test_file = $test_dir . '/test.txt';
        if (is_dir($test_dir)) {
            @unlink($test_file);
            @rmdir($test_dir);
        }
        $folder = @mkdir($test_dir, 0755);
        if (!$folder) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        $file = @fopen($test_file, 'w');
        if (!$file) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        $write = @fwrite($file, 'test');
        if (!$write) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        fclose($file);
        @unlink($test_file);
        @rmdir($test_dir);
        if (is_dir($test_dir)) {
            return $this->prnJson('diag_error', 'error', $response);
        }

        $test_file = BASE_PATH . '/temp/proc/test.txt';
        if (is_file($test_file)) {
            @unlink($test_file);
        }
        $file = @fopen($test_file, 'w');
        if (!$file) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        $write = @fwrite($file, 'test');
        if (!$write) {
            @fclose($file);
            return $this->prnJson('diag_error', 'error', $response);
        }
        @fclose($file);
        @unlink($test_file);
        if (is_file($test_file)) {
            return $this->prnJson('diag_error', 'error', $response);
        }

        // make regular function for check test files?
        $test_file = BASE_PATH . '/temp/logs/test.txt';
        if (is_file($test_file)) {
            @unlink($test_file);
        }
        $file = @fopen($test_file, 'w');
        if (!$file) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        $write = @fwrite($file, 'test');
        if (!$write) {
            @fclose($file);
            return $this->prnJson('diag_error', 'error', $response);
        }
        @fclose($file);
        @unlink($test_file);
        if (is_file($test_file)) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        return $this->prnJson('diag_ok', 'success', $response);
    }

    public function step2($request, $response)
    {
        sleep(1);
        $code = sha1(microtime());
        $res_write = $this->settings->testDBwrite($code);
        if (!$res_write) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        $res_read = $this->settings->testDBread();
        if (!$res_read) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        if ($res_read !== $code) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        $res_clear = $this->settings->testDBclear();
        if (!$res_clear) {
            return $this->prnJson('diag_error', 'error', $response);
        }
        return $this->prnJson('diag_ok', 'success', $response);
    }

    public function step3($request, $response)
    {
        sleep(1);
        $res = false;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return $this->prnJson('Linux only', 'error', $response);
        } else {
            /*$command = '/usr/bin/php ' . BASE_PATH . '/app/cli.php --c test';
            $res = exec($command);*/
            $shell = require BASE_PATH . '/config/shell.php';
            $res = exec($shell['test']);
        }
        if ($res !== 'ok') {
            return $this->prnJson('diag_error', 'error', $response);
        }
        // $res = exec('systemctl status clienttick.service');
        // parse result
        return $this->prnJson('diag_ok', 'success', $response);
    }

    public function step4($request, $response)
    {
        $complex_list = $this->settings->getListComplex(true);
        if (!$complex_list) {
            return $this->prnJson('empty_complex_list', 'error', $response);
        }
        $lang_labels = $this->lang->loadLangLabels('diag');
        $result = [];
        $apikey = $this->di->get('configs')['apikey'];
        $apisocket = new ApiSockets($this->writelog, 'diag');
        foreach ($complex_list as $cl) {
            $result_flag = '<span class="text-success">' . $lang_labels['available'] . '</span>';
            $socket_config = [
                'host' => $cl['cip'],
                'port' => $cl['cpt'],
                'key' => $apikey
            ];
            $health_socket = $apisocket->socketWork($socket_config);
            if ($health_socket) {
                $result_health = $health_socket->api('test');
                $health_socket->close();
                if (!$result_health) {
                    $result_flag = '<span class="text-danger">' . $lang_labels['not_available'] . '</span>';
                } elseif ($result_health['response_status'] != 200) {
                    $result_flag = '<span class="text-danger">' . $lang_labels['complex_error'] . '</span>';
                }
            } else {
                $result_flag = '<span class="text-danger">' . $lang_labels['not_available'] . '</span>';
            }
            $result[] = '<strong class="ml-5 fgc-' . $cl['colour'] . '">' . $cl['cid'] . ' (' . $cl['cip'] . ')</strong> - ' . $result_flag . '<br />';
        }
        $data = [
            'result' => 'success',
            'resdata' => implode('', $result)
        ];
        return $response->withJson($data);
    }
}

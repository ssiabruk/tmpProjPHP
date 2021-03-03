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

use App\Libs\Helper;
use App\Controllers\Controller;
use App\Models\Users;

class AuthController extends Controller
{
    private $users;
    protected $view;
    protected $csrf;

    public function init()
    {
        $this->users = new Users($this->db);
        //$this->view = $this->di->get('view');
        $this->csrf = $this->di->get('csrf');
    }

    public function login()
    {
        $token = $this->csrf->getToken();
        $this->view->setVar('token', $token);
        $lang_labels = $this->lang->loadLangLabels('login');
        $this->view->setVar('title', $lang_labels['login_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        $this->view->setCss('auth/login');
        $this->view->setJsFile('auth/login');
        $this->view->setJsUrl('actionLogin', $this->site_url . '/login');
        $this->view->setLayout('auth');
        $this->view->render('auth/index');
    }

    public function doLogin($request, $response)
    {
        $post_data = $request->getParsedBody();
        $chk_fields = (($post_data['username']??false) && ($post_data['password']??false));
        if (!$chk_fields) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $username = Helper::xss($post_data['username']);
        $password = Helper::xss($post_data['password']);
        $result = $this->users->loginUser($username, $password);
        if (!$result) {
            return $this->prnJson('user_not_found', 'error', $response);
        }
        if ($result == 'err.login') {
            return $this->prnJson('system_failure', 'error', $response);
        }
        $this->csrf->regenerateToken();
        $user = [
            'id' => $result['id'],
            'login' => $username,
            'role' => $result['urole']
        ];
        $this->sessions->set('user', $user);
        $this->sessions->set('lang', $result['uilang']);
        $this->sessions->set('is_logged', true);
        return $this->prnJson($this->site_url . '/', 'redirect', $response);
    }

    public function doLogout($request, $response)
    {
        $this->sessions->clear();
        return $response->withRedirect('/login', 301);
    }

    public function setLang($request, $response)
    {
        $ref = $request->getHeader('HTTP_REFERER')[0];
        if (!$ref) {
            $ref = $this->site_url;
        }
        $lang_code = $request->getAttribute('lang');
        if ($this->lang->hasLang($lang_code)) {
            $this->sessions->set('lang', $lang_code);
        }
        return $response->withRedirect($ref, 301);
    }

    public function restoreAccess()
    {
        $token = $this->csrf->getToken();
        $this->view->setVar('token', $token);
        $lang_labels = $this->lang->loadLangLabels('login');
        $this->view->setVar('title', $lang_labels['login_restore']);
        $this->view->setLangLabes($lang_labels, $this->lang->getCurrentLangCode());

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        $this->view->setCss('auth/restore');
        $this->view->setJsFile('auth/restore');
        $this->view->setJsUrl('actionRes', $this->site_url . '/restore');
        $this->view->setJsUrl('actionReg', $this->site_url . '/register');
        $this->view->setLayout('auth');
        $this->view->render('auth/restore');
    }

    public function doRestoreAccess($request, $response)
    {
        $post_data = $request->getParsedBody();
        $first_char = mb_substr($post_data['username'], 0, 1, 'UTF-8');
        if (is_numeric($first_char)) {
            return $this->prnJson('login_fist_char', 'error', $response);
        }

        sleep(5);
        return $this->prnJson('server_not_available', 'error', $response);
    }

    public function doRegisterUser($request, $response)
    {
        //return false; // for demo
        $post_data = $request->getParsedBody();
        $chk_fields = (($post_data['username']??false) && ($post_data['password']??false));
        if (!$chk_fields) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        $first_char = mb_substr($post_data['username'], 0, 1, 'UTF-8');
        if (is_numeric($first_char)) {
            return $this->prnJson('login_fist_char', 'error', $response);
        }
        if ($post_data['password'] !== $post_data['password2']) {
            return $this->prnJson('passwords_not_match', 'error', $response);
        }
        $username = Helper::xss($post_data['username']);
        $password = Helper::xss($post_data['password']);
        $result = $this->users->registerUser($username, $password); //, 'admin');
        if ($result === 'err.login.exist') {
            return $this->prnJson('login_exist', 'error', $response);
        }
        if (!$result) {
            return $this->prnJson('error_register', 'error', $response);
        }
        return $this->prnJson('user_registered', 'success', $response);
    }
}

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

class ProfileController extends Controller
{
    //private $user;
    private $users;

    public function init()
    {
        //$this->view = $this->di->get('view');
        //$this->user = $this->sessions->get('user');
        $this->users = new Users($this->db);
        $this->view->setVar('active_menu_item', 'profile');
    }

    public function index($request, $response)
    {
        $user_data = $this->users->getUserById($this->user['id']);
        $this->view->setVar('user', $user_data);
        $lang_labels = $this->lang->loadLangLabels('profile');
        $this->view->setVar('title', $lang_labels['profile_title']);
        $this->view->setLangLabes($lang_labels, $this->clc);
        $lang_labels = $this->lang->loadLangLabels('menu');
        $this->view->setLangLabes($lang_labels, $this->clc);

        $token = $this->di->get('csrf')->getToken();
        $this->view->setVar('token', $token);
        $this->view->setJsUrl('actionSave', $this->site_url . '/profile/save');
        $this->view->setJsUrl('actionLang', $this->site_url . '/profile/lang');
        $this->view->setJsUrl('actionPasswd', $this->site_url . '/profile/password');
        $this->view->setJsUrl('actionUsersync', $this->site_url . '/profile/synchronize');

        $this->view->setCss('toast.min');
        $this->view->setJsFile('toast.min');
        $this->view->setCss('forms');
        $this->view->setJsFile('forms');

        $this->view->setCss('cabinet/profile');
        $this->view->setJsFile('cabinet/profile');
        $this->view->setLayout('cabinet');
        $this->view->render('cabinet/profile');
    }

    public function save($request, $response)
    {
        $post_data = $request->getParsedBody();
        $data = Helper::xss_array($post_data);
        $res = $this->users->updateUserData($this->user['id'], $data);
        return $this->prnJson($this->site_url . '/profile', 'redirect', $response);
    }

    public function lang($request, $response)
    {
        $post_data = $request->getParsedBody();
        $res = $this->users->setUILang($this->user['id'], $post_data['lang']);
        if ($res) {
            $this->sessions->set('lang', $post_data['lang']);
        }
        return $this->prnJson($this->site_url . '/profile', 'redirect', $response);
    }

    public function password($request, $response)
    {
        $post_data = $request->getParsedBody();
        $chk_fields = (($post_data['password']??false) && ($post_data['password2']??false) && ($post_data['oldpassw']??false));
        if (!$chk_fields) {
            return $this->prnJson('fields_required_empty', 'error', $response);
        }
        if ($post_data['password'] !== $post_data['password2']) {
            return $this->prnJson('passwords_not_match', 'error', $response);
        }
        $oldpassword = Helper::xss($post_data['oldpassw']);
        $newpassword = Helper::xss($post_data['password']);
        $result = $this->users->changePassword($this->user['login'], $oldpassword, $newpassword);
        if (!$result) {
            return $this->prnJson('error_chpassw', 'error', $response);
        }
        return $this->prnJson('password_changed', 'success', $response);
    }

    public function synchronize($request, $response)
    {
        sleep(5);
        return $this->prnJson('server_not_available', 'error', $response);
        //return $this->prnJson('userdata_sync_ok', 'success', $response);
    }
}

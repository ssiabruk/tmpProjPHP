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


namespace App\Models;

class Users
{
    private $db;
    private $roles;

    public function __construct($db)
    {
        $this->db = $db;
        //$this->roles = ['oper', 'admin'];
    }

    /*public function getAllUsers()
    {
        $st = $this->db->prepare('SELECT * FROM auth');
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }*/

    public function getUserById(int $user_id)
    {
        $st = $this->db->prepare('SELECT *, urole AS role FROM userlist WHERE id = ?');
        try {
            $st->execute([$user_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        $res = $st->fetch(\PDO::FETCH_ASSOC);
        unset($res['upassword']);
        return $res;
    }

    public function loginUser($username, $password)
    {
        if (!$username || !$password) {
            return false;
        }

        $st = $this->db->prepare('SELECT id, urole, uilang FROM userlist WHERE ulogin = ? AND upassword = ?');
        $password = sha1($username.$password);
        try {
            $st->execute([$username, $password]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return 'err.login';
        }
        $res = $st->fetch(\PDO::FETCH_ASSOC);
        if ($res['urole'] == 'disabled') {
            return false;
        }
        return $res;
    }

    public function registerUser($username, $password, $role = 'oper')
    {
        /*if (!$username || !$password) {
            return false;
        }*/

        $st = $this->db->prepare('INSERT INTO userlist (ulogin, upassword, urole) VALUES (?, ?, ?)');
        $password = sha1($username.$password);
        try {
            $st->execute([$username, $password, $role]);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            $code = $e->getCode();
            if ($code == 23505) {
                return 'err.login.exist';
            }
            return false;
        }
        return $this->db->lastInsertId('userlist_id_seq');
    }

    public function setUILang($user_id, $lang_code)
    {
        $exists_langs = ['uk', 'en', 'ru'];
        if (!in_array($lang_code, $exists_langs)) {
            return false;
        }
        $st = $this->db->prepare('UPDATE userlist SET uilang = ? WHERE id = ?');
        try {
            $st->execute([$lang_code, $user_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return true;
    }

    public function changePassword($username, $oldpassword, $newpassword)
    {
        $oldpassword = sha1($username.$oldpassword);
        $newpassword = sha1($username.$newpassword);
        $st = $this->db->prepare('UPDATE userlist SET upassword = ? WHERE ulogin = ? AND upassword = ?');
        try {
            $st->execute([$newpassword, $username, $oldpassword]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return $st->rowCount();
    }

    public function updateUserData($user_id, $data)
    {
        $fname = $data['fname']?:NULL;
        $phone = $data['phone']?:NULL;
        $email = $data['email']?:NULL;
        $dept = $data['dept']?:NULL;
        $squad = $data['squad']?:NULL;
        $st = $this->db->prepare('UPDATE userlist SET fname = ?, phone = ?, email = ?, dept = ?, squad = ? WHERE id = ?');
        try {
            $st->execute([$fname, $phone, $email, $dept, $squad, $user_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return $st->rowCount();
    }

    public function getUsersList($except_user = false)
    {
        $sql = 'SELECT id, ulogin, urole, fname, phone, email FROM userlist';
        if ($except_user) {
            $sql.= ' WHERE id <> ' . $except_user;
        }
        $sql.= ' ORDER BY id';
        $st = $this->db->prepare($sql);
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function setUserRole($user_id, $role, $except_user)
    {
        if ($user_id == $except_user) {
            return false;
        }
        $sql = 'UPDATE userlist SET urole = ? WHERE id = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$role, $user_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return true;
    }

    public function deleteUser($user_id, $except_user)
    {
        if ($user_id == $except_user) {
            return false;
        }
        $sql = 'DELETE FROM userlist WHERE id = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$user_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return true;
    }
}

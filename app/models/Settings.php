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

class Settings
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getListComplex($on_only = false)
    {
        $data = [];
        $sql = 'SELECT * FROM complexlist';
        if ($on_only) {
            $data = ['on'];
            $sql .= ' WHERE cstatus = ?';
        }
        $sql.= ' ORDER BY id';
        $st = $this->db->prepare($sql);
        try {
            $st->execute($data);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getComplexByID($cid)
    {
        $st = $this->db->prepare('SELECT * FROM complexlist WHERE id = ?');
        try {
            $st->execute([$cid]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    public function getComplexModes()
    {
        $sql = 'SELECT * FROM complexmodes';
        $st = $this->db->prepare($sql);
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        $res = $st->fetchAll(\PDO::FETCH_ASSOC);
        if (!$res) {
            return false;
        }
        array_walk($res, function (&$item, $key) {
            $item['modes'] = unserialize($item['modes']);
        });
        return $res;
    }

    public function getComplexModesById($cid)
    {
        $sql = 'SELECT * FROM complexmodes WHERE complex_id = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        $res = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$res) {
            return false;
        }
        $res['modes'] = unserialize($res['modes']);
        return $res;
    }

    public function updateComplexModes($id, $modes)
    {
        $st = $this->db->prepare('DELETE FROM complexmodes WHERE complex_id = ?');
        $st->execute([$id]);

        try {
            $modes = serialize($modes);
            $st = $this->db->prepare('INSERT INTO complexmodes (complex_id, modes) VALUES (?, ?)');
            $st->execute([$id, $modes]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return true;
    }

    public function addComplex($data)
    {
        $cid = $data['cid'];
        $cip = $data['cip'];
        $cpt = $data['cpt'];
        $ckey = $data['ckey'];
        $camres = $data['camres'];
        $colour = $data['colour']??'red';

        if (!$cid || !$cip || !$cpt || !$ckey || !$camres) {
            return false;
        }

        $sql = 'INSERT INTO complexlist (cid, cip, cpt, ckey, colour, camres, cstatus) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid, $cip, $cpt, $ckey, $colour, $camres, 'on']);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            $code = $e->getCode();
            if ($code == 23505) {
                return 'err.complex.exist';
            }
            return false;
        }
        return true;
    }

    public function editComplex($data)
    {
        $cid = $data['cid'];
        $cip = $data['cip'];
        $cpt = $data['cpt'];
        $ckey = $data['ckey'];
        $camres = $data['camres'];
        $id = $data['id'];
        $colour = $data['colour']??'red';
        $cstatus = isset($data['cstatus'])?'on':'off';

        if (!$cid || !$cip || !$cpt || !$ckey || !$camres || !$id) {
            return false;
        }

        $sql = 'UPDATE complexlist SET cid = ?, cip = ?, cpt = ?, ckey = ?, colour = ?, cstatus = ?, camres = ? WHERE id = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid, $cip, $cpt, $ckey, $colour, $cstatus, $camres, $id]);
        } catch(\PDOException $e) {
            $code = $e->getCode();
            if ($code == 23000 || $code == 23505) {
                return 'err.complex.exist';
            }
            return false;
        }
        return true;
    }

    public function deleteComplex($id)
    {
        $st = $this->db->prepare('DELETE FROM complexlist WHERE id = ?');
        try {
            $st->execute([$id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return true;
    }

    public function getContacts($ctype = 'all')
    {
        $sql = '';
        switch($ctype){
            case 'all':
                $sql = 'SELECT id, contact FROM contactslist';
                break;
            case 'emails':
                $sql = 'SELECT id, contact FROM contactslist WHERE ctype = \'email\'';
                break;
            case 'phones':
                $sql = 'SELECT id, contact FROM contactslist WHERE ctype = \'phone\'';
                break;
            default:
                return false;
                break;
        }
        $st = $this->db->prepare($sql);
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addContact($ctype, $cdata)
    {
        $sql = 'INSERT INTO contactslist (ctype, contact) VALUES (?, ?)';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$ctype, $cdata]);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            $code = $e->getCode();
            if ($code == 23505) {
                return 'err.contact.exist';
            }
            return false;
        }
        return $this->db->lastInsertId('contactslist_id_seq');
    }

    public function deleteContact($contact_id)
    {
        $st = $this->db->prepare('DELETE FROM contactslist WHERE id = ?');
        try {
            $st->execute([$contact_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
            return false;
        }
        return true;
    }

    public function testDBclear()
    {
        $st = $this->db->prepare('UPDATE attrs SET diagnostic_temp = NULL');
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return false;
        }
        return $st->rowCount();
    }

    public function testDBread()
    {
        $st = $this->db->prepare('SELECT diagnostic_temp FROM attrs');
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return false;
        }
        return $st->fetch(\PDO::FETCH_ASSOC)['diagnostic_temp'];
    }

    public function testDBwrite($code)
    {
        $st = $this->db->prepare('UPDATE attrs SET diagnostic_temp = ?');
        try {
            $st->execute([$code]);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return false;
        }
        return true;
    }

    public function getNotifyBlockingTime()
    {
        $st = $this->db->prepare('SELECT block_notify_from FROM attrs');
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return 0;
        }
        $tmp = $st->fetch(\PDO::FETCH_ASSOC)['block_notify_from'];
        return $tmp?:0;
    }

    public function setNotifyBlockingTime($time)
    {
        $sql = 'UPDATE attrs SET block_notify_from = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$time]);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
        }
    }

    public function getNotifyLang() // dirty hack
    {
        $st = $this->db->prepare('SELECT data FROM sessions ORDER BY last_updated DESC LIMIT 1 OFFSET 0');
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return 'en';
        }
        $tmp = $st->fetch(\PDO::FETCH_ASSOC);
        $ar = explode(';', $tmp['data']);
        $tmp = explode(':', $ar[0]);
        $res = str_replace('"', '', $tmp[2]);
        if (!in_array($res, ['uk', 'ru', 'en'])) {
            return 'en';
        }
        return $res;
    }

    public function setLoggerStatus($status)
    {
        $sql = 'UPDATE attrs SET use_logger = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$status]);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return false;
        }
        return true;
    }

    public function getLoggerStatus()
    {
        $st = $this->db->prepare('SELECT use_logger FROM attrs');
        try {
            $st->execute();
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return 'off';
        }
        return $st->fetch(\PDO::FETCH_ASSOC)['use_logger'];
    }
}

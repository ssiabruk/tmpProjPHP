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

class System
{
    private $db;
    private $cs_start = 'recdet';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getDeparture($cid, $flying_only = false)
    {
        $sql = 'SELECT * FROM departures WHERE complex_id = ? AND (status = ? OR status = ?)
                ORDER BY id DESC LIMIT 1';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid, 'record', 'recdet']);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        $res = $st->fetch(\PDO::FETCH_ASSOC);
        if (!$flying_only) {
            return $res;
        }

        $sql = 'SELECT id, status FROM departures WHERE complex_id = ? AND action_timestamp > ?
                ORDER BY id DESC LIMIT 1';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid, $res['action_timestamp']]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        if (!$st->rowCount()) {
            return $res;
        }
        return false;
    }

    public function getDepartureBySession($cid, $session)
    {
        $sql = 'SELECT * FROM departures WHERE complex_id = ? AND session_id = ? AND status = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$cid, $session, $this->cs_start]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    public function setDeparture($action, $mode, $cid, $cname, $uid, $session)
    {
        $time = time();
        $sql = 'INSERT INTO departures (status, cam_mode, complex_id, complex_name, action_user, action_timestamp, session_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$action, $mode, $cid, $cname, $uid, $time, $session]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
    }

    public function getDeparturedList()
    {
        $st = $this->db->prepare('SELECT * FROM departures WHERE status = ?');
        try {
            $st->execute([$this->cs_start]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        $res = $st->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($res as $r) {
            if (!$r['session_id']) continue;
            $result[$r['complex_id']] = $r; // there can be only last session by each complexes
            unset($result[$r['complex_id']]['id']);
            unset($result[$r['complex_id']]['complex_id']);
        }
        return $result;
    }
}

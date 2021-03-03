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


namespace App\Models;

class Storage
{
    private $db;
    private $cs_active = ['stop', 'recdet']; // complexes flying statuses
    private $cs_stop = 'stop';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function resetSessions($complex_id)
    {
        $st = $this->db->prepare('DELETE FROM sessionlist WHERE complex_id = ?');
        try {
            $st->execute([$complex_id]);
        } catch(\PDOException $e) {
            //return $e->getMessage();
        }
    }

    public function addSession($complex_id, $data)
    {
        $time = $data['start']?strtotime($data['start']):NULL;
        $detected = $data['detected']?'detect':'record';
        $sql = 'INSERT INTO sessionlist (complex_id, session_id, session_type, session_start, session_stop, session_timestamp)
                VALUES (?, ?, ?, ?, ?, ?)';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$complex_id, $data['name'], $detected, $data['start'], $data['stop'], $time]);
        } catch(\PDOException $e) {
            //return $e->getMessage();
        }
    }

    public function getSessions($complex_id)
    {
        $st = $this->db->prepare('SELECT * FROM sessionlist WHERE complex_id = ? ORDER BY session_type, session_timestamp DESC');
        try {
            $st->execute([$complex_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage(); die;
            return false;
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSession($session_id)
    {
        $st = $this->db->prepare('SELECT id, time_stamp, detect_objects, latitude, longitude FROM sessiondata WHERE session_id = ? ORDER BY time_stamp DESC');
        try {
            $st->execute([$session_id]);
        } catch(\PDOException $e) {
            //return $e->getMessage();
        }
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addSessionData($complex_id, $session_id, $session_data)
    {
        $sql = 'INSERT INTO sessiondata
                (cid, time_stamp, url, detect_objects, gps_time, latitude, longitude, cam_mode, imgfull, track_id, session_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT DO NOTHING';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([
                $complex_id,
                $session_data['time_stamp'],
                $session_data['url'],
                $session_data['detect_objects'],
                $session_data['gps_time'],
                $session_data['latitude'],
                $session_data['longitude'],
                $session_data['cam_mode'],
                $session_data['imgfull'],
                $session_data['track_id'],
                $session_id
            ]);
        } catch(\PDOException $e) {
            return $e->getMessage(); die;
        }
        return $this->db->lastInsertId('sessiondata_id_seq');
    }

    public function deleteSession($complex_id, $session_id)
    {
        $st = $this->db->prepare('DELETE FROM sessionlist WHERE complex_id = ? AND session_id = ?');
        try {
            $st->execute([$complex_id, $session_id]);
        } catch(\PDOException $e) {
            //return $e->getMessage();
        }
    }

    public function getLocalSessions($complex_id)
    {
        $fly_statuses = "'" . implode("', '", $this->cs_active) . "'";
        $sql = 'SELECT * FROM departures WHERE complex_id = ? AND status IN (' . $fly_statuses . ') ORDER BY action_timestamp ASC';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$complex_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        $res = $st->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        //var_dump($res); die;
        $tmp = [];
        foreach ($res as $r) {
            if (!$r['session_id']) continue;
            $result[$r['session_id']]['session_id'] = $r['session_id'];
            $result[$r['session_id']]['session_type'] = null;
            $result[$r['session_id']]['complex_id'] = $complex_id;
            if ($r['status'] == $this->cs_stop) {
                $result[$r['session_id']]['session_stop'] = is_numeric($r['action_timestamp'])?date('d/m/Y H:i:s', $r['action_timestamp']):NULL;
                if (!in_array($r['session_id'], $tmp)) {
                    $result[$r['session_id']] = null;
                    unset($result[$r['session_id']]);
                }
            } else {
                $result[$r['session_id']]['session_start'] = is_numeric($r['action_timestamp'])?date('d/m/Y H:i:s', $r['action_timestamp']):NULL;
                $tmp[] = $r['session_id'];
            }
        }
        $result = array_reverse($result);
        //var_dump($result); die;
        return $result;
    }

    public function getComplexByLocalSession($session)
    {
        $sql = 'SELECT complexlist.*, complex_id FROM departures
                LEFT JOIN complexlist ON departures.complex_id = complexlist.id
                WHERE session_id = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$session]);
        } catch(\PDOException $e) {
            //return $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    public function getComplexByComplexSession($session)
    {
        $sql = 'SELECT complexlist.*, complex_id FROM sessionlist
                LEFT JOIN complexlist ON sessionlist.complex_id = complexlist.id
                WHERE session_id = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$session]);
        } catch(\PDOException $e) {
            //return $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    public function deleteLocalSession($session)
    {
        $this->db->beginTransaction();
        $st = $this->db->prepare('DELETE FROM departures WHERE session_id = ?');
        try {
            $st->execute([$session]);
        } catch(\PDOException $e) {
            //return $e->getMessage();
            $this->db->rollback();
            return false;
        }
        $st = $this->db->prepare('DELETE FROM detections WHERE session_id = ?');
        try {
            $st->execute([$session]);
        } catch(\PDOException $e) {
            $this->db->rollback();
            return false;
            //echo $e->getMessage();
        }
        $this->db->commit();
        return true;
    }

    public function getLocalTrackById($track_id)
    {
        $sql = 'SELECT detect_objects, time_stamp, id AS dtid FROM detections WHERE track_id = ? LIMIT 1 OFFSET 0';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$track_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    public function getComplexTrackById($track_id)
    {
        $sql = 'SELECT url, imgfull, cam_mode, session_id, cid FROM sessiondata WHERE id = ?';
        $st = $this->db->prepare($sql);
        try {
            $st->execute([$track_id]);
        } catch(\PDOException $e) {
            //echo $e->getMessage();
        }
        return $st->fetch(\PDO::FETCH_ASSOC);
    }
}

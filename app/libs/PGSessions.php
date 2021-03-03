<?php

/*
 *  POSHUK electron-optical complex
 *
 *  Fork PGSessions class
 *  @author Kevin Shah
 *  @link https://github.com/iKevinShah/PGSessions
 *  Class to save PHP Sessions in Postgres database
 *
 */


namespace App\Libs;

class PGSessions implements \SessionHandlerInterface
{
    private $db = NULL;
    private $dur = 86400;

    public function __construct(\PDO $database)
    {
        //$this->db = NULL;
        if(($database instanceof \PDO))
        {
            $this->db = $database;
        }
        else
        {
            die('Passed database instance is NOT of the type required. Exiting');
        }
        /*if(!defined('SESSION_DURATION'))
        {
            define('SESSION_DURATION',86400);
            //Make Sessions valid for 1 day be default
        }*/
    }

    public function setDuration($dur)
    {
        $this->dur = $dur;
    }

    public function open($savePath=NULL, $sessionName=NULL)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($session_id)
    {
        try
        {
            $statement = $this->db->prepare('SELECT "data" FROM "sessions" WHERE "sessions"."id"=:session_id');
            $statement->bindParam(':session_id', $session_id, \PDO::PARAM_STR);
            if ($statement->execute() && 1 === $statement->rowCount())
            {
                $row = $statement->fetch();
                return $row['data'];
            }
        }
        catch(\PDOException $error)
        {
            /*error_log($error);
            //Show a nice decent Database Connection Error page
            //without the details of database host, username and the password
            exit;*/
            return false;
        }
        return '';
    }

    public function write($session_id, $data)
    {
        try
        {
            $current_time = time();
            $expiry_time = $current_time + $this->dur; //SESSION_DURATION;

            $sql = 'INSERT INTO sessions
                    ("id","last_updated","expiry","data") VALUES (:id,:last_updated,:expiry,:data)
                    ON CONFLICT(id) DO UPDATE SET "data"=:data,"last_updated"=:last_updated,"expiry"=:expiry
                    WHERE "sessions"."id"=:id;';
            $statement = $this->db->prepare($sql);

            $statement->bindParam(':data',$data,\PDO::PARAM_STR);
            $statement->bindParam(':last_updated',$current_time,\PDO::PARAM_INT);
            $statement->bindParam(':expiry',$expiry_time,\PDO::PARAM_INT);
            $statement->bindParam(':id',$session_id,\PDO::PARAM_STR);

            if($statement->execute())
            {
                return true;
            }
        }
        catch(\PDOException $error)
        {
            /*error_log($error);
            //Show a nice decent Database Connection Error page
            //without the details of database host, username and the password
            exit;*/
            return false;
        }
        return false;
    }

    public function destroy($session_id)
    {
        try
        {
            $statement = $this->db->prepare('DELETE from "sessions" WHERE  "sessions"."id" = :session_id');
            $statement->bindParam(':session_id',$session_id,\PDO::PARAM_STR);
            if($statement->execute())
            {
                return true;
            }
        }
        catch(\PDOException $error)
        {
            /*error_log($error);
            //Show a nice decent Database Connection Error page
            //without the details of database host, username and the password
            exit;*/
            return false;
        }
        return false;
    }

    public function gc($maxlifetime)
    {
        if (!$maxlifetime) {
            $maxlifetime = $this->dur;
        }
        try
        {
            $current_time = time();
            $statement = $this->db->prepare('DELETE from "sessions" WHERE  "sessions"."expiry" < :current_time');
            $statement->bindParam(':current_time',$current_time,\PDO::PARAM_INT);
            if($statement->execute())
            {
                return true;
            }
        }
        catch(\PDOException $error)
        {
            /*error_log($error);
            //Show a nice decent Database Connection Error page
            //without the details of database host, username and the password
            exit;*/
            return false;
        }
        return false;
    }
}

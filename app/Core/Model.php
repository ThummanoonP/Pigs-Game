<?php
namespace Core;
use mysqli;

class Model {
    protected static ?mysqli $db = null;

    public function __construct(){
        if (!self::$db){
            $cfg = require __DIR__ . '/../Config/config.php';
            self::$db = new mysqli($cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'], $cfg['db']['name']);
            if (self::$db->connect_error){
                die('DB Connection failed: ' . self::$db->connect_error);
            }
            self::$db->set_charset('utf8mb4');
        }
    }

    protected function db(): mysqli { return self::$db; }

    protected function query(string $sql, array $params = []){
        $stmt = self::$db->prepare($sql);
        if ($stmt === false){
            throw new \Exception('Prepare failed: ' . self::$db->error);
        }
        if ($params){
       
            $types = '';
            $vals = [];
            foreach ($params as $p){
                if (is_int($p)) $types.='i';
                elseif (is_double($p)) $types.='d';
                else $types.='s';
                $vals[] = $p;
            }
            $stmt->bind_param($types, ...$vals);
        }
        if (!$stmt->execute()){
            throw new \Exception('Execute failed: ' . $stmt->error);
        }
        return $stmt;
    }
}

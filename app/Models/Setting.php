<?php
namespace App\Models;
use Core\Model;
class Setting extends Model {
    public function get(string $key, $default=null){
        $stmt = $this->query('SELECT val FROM settings WHERE `key`=? LIMIT 1', [$key]);
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['val'] : $default;
    }
}

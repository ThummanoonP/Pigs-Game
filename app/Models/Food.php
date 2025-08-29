<?php
namespace App\Models;
use Core\Model;
class Food extends Model {
    public function list($playerId){
        $stmt = $this->query('SELECT * FROM foods WHERE player_id=? ORDER BY id', [$playerId]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function consume($playerId, $foodId){
        $this->query('UPDATE foods SET qty=qty-1 WHERE id=? AND player_id=? AND qty>0', [$foodId,$playerId]);
        if ($this->db()->affected_rows <= 0){
            return false;
        }
        return true;
    }
    public function addQty($playerId, $foodId, $qty){
        $this->query('UPDATE foods SET qty=qty+? WHERE id=? AND player_id=?', [$qty, $foodId, $playerId]);
        return $this->db()->affected_rows>0;
    }
    public function createIfNone($playerId, $name, $qty){
        $stmt = $this->query('SELECT id FROM foods WHERE player_id=? AND name=? LIMIT 1', [$playerId,$name]);
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) return $row['id'];
        $this->query('INSERT INTO foods(player_id,name,qty) VALUES (?,?,?)', [$playerId,$name,$qty]);
        return $this->db()->insert_id;
    }
    public function find($playerId, $foodId){
        $stmt = $this->query('SELECT * FROM foods WHERE id=? AND player_id=? LIMIT 1', [$foodId,$playerId]);
        return $stmt->get_result()->fetch_assoc();
    }
}

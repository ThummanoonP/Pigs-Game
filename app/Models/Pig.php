<?php
namespace App\Models;
use Core\Model;

class Pig extends Model {
    public function allByPlayer($playerId){
        $stmt = $this->query('SELECT * FROM pigs WHERE player_id=? ORDER BY id', [$playerId]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function findByIdAndPlayer($id, $playerId){
        $stmt = $this->query('SELECT * FROM pigs WHERE id=? AND player_id=? LIMIT 1', [$id, $playerId]);
        return $stmt->get_result()->fetch_assoc();
    }
    public function updateFed($pigId){
        $this->query('UPDATE pigs SET last_fed_at=NOW(), exp=exp+5 WHERE id=?', [$pigId]);
    }
}

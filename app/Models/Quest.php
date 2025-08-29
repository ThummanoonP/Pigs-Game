<?php
namespace App\Models;
use Core\Model;

class Quest extends Model {
    public function today(){
        $date = date('Y-m-d');
        $stmt = $this->query('SELECT * FROM daily_quests WHERE active_date IS NULL OR active_date = ?', [$date]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function progressForUser($playerId){
        $today = date('Y-m-d');
        $stmt = $this->query('SELECT * FROM quest_progress WHERE player_id=? AND for_date=?', [$playerId, $today]);
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $byQuest = [];
        foreach($rows as $r){ $byQuest[$r['quest_id']] = $r; }
        return $byQuest;
    }
    public function ensureRows($playerId){
        $today = date('Y-m-d');
        $quests = $this->today();
        foreach ($quests as $q){
            $this->query('INSERT IGNORE INTO quest_progress(player_id, quest_id, for_date, progress, completed, claimed)
                          VALUES (?,?,?,?,0,0)', [$playerId, $q['id'], $today, 0]);
        }
    }
    public function addProgress($playerId, $count=1){
        $today = date('Y-m-d');
        $this->query('UPDATE quest_progress qp
                      JOIN daily_quests q ON q.id=qp.quest_id
                      SET qp.progress = LEAST(qp.progress + ?, q.target), qp.completed = (qp.progress + ? >= q.target)
                      WHERE qp.player_id=? AND qp.for_date=?', [$count,$count,$playerId,$today]);
    }
    public function claim($playerId, $questId){
        $today = date('Y-m-d');
        $stmt = $this->query('SELECT qp.*, q.reward_coin FROM quest_progress qp JOIN daily_quests q ON q.id=qp.quest_id
                              WHERE qp.player_id=? AND qp.quest_id=? AND qp.for_date=? LIMIT 1', [$playerId,$questId,$today]);
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) return ['ok'=>false, 'error'=>'Not found'];
        if ((int)$row['claimed'] === 1) return ['ok'=>false, 'error'=>'Already claimed'];
        if ((int)$row['completed'] !== 1) return ['ok'=>false, 'error'=>'Not completed'];
        
        $this->query('UPDATE quest_progress SET claimed=1 WHERE id=?', [$row['id']]);

        $this->query('UPDATE players SET coin = coin + ? WHERE id=?', [(int)$row['reward_coin'], $playerId]);
        return ['ok'=>true, 'reward_coin'=>(int)$row['reward_coin']];
    }
}

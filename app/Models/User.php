<?php
namespace App\Models;
use Core\Model;

class User extends Model {
    public function create($email, $username, $password){
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->query('INSERT INTO users(email,username,password_hash) VALUES (?,?,?)', [$email,$username,$hash]);
        return $this->db()->insert_id;
    }
    public function findByEmail($email){
        $stmt = $this->query('SELECT * FROM users WHERE email=? LIMIT 1', [$email]);
        return $stmt->get_result()->fetch_assoc();
    }
    public function ensurePlayerInit($userId){
        $stmt = $this->query('SELECT id FROM players WHERE user_id=? LIMIT 1', [$userId]);
        $player = $stmt->get_result()->fetch_assoc();
        if ($player) return $player['id'];

        $this->query('INSERT INTO players(user_id, level, exp, coin) VALUES (?,1,0,0)', [$userId]);
        $playerId = $this->db()->insert_id;

        for ($i=1;$i<=10;$i++){
            $this->query('INSERT INTO pigs(player_id, name, level, exp, last_fed_at) VALUES (?,?,?,?,NULL)',
                [$playerId, 'Pig #'.$i, 1, 0]);
        }

        $this->query('INSERT INTO foods(player_id, name, qty) VALUES (?,?,?)', [$playerId,'Basic Feed', 50]);
        return $playerId;
    }
    public function getProfile($userId){
        $stmt = $this->query('SELECT p.id as player_id, p.level, p.exp, p.coin, u.email, u.username, u.is_admin, u.is_banned
                              FROM players p JOIN users u ON u.id=p.user_id WHERE p.user_id=? LIMIT 1', [$userId]);
        return $stmt->get_result()->fetch_assoc();
    }
    public function setBan($userId, $ban){
        $this->query('UPDATE users SET is_banned=? WHERE id=?', [$ban?1:0, $userId]);
    }
}

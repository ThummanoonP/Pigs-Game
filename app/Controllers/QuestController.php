<?php
namespace App\Controllers;
use Core\Controller;
use Core\Response;
use App\Models\User;
use App\Models\Quest;

class QuestController extends Controller {
    public function daily(){
        $user = $this->authUserOrFail();
        $u = new User();
        $profile = $u->getProfile($user['id']);
        $q = new Quest();
        $q->ensureRows($profile['player_id']);
        $quests = $q->today();
        $progress = $q->progressForUser($profile['player_id']);
        foreach ($quests as &$qq){
            $p = $progress[$qq['id']] ?? null;
            $qq['progress'] = $p ? (int)$p['progress'] : 0;
            $qq['completed'] = $p ? (int)$p['completed'] : 0;
            $qq['claimed'] = $p ? (int)$p['claimed'] : 0;
        }
        Response::json(['ok'=>true, 'quests'=>$quests]);
    }
    public function claim($params){
        $user = $this->authUserOrFail();
        $questId = (int)($params['id'] ?? 0);
        $u = new User();
        $profile = $u->getProfile($user['id']);
        $q = new Quest();
        $r = $q->claim($profile['player_id'], $questId);
        if (!$r['ok']) Response::json(['error'=>$r['error']], 400);
        Response::json(['ok'=>true, 'reward_coin'=>$r['reward_coin']]);
    }
}

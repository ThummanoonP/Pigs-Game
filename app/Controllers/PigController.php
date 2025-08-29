<?php
namespace App\Controllers;
use Core\Controller;
use Core\Response;
use App\Models\Pig;
use App\Models\Food;
use App\Models\Quest;
use App\Models\User;
use App\Models\Setting;

class PigController extends Controller {
    public function index(){
        $user = $this->authUserOrFail();
        $u = new User();
        $profile = $u->getProfile($user['id']);
        $pig = new Pig();
        $pigs = $pig->allByPlayer($profile['player_id']);
        Response::json(['ok'=>true, 'pigs'=>$pigs]);
    }

    public function feed($params){
        $user = $this->authUserOrFail();
        $pigId = (int)($params['id'] ?? 0);
        $data = $this->input();
        $foodId = isset($data['food_id']) ? (int)$data['food_id'] : 0;

        $u = new User();
        $profile = $u->getProfile($user['id']);
        $playerId = $profile['player_id'];

        $pig = new Pig();
        $p = $pig->findByIdAndPlayer($pigId, $playerId);
        if (!$p){ Response::json(['error'=>'Pig not found'], 404); }

        $setting = new Setting();
        $cooldown = (int)($setting->get('feed_cooldown_seconds', 10));
        if ($p['last_fed_at']){
            $last = strtotime($p['last_fed_at']);
            if (time() - $last < $cooldown){
                $remain = $cooldown - (time()-$last);
                Response::json(['error'=>'Cooldown','seconds_remaining'=>$remain], 429);
            }
        }

        $food = new Food();
        if ($foodId === 0){
            $foods = $food->list($playerId);
            $avail = null;
            foreach ($foods as $f){ if ((int)$f['qty'] > 0){ $avail = $f; break; } }
            if (!$avail){ Response::json(['error'=>'No food available'], 400); }
            $foodId = (int)$avail['id'];
        }
        $frow = $food->find($playerId, $foodId);
        if (!$frow){ Response::json(['error'=>'Food not found'], 404); }

        if (!$food->consume($playerId, $foodId)){
            Response::json(['error'=>'Food out of stock'], 400);
        }

        $pig->updateFed($pigId);

        $q = new Quest();
        $q->ensureRows($playerId);
        $q->addProgress($playerId, 1);

        $this->query('UPDATE players SET exp=exp+1, level = 1 + FLOOR(exp/100) WHERE id=?', [$playerId]);

        Response::json(['ok'=>true, 'message'=>'Fed successfully', 'pig_id'=>$pigId, 'food_id'=>$foodId]);
    }
}

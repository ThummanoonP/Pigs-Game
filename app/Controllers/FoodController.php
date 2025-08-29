<?php
namespace App\Controllers;
use Core\Controller;
use Core\Response;
use App\Models\Food;
use App\Models\User;

class FoodController extends Controller {
    public function index(){
        $user = $this->authUserOrFail();
        $u = new User();
        $profile = $u->getProfile($user['id']);
        $f = new Food();
        Response::json(['ok'=>true, 'foods'=>$f->list($profile['player_id'])]);
    }
    public function addQuantity(){
        $user = $this->authUserOrFail();
        $data = $this->input();
        $foodId = (int)($data['food_id'] ?? 0);
        $qty = (int)($data['qty'] ?? 0);
        if ($foodId<=0 || $qty<=0){ Response::json(['error'=>'food_id and qty required'], 422); }
        $u = new User();
        $profile = $u->getProfile($user['id']);
        $f = new Food();
        $ok = $f->addQty($profile['player_id'], $foodId, $qty);
        if (!$ok) Response::json(['error'=>'Update failed'], 400);
        Response::json(['ok'=>true]);
    }
}

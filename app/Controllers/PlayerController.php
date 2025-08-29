<?php
namespace App\Controllers;
use Core\Controller;
use Core\Response;
use App\Models\User;

class PlayerController extends Controller {
    public function profile(){
        $user = $this->authUserOrFail();
        $u = new User();
        $profile = $u->getProfile($user['id']);
        if (!$profile){
            $u->ensurePlayerInit($user['id']);
            $profile = $u->getProfile($user['id']);
        }
        Response::json(['ok'=>true, 'profile'=>$profile]);
    }
    public function init(){
        $user = $this->authUserOrFail();
        $u = new User();
        $playerId = $u->ensurePlayerInit($user['id']);
        Response::json(['ok'=>true, 'player_id'=>$playerId]);
    }
}

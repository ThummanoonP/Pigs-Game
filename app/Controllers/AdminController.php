<?php
namespace App\Controllers;
use Core\Controller;
use Core\Response;
use App\Models\User;

class AdminController extends Controller {
    public function ban(){
        $user = $this->authUserOrFail();
        $this->adminOrFail($user);
        $data = $this->input();
        $uid = (int)($data['user_id'] ?? 0);
        if ($uid<=0) Response::json(['error'=>'user_id required'], 422);
        $u = new User();
        $u->setBan($uid, true);
        Response::json(['ok'=>true, 'banned_user_id'=>$uid]);
    }
    public function unban(){
        $user = $this->authUserOrFail();
        $this->adminOrFail($user);
        $data = $this->input();
        $uid = (int)($data['user_id'] ?? 0);
        if ($uid<=0) Response::json(['error'=>'user_id required'], 422);
        $u = new User();
        $u->setBan($uid, false);
        Response::json(['ok'=>true, 'unbanned_user_id'=>$uid]);
    }
}

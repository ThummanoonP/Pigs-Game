<?php
namespace Core;
class Controller extends Model {
    protected function input(){
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    protected function authUserOrFail(){
        $auth = new \Core\Auth();
        $user = $auth->user();
        if (!$user){ Response::json(['error'=>'Unauthorized'], 401); }
        if ((int)$user['is_banned'] === 1){
            Response::json(['error'=>'Banned user'], 403);
        }
        return $user;
    }
    protected function adminOrFail($user){
        if ((int)$user['is_admin'] !== 1){
            Response::json(['error'=>'Admin only'], 403);
        }
    }
}

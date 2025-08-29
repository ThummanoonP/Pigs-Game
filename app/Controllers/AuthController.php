<?php
namespace App\Controllers;
use Core\Controller;
use Core\Response;
use Core\Auth;
use App\Models\User;

class AuthController extends Controller {
    public function register(){
        $data = $this->input();
        $email = $data['email'] ?? '';
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        if (!$email || !$username || !$password){
            Response::json(['error'=>'email, username, password required'], 422);
        }
        $u = new User();
        if ($u->findByEmail($email)){
            Response::json(['error'=>'Email already registered'], 409);
        }
        $uid = $u->create($email, $username, $password);
        Response::json(['ok'=>true, 'user_id'=>$uid], 201);
    }

    public function login(){
        $data = $this->input();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        if (!$email || !$password){
            Response::json(['error'=>'email, password required'], 422);
        }
        $u = new User();
        $row = $u->findByEmail($email);
        if (!$row || !password_verify($password, $row['password_hash'])){
            Response::json(['error'=>'Invalid credentials'], 401);
        }
        if ((int)$row['is_banned'] === 1){
            Response::json(['error'=>'Banned user'], 403);
        }
      
        $u->ensurePlayerInit($row['id']);

        $auth = new Auth();
        $token = $auth->issueToken($row['id']);
        Response::json(['ok'=>true, 'token'=>$token]);
    }
}

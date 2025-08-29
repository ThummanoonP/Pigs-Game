<?php
namespace Core;
class Auth extends Model {
    private $cfg;
    public function __construct(){
        parent::__construct();
        $this->cfg = require __DIR__ . '/../Config/config.php';
    }
    private function getBearerToken(){
        $headers = getallheaders();
        if (isset($headers['Authorization']) && preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $m)){
            return $m[1];
        }
        return null;
    }
    public function user(){
        $token = $this->getBearerToken();
        if (!$token) return null;
        $payload = $this->jwtDecode($token);
        if (!$payload || empty($payload['uid'])) return null;
        $stmt = $this->query('SELECT id,email,username,is_admin,is_banned FROM users WHERE id = ?', [$payload['uid']]);
        $res = $stmt->get_result()->fetch_assoc();
        return $res ?: null;
    }
    public function issueToken($uid){
        $now = time();
        $payload = ['uid'=>$uid, 'iat'=>$now, 'exp'=>$now + 86400*3];
        return $this->jwtEncode($payload);
    }
    private function b64($data){ return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); }
    private function b64d($data){ return base64_decode(strtr($data, '-_', '+/')); }
    private function jwtEncode(array $payload){
        $header = ['alg'=>'HS256','typ'=>'JWT'];
        $h = $this->b64(json_encode($header));
        $p = $this->b64(json_encode($payload));
        $sig = hash_hmac('sha256', $h.'.'.$p, $this->cfg['jwt_secret'], true);
        return $h.'.'.$p.'.'.$this->b64($sig);
    }
    private function jwtDecode(string $jwt){
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;
        [$h,$p,$s] = $parts;
        $sig = $this->b64d($s);
        $calc = hash_hmac('sha256', $h.'.'.$p, $this->cfg['jwt_secret'], true);
        if (!hash_equals($calc, $sig)) return null;
        $payload = json_decode($this->b64d($p), true);
        if (!$payload) return null;
        if (isset($payload['exp']) && time() > $payload['exp']) return null;
        return $payload;
    }
}

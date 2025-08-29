<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/app/Config/config.php';
spl_autoload_register(function($class){
    $base = __DIR__ . '/app/';
    $class = ltrim($class, '\\');

    if (strpos($class, 'App\\') === 0) {
        $class = substr($class, 4); 
    }

    $class = str_replace('\\', '/', $class);
    $file = $base . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

use Core\Router;
use Core\Response;

header('Content-Type: application/json; charset=utf-8');

$router = new Router();

$router->get('/', function() {
    header('Content-Type: application/json');
    echo json_encode([
        "message" => "🐷 Piggy API is running!",
        "available_routes" => [
            "POST /auth/register",
            "POST /auth/login",
            "POST /pig/feed",
            "POST /mission/claim",
            "POST /food/add",
            "POST /admin/ban"
        ]
    ]);
});

$router->post('/auth/register', ['App\Controllers\AuthController', 'register']);
$router->post('/auth/login',    ['App\Controllers\AuthController', 'login']);

$router->get('/player/profile',        ['App\Controllers\PlayerController', 'profile']);
$router->post('/player/init',          ['App\Controllers\PlayerController', 'init']); 

$router->get('/pigs',                  ['App\Controllers\PigController', 'index']);
$router->post('/pigs/{id}/feed',       ['App\Controllers\PigController', 'feed']);
$router->get('/food',                  ['App\Controllers\FoodController', 'index']);
$router->post('/food/add',             ['App\Controllers\FoodController', 'addQuantity']); 

$router->get('/quests/daily',          ['App\Controllers\QuestController', 'daily']);
$router->post('/quests/claim/{id}',    ['App\Controllers\QuestController', 'claim']);

$router->post('/admin/ban',            ['App\Controllers\AdminController', 'ban']);
$router->post('/admin/unban',          ['App\Controllers\AdminController', 'unban']);

$router->run();

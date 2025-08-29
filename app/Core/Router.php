<?php
namespace Core;
class Router {
    private array $routes = ['GET'=>[], 'POST'=>[]];

    public function get(string $path, $handler){ $this->routes['GET'][$this->normalize($path)] = $handler; }
    public function post(string $path, $handler){ $this->routes['POST'][$this->normalize($path)] = $handler; }

    private function normalize($path){
        return rtrim($path, '/');
    }

    private function match($method, $uri){
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        foreach ($this->routes[$method] ?? [] as $route => $handler){
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $route);
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)){
                array_shift($matches);
                // extract param names
                preg_match_all('#\{([^/]+)\}#', $route, $names);
                $params = [];
                foreach($names[1] ?? [] as $i => $name){
                    $params[$name] = $matches[$i];
                }
                return [$handler, $params];
            }
        }
        return [null, []];
    }

    public function run(){
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        [$handler, $params] = $this->match($method, $uri);

        if (!$handler) {
            Response::json(['error'=>'Route not found','method'=>$method,'uri'=>$uri], 404);
            return;
        }

        if (is_callable($handler)) {
            call_user_func($handler, $params);
        } elseif (is_array($handler)) {
            [$class, $methodName] = $handler;
            $controller = new $class();
            $controller->$methodName($params);
        } else {
            throw new \Exception("Invalid route handler");
        }
    }
}

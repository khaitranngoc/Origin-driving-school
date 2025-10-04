<?php
// /app/Core/Router.php
final class Router {
  private static array $routes = ['GET'=>[], 'POST'=>[]];
  public static function get(string $p, callable|array $h): void { self::$routes['GET'][$p] = $h; }
  public static function post(string $p, callable|array $h): void { self::$routes['POST'][$p] = $h; }
  public static function dispatch(string $uri, string $method): void {
    $path = parse_url($uri, PHP_URL_PATH);
    $h = self::$routes[$method][$path] ?? null;
    if (!$h) { http_response_code(404); echo '404'; return; }
    if (is_array($h)) { [$class,$action] = $h; (new $class)->$action(); return; }
    $h();
  }
}

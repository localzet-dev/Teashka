<?php

use localzet\Server;
use localzet\Server\Connection\TcpConnection;
use support\Container;
use support\Response;
use support\Translation;
use Symfony\Component\Yaml\Yaml;
use Triangle\Engine\App;
use Triangle\Engine\Config;
use Triangle\Engine\Http\Request;
use Triangle\Engine\Router;
use Triangle\Engine\View\Blade;
use Triangle\Engine\View\Raw;
use Triangle\Engine\View\ThinkPHP;
use Triangle\Engine\View\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

define('BASE_PATH', dirname(__DIR__));

/** RESPONSE HELPERS */


/**
 * @param mixed $body
 * @param int $status
 * @param array $headers
 * @param bool $http_status
 * @param bool $onlyJson
 * @return Response
 * @throws Throwable
 */
function response(mixed $body = '', int $status = 200, array $headers = [], bool $http_status = false, bool $onlyJson = false): Response
{
    $body = [
        'debug' => config('app.debug'),
        'status' => $status,
        'data' => $body
    ];
    $status = ($http_status === true) ? $status : 200;

    if (request()->expectsJson() || $onlyJson) {
        return responseJson($body, $status, $headers);
    } else {
        return responseView($body, $status, $headers);
    }
}

/**
 * @param string $blob
 * @param string $type
 * @return Response
 */
function responseBlob(string $blob, string $type = 'image/png'): Response
{
    return new Response(
        200,
        [
            'Content-Type' => $type,
            'Content-Length' => strlen($blob)
        ],
        $blob
    );
}

/**
 * @param $data
 * @param int $status
 * @param array $headers
 * @param int $options
 * @return Response
 */
function responseJson($data, int $status = 200, array $headers = [], int $options = JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR): Response
{
    $headers = ['Content-Type' => 'application/json'] + $headers;
    $body = json($data, $options);

    return new Response($status, $headers, $body);
}

/**
 * @param array $data
 * @param null $status
 * @param array $headers
 * @return Response
 * @throws Throwable
 */
function responseView(array $data, $status = null, array $headers = []): Response
{
    if (
        ($status == 200 || $status == 500)
        && (!empty($data['status']) && is_numeric($data['status']))
        && ($data['status'] >= 100 && $data['status'] < 600)
    ) {
        $status = $data['status'];
    }
    $template = ($status == 200) ? 'success' : 'error';

    return new Response($status, $headers, Raw::renderSys($template, $data));
}

/**
 * @param string $location
 * @param int $status
 * @param array $headers
 * @return Response
 */
function redirect(string $location, int $status = 302, array $headers = []): Response
{
    $response = new Response($status, ['Location' => $location]);
    if (!empty($headers)) {
        $response->withHeaders($headers);
    }
    return $response;
}

/**
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @param string|null $plugin
 * @param int $http_code
 * @return Response
 */
function view(string $template, array $vars = [], string $app = null, string $plugin = null, int $http_code = 200): Response
{
    $request = request();
    $plugin = $plugin === null ? ($request->plugin ?? '') : $plugin;
    $handler = config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
    return new Response($http_code, [], $handler::render($template, $vars, $app, $plugin));
}

/**
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 * @throws Throwable
 */
function raw_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], Raw::render($template, $vars, $app));
}

/**
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 */
function blade_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], Blade::render($template, $vars, $app));
}

/**
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 */
function think_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], ThinkPHP::render($template, $vars, $app));
}

/**
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 * @throws LoaderError
 * @throws RuntimeError
 * @throws SyntaxError
 */
function twig_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], Twig::render($template, $vars, $app));
}

/**
 * 404 not found
 *
 * @return Response
 * @throws Throwable
 */
function not_found(): Response
{
    return response('Ничего не найдено', 404);
}

/** DIRS HELPERS */


/**
 * Copy dir
 * @param string $source
 * @param string $dest
 * @param bool $overwrite
 * @return void
 */
function copy_dir(string $source, string $dest, bool $overwrite = false): void
{
    if (is_dir($source)) {
        if (!is_dir($dest)) {
            mkdir($dest);
        }
        $files = array_diff(scandir($source), ['.', '..']) ?: [];
        foreach ($files as $file) {
            copy_dir("$source/$file", "$dest/$file", $overwrite);
        }
    } else if (file_exists($source) && ($overwrite || !file_exists($dest))) {
        copy($source, $dest);
    }
}

/**
 * ScanDir.
 * @param string $basePath
 * @param bool $withBasePath
 * @return array
 */
function scan_dir(string $basePath, bool $withBasePath = true): array
{
    if (!is_dir($basePath)) {
        return [];
    }
    $paths = array_diff(scandir($basePath), ['.', '..']) ?: [];
    return $withBasePath ? array_map(fn($path) => $basePath . DIRECTORY_SEPARATOR . $path, $paths) : $paths;
}

/**
 * Remove dir
 * @param string $dir
 * @return bool
 */
function remove_dir(string $dir): bool
{
    if (is_link($dir) || is_file($dir)) {
        return file_exists($dir) && unlink($dir);
    }
    $files = array_diff(scandir($dir), ['.', '..']) ?: [];
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) && !is_link($path) ? remove_dir($path) : file_exists($path) && unlink($path);
    }
    return file_exists($dir) && rmdir($dir);
}

/**
 * Create directory
 * @param string $dir
 * @return bool
 */
function create_dir(string $dir): bool
{
    return mkdir($dir);
}

/**
 * Rename directory
 * @param string $oldName
 * @param string $newName
 * @return bool
 */
function rename_dir(string $oldName, string $newName): bool
{
    return rename($oldName, $newName);
}


/** PARSERS HELPERS */


/**
 * Декодирует строку в объект.
 *
 * Этот метод сначала попытается проанализировать данные
 * как строку JSON (поскольку большинство провайдеров используют этот формат), а затем XML и parse_str.
 *
 * @param string|null $raw
 *
 * @return mixed
 */
function parse(string $raw = null): mixed
{
    $parsers = ['parseJson', 'parseXml', 'parseQueryString'];

    foreach ($parsers as $parser) {
        $data = $parser($raw);
        if ($data) {
            return $data;
        }
    }

    return null;
}

/**
 * Декодирует строку JSON
 *
 * @param string|null $raw
 * @return mixed
 */
function parseJson(string $raw = null): mixed
{
    $data = json_decode($raw, true);
    return json_last_error() === JSON_ERROR_NONE ? $data : null;
}

/**
 * Декодирует строку XML
 *
 * @param string|null $raw
 * @return array|null
 */
function parseXml(string $raw = null): ?array
{
    libxml_use_internal_errors(true);

    $raw = preg_replace('/([<\/])([a-z0-9-]+):/i', '$1', $raw);
    $xml = simplexml_load_string($raw);

    libxml_use_internal_errors(false);

    if (!$xml) {
        return null;
    }

    $arr = json_decode(json_encode((array)$xml), true);
    return [$xml->getName() => $arr];
}

/**
 * Разбирает строку на переменные
 *
 * @param string|null $raw
 * @return StdClass|null
 */
function parseQueryString(string $raw = null): ?StdClass
{
    parse_str($raw, $output);

    if (!is_array($output)) {
        return null;
    }

    return (object)$output;
}


/** FORMATS HELPERS */


/**
 * @param $value
 * @param int $flags
 * @return string|false
 */
function json($value, int $flags = JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR): false|string
{
    return json_encode($value, $flags);
}

/**
 * @param $xml
 * @return Response
 */
function xml($xml): Response
{
    if ($xml instanceof SimpleXMLElement) {
        $xml = $xml->asXML();
    }
    return new Response(200, ['Content-Type' => 'text/xml'], $xml);
}

/**
 * @param $data
 * @param string $callbackName
 * @return Response
 */
function jsonp($data, string $callbackName = 'callback'): Response
{
    if (!is_scalar($data) && null !== $data) {
        $data = json_encode($data);
    }
    return new Response(200, [], "$callbackName($data)");
}

/**
 * @param $yaml
 * @return Response
 */
function yaml($yaml): Response
{
    if (!class_exists(Yaml::class)) {
        throw new RuntimeException("Запусти composer require symfony/yaml для поддержки YAML");
    }
    if (is_array($yaml)) {
        $yaml = Yaml::dump($yaml);
    }
    return new Response(200, ['Content-Type' => 'text/yaml'], $yaml);
}


/** TRANSLATION HELPERS */


/**
 * Translation
 * @param string $id
 * @param array $parameters
 * @param string|null $domain
 * @param string|null $locale
 * @return string
 */
function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
{
    $res = Translation::trans($id, $parameters, $domain, $locale);
    return $res === '' ? $id : $res;
}

/**
 * Locale
 * @param string|null $locale
 * @return string
 */
function locale(string $locale = null): string
{
    if (!$locale) {
        return Translation::getLocale();
    }
    Translation::setLocale($locale);
    return $locale;
}


/** SYSTEM HELPERS */


/**
 * @param string|null $key
 * @param $default
 * @return array|mixed|null
 */
function config(string $key = null, $default = null): mixed
{
    return Config::get($key, $default);
}

/**
 * @return TcpConnection|null
 */
function connection(): ?TcpConnection
{
    return App::connection();
}

/**
 * @return \support\Request|Request|null
 */
function request(): \support\Request|Request|null
{
    return App::request();
}

/**
 * @return Server|null
 */
function server(): ?Server
{
    return App::server();
}

/**
 * @return bool
 */
function is_phar(): bool
{
    return class_exists(Phar::class, false) && Phar::running();
}

/**
 * @param string $name
 * @param ...$parameters
 * @return string
 */
function route(string $name, ...$parameters): string
{
    $route = Router::getByName($name);
    if (!$route) {
        return '';
    }

    if (!$parameters) {
        return $route->url();
    }

    if (is_array(current($parameters))) {
        $parameters = current($parameters);
    }

    return $route->url($parameters);
}

/**
 * @param mixed|null $key
 * @param mixed|null $default
 * @return mixed
 * @throws Exception
 */
function session(mixed $key = null, mixed $default = null): mixed
{
    $session = request()->session();
    if (null === $key) {
        return $session;
    }
    if (is_array($key)) {
        $session->put($key);
        return null;
    }
    if (strpos($key, '.')) {
        $keyArray = explode('.', $key);
        $value = $session->all();
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }
    return $session->get($key, $default);
}

/**
 * Получение IP-адреса
 *
 * @return string|null IP-адрес
 */
function getRequestIp(): ?string
{
    $ip = request()->header(
        'x-real-ip',
        request()->header(
            'x-forwarded-for',
            request()->header(
                'client-ip',
                request()->header(
                    'x-client-ip',
                    request()->header(
                        'remote-addr',
                        request()->header(
                            'via'
                        )
                    )
                )
            )
        )
    );
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
}

/**
 * Get request parameters, if no parameter name is passed, an array of all values is returned, default values is supported
 * @param string|null $param param's name
 * @param mixed|null $default default value
 * @return mixed|null
 */
function input(string $param = null, mixed $default = null): mixed
{
    return is_null($param) ? request()->all() : request()->input($param, $default);
}


/** PATHS HELPERS */


/**
 * @param string $path
 * @return string
 */
function app_path(string $path = ''): string
{
    $appPath = App::appPath();
    return path_combine($appPath != '' ? $appPath : (BASE_PATH . DIRECTORY_SEPARATOR . 'app'), $path);
}

/**
 * @param string $path
 * @return string
 */
function view_path(string $path = ''): string
{
    return path_combine(app_path('view'), $path);
}

/**
 * @param string $path
 * @return string
 */
function public_path(string $path = ''): string
{
//    static $publicPath = '';
//    if (!$publicPath) {
//        $publicPath = config('app.public_path') ?: run_path('public');
//    }
    $publicPath = App::publicPath();
    return path_combine($publicPath != '' ? $publicPath : (config('app.public_path') ?: run_path('public')), $path);
}

/**
 * @param string $path
 * @return string
 */
function config_path(string $path = ''): string
{
    return path_combine(base_path('config'), $path);
}

/**
 * @param string $path
 * @return string
 */
function runtime_path(string $path = ''): string
{
    static $runtimePath = '';
    if (!$runtimePath) {
        $runtimePath = config('app.runtime_path') ?: run_path('runtime');
    }
    return path_combine($runtimePath, $path);
}

/**
 * Generate paths based on given information
 * @param string $front
 * @param string $back
 * @return string
 */
function path_combine(string $front, string $back): string
{
    return $front . ($back ? (DIRECTORY_SEPARATOR . ltrim($back, DIRECTORY_SEPARATOR)) : $back);
}

/**
 * return the program execute directory
 * @param string $path
 * @return string
 */
function run_path(string $path = ''): string
{
    static $runPath = '';
    if (!$runPath) {
        $runPath = is_phar() ?
            dirname(Phar::running(false)) :
            BASE_PATH;
    }
    return path_combine($runPath, $path);
}

/**
 * @param false|string $path
 * @return string
 */
function base_path(false|string $path = ''): string
{
    if (false === $path) {
        return run_path();
    }
    return path_combine(BASE_PATH, $path);
}

/**
 * Get realpath
 * @param string $filePath
 * @return string
 */
function get_realpath(string $filePath): string
{
    if (str_starts_with($filePath, 'phar://')) {
        return $filePath;
    } else {
        return realpath($filePath);
    }
}


/** SERVER HELPERS */


/**
 * @param $server
 * @param $class
 */
function server_bind($server, $class): void
{
    $callbackMap = [
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onServerStop',
        'onWebSocketConnect',
        'onServerReload'
    ];
    foreach ($callbackMap as $name) {
        if (method_exists($class, $name)) {
            $server->$name = [$class, $name];
        }
    }
    if (method_exists($class, 'onServerStart')) {
        call_user_func([$class, 'onServerStart'], $server);
    }
}

/**
 * @param $processName
 * @param $config
 * @return void
 */
function server_start($processName, $config): void
{
    $server = new Server($config['listen'] ?? null, $config['context'] ?? []);
    $propertyMap = [
        'count',
        'user',
        'group',
        'reloadable',
        'reusePort',
        'transport',
        'protocol',
    ];
    $server->name = $processName;
    foreach ($propertyMap as $property) {
        if (isset($config[$property])) {
            $server->$property = $config[$property];
        }
    }

    $server->onServerStart = function ($server) use ($config) {
        require_once base_path('/support/bootstrap.php');

        foreach ($config['services'] ?? [] as $service) {
            if (!class_exists($service['handler'])) {
                echo "process error: class {$service['handler']} not exists\r\n";
                continue;
            }
            $listen = new Server($service['listen'] ?? null, $service['context'] ?? []);
            if (isset($service['listen'])) {
                echo "listen: {$service['listen']}\n";
            }
            $instance = Container::make($service['handler'], $service['constructor'] ?? []);
            server_bind($listen, $instance);
            $listen->listen();
        }

        if (isset($config['handler'])) {
            if (!class_exists($config['handler'])) {
                echo "process error: class {$config['handler']} not exists\r\n";
                return;
            }

            $instance = Container::make($config['handler'], $config['constructor'] ?? []);
            server_bind($server, $instance);
        }
    };
}

/**
 * @return int
 */
function cpu_count(): int
{
    if (DIRECTORY_SEPARATOR === '\\') {
        return 1;
    }
    $count = 4;
    if (is_callable('shell_exec')) {
        if (strtolower(PHP_OS) === 'darwin') {
            $count = (int)shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = (int)shell_exec('nproc');
        }
    }
    return $count > 0 ? $count : 4;
}

/**
 * Генерация ID
 *
 * @return string
 */
function generateId(): string
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}
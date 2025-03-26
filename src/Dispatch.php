<?php

namespace Source;

use Source;
use Exception;

abstract class Dispatch
{
    /** @var string */
    protected string $projectUrl;

    /** @var string */
    protected string $requestUri;

    /** @var string|mixed */
    protected string $requestMethod;

    /** @var string */
    protected string $namespace;

    /** @var array */
    protected array $routes;

    /** @var array */
    protected array $route;

    /** @var array */
    private array $data;

    /** @var array|null */
    protected ?array $middlewares;

    /** @var bool */
    private bool $parseRequest = false;

    /** @var string|null */
    protected ?string $group = null;

    /** @var int|null */
    protected(set) ?int $error = null;

    public function __construct(string $projectUrl)
    {
        $this->projectUrl = $this->validateUrl($projectUrl);
        $this->requestUri = rtrim(filter_input(INPUT_GET, "route", FILTER_SANITIZE_SPECIAL_CHARS) ?? "/", "/") ?? "/";
        $this->requestMethod = $_SERVER["REQUEST_METHOD"];
    }

    /**
     * @param string $verb
     * @param string $route
     * @param string|callable $handler
     * @param string|null $name
     * @param string|array|null $middlewares
     * @return void
     */
    protected function addRoute(
        string $verb,
        string $route,
        string|callable $handler,
        ?string $name = null,
        null|string|array $middlewares = null
    ): void {
        $this->monitorData($route);
        $route = rtrim($this->group ? "{$this->group}{$route}" : $route, "/");
        $routeName = preg_replace("~{([a-zA-Z-_][a-zA-Z0-9-_]*)}~x", "([^/]*)", $route);

        $this->routes[$verb][$routeName] = [
            "route" => $route,
            "name" => $name,
            "handler" => is_callable($handler) ? $handler : strstr($handler, ":", true),
            "method" => is_string($handler) ? substr(strstr($handler, ":"), "1") : "",
            "middlewares" => $this->addMiddleware($middlewares),
            "data" => $this->data
        ];
    }

    /**
     * @return void
     */
    protected function execute(): void
    {
        if (!empty($this->route)) {
            if (!$this->middlewares()) {
                return;
            }

            if (is_callable($this->route["handler"])) {
                call_user_func($this->route["handler"]);
                return;
            }

            $controller = $this->namespace . "\\" . $this->route["handler"];
            $method = $this->route["method"];

            if (class_exists($controller)) {
                $newController = new $controller($this);
                if (method_exists($newController, $method)) {
                    $newController->$method($this->routeData($this->route["data"]) ?? []);
                    return;
                }
                $this->error = HttpStatusCode::METHOD_NOT_ALLOWED;
                return;
            }
            $this->error = HttpStatusCode::BAD_REQUEST;
            return;
        }

        $this->error = HttpStatusCode::NOT_FOUND;
    }

    /**
     * @return bool
     */
    private function middlewares(): bool
    {
        $middlewares = $this->route["middlewares"];
        if (empty($middlewares)) {
            return true;
        }

        foreach ($middlewares as $middleware) {
            if (class_exists($middleware)) {
                $newMiddleware = new $middleware;
                if (!$newMiddleware->handle($this->routeData($this->route["data"]) ?? [])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string|array|null $middlewares
     * @return array|null
     */
    private function addMiddleware(null|string|array $middlewares): ?array
    {
        $groupMiddlewares = !empty($this->middlewares[$this->group]) ? $this->middlewares[$this->group] : [];
        $routeMiddlewares = is_array($middlewares) ? $middlewares : [$middlewares];

        $middlewares = array_merge($groupMiddlewares, $routeMiddlewares);
        return array_filter($middlewares);
    }

    /**
     * @param string $route
     * @return void
     */
    private function monitorData(string $route): void
    {
        $removeGroupUri = $this->group ? str_replace($this->group, "", $this->requestUri) : $this->requestUri;

        $routeTrim = trim($route, "/");
        $requestUriTrim = trim($removeGroupUri, "/");

        $routeArr = explode("/", $routeTrim);
        $requestUriArr = explode("/", $requestUriTrim);

        $dataArr = array_values(array_diff($requestUriArr, $routeArr));
        preg_match_all("~{([a-zA-Z-_][a-zA-Z0-9-_]*)}~x", $route, $matches, PREG_PATTERN_ORDER);

        $this->addData();
        $index = 0;
        foreach ($matches[1] as $match) {
            $this->data[$match] = $dataArr[$index] ?? null;
            $index++;
        }
    }

    /**
     * @return void
     */
    private function addData(): void
    {
        if ($this->requestMethod == "POST") {
            $this->data = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            return;
        }

        $verbs = ["PUT", "PATCH", "DELETE"];
        if (array_find($verbs, fn(string $verb) => $verb == $this->requestMethod)) {
            $this->data = [];
            $this->parseRequest = true;
            return;
        }

        $this->data = [];
    }

    private function routeData(array $routeData): array
    {
        if ($this->parseRequest) {
            return array_merge($routeData, request_parse_body());
        }

        return $routeData;
    }

    /**
     * @param string $route
     * @param array $data
     * @return string
     */
    protected function parameters(string $route, array $data): string
    {
        $routeData = [];
        $queryStringData = [];

        foreach ($data as $key => $value) {
            if (str_contains($route, $key)) {
                $routeData["{{$key}}"] = $value;
            } else {
                $queryStringData[$key] = $value;
            }
        }

        $route = str_replace(array_keys($routeData), array_values($routeData), $route);
        $params = $queryStringData ? "?" . http_build_query($queryStringData) : null;
        return $this->projectUrl . $route . $params;
    }

    /**
     * @param string $url
     * @return string
     * @throws Exception
     */
    private function validateUrl(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception("The URL provided is not valid");
        }

        return $url;
    }
}
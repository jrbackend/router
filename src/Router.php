<?php

namespace Source;

/**
 *
 */
class Router extends Dispatch
{

    /**
     * @param string $projectUrl
     */
    public function __construct(string $projectUrl)
    {
        parent::__construct($projectUrl);
    }

    /**
     * @param string $route
     * @param string|callable $handler
     * @param string|null $name
     * @param string|array|null $middlewares
     * @return void
     */
    public function get(
        string $route,
        string|callable $handler,
        ?string $name = null,
        null|string|array $middlewares = null
    ): void {
        $this->addRoute("GET", $route, $handler, $name, $middlewares);
    }

    /**
     * @param string $route
     * @param string|callable $handler
     * @param string|null $name
     * @param string|array|null $middlewares
     * @return void
     */
    public function post(
        string $route,
        string|callable $handler,
        ?string $name = null,
        null|string|array $middlewares = null
    ): void {
        $this->addRoute("POST", $route, $handler, $name, $middlewares);
    }

    /**
     * @param string $route
     * @param string|callable $handler
     * @param string|null $name
     * @param string|array|null $middlewares
     * @return void
     */
    public function put(
        string $route,
        string|callable $handler,
        ?string $name = null,
        null|string|array $middlewares = null
    ): void {
        $this->addRoute("PUT", $route, $handler, $name, $middlewares);
    }

    /**
     * @param string $route
     * @param string|callable $handler
     * @param string|null $name
     * @param string|array|null $middlewares
     * @return void
     */
    public function patch(
        string $route,
        string|callable $handler,
        ?string $name = null,
        null|string|array $middlewares = null
    ): void {
        $this->addRoute("PATCH", $route, $handler, $name, $middlewares);
    }

    /**
     * @param string $route
     * @param string|callable $handler
     * @param string|null $name
     * @param string|array|null $middlewares
     * @return void
     */
    public function delete(
        string $route,
        string|callable $handler,
        ?string $name = null,
        null|string|array $middlewares = null
    ): void {
        $this->addRoute("DELETE", $route, $handler, $name, $middlewares);
    }

    /**
     * @param string $namespace
     * @return $this
     */
    public function namespace(string $namespace): self
    {
        $this->namespace = ucwords($namespace, "\\");
        return $this;
    }

    /**
     * @param string|null $group
     * @param array|null $middlewares
     * @return $this
     */
    public function group(?string $group, ?array $middlewares = null): self
    {
        $this->group = $group ? rtrim($group, "/") : null;
        $this->middlewares[$this->group] = $middlewares;

        return $this;
    }

    /**
     * @param string $name
     * @param array|null $data
     * @return string|null
     */
    public function route(string $name, ?array $data = null): ?string
    {
        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                if ($route["name"] == $name) {
                    if ($data) {
                        return $this->parameters($route["route"], $data);
                    }
                    return $this->projectUrl . $route["route"];
                }
            }
        }

        return null;
    }

    /**
     * @return void
     */
    public function dispatch(): void
    {
        if (empty($this->routes) || empty($this->routes[$this->requestMethod])) {
            $this->error = HttpStatusCode::NOT_IMPLEMENTED;
            return;
        }

        $this->route = [];
        foreach ($this->routes[$this->requestMethod] as $route => $dataRoute) {
            if (preg_match("~^" . $route . "$~", $this->requestUri)) {
                $this->route = $dataRoute;
            }
        }

        $this->execute();
    }

    /**
     * @param string $path
     * @param array|null $data
     * @return void
     */
    public function redirect(string $path, ?array $data): void
    {
        if ($redirect = $this->route($path, $data)) {
            header("Location: {$redirect}");
            exit;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            header("Location: {$path}");
            exit;
        }
    }

}
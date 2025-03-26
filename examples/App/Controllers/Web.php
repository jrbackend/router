<?php

namespace App\Controllers;

use Source\Router;

class Web
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function home(): void
    {
        echo "<a href='{$this->router->route("web.register")}'>Cadastro</a>";
    }

    public function register(array $data): void
    {
        if (!empty($data)) {
            var_dump($data);
        }

        $router = $this->router;
        require dirname(__DIR__, 2) . "/form.php";
    }

    public function error(array $data): void
    {
        var_dump($data["errcode"]);
    }
}
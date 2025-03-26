<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Example</title>
</head>
<body>
<form action="<?= $router->route("web.register.post", ["id" => 1]) ?>" method="post">
    <label>
        <span>Nome:</span>
        <input type="text" name="first_name">
    </label>

    <label>
        <span>Sobrenome:</span>
        <input type="text" name="last_name">
    </label>

    <button type="submit">Cadastrar</button>
</form>
</body>
</html>
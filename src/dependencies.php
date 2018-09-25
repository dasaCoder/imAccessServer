<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};


//database
$container['db'] = function ($c){
    $db = $c->get('config')['database'];
    try{
        $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
            $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $ex){
        // log exception
        $c->logger->critical($ex->getMessage());

        // format of exception to return
        $data = [
            'error' => true,
            'message' => $ex->getMessage()
        ];
        return $c['response']->withStatus(500)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode($data));
    }
    return $pdo;
};

$container['ParentController'] = function ($c) {
    return new App\Controllers\ParentController($c);
};

$container['UserController'] = function ($c) {
    return new App\Controllers\UserController($c);
};

$container['User'] = function ($c) {
    return new App\Models\User($c);
};

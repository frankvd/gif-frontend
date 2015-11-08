<?php

require __DIR__ . '/../vendor/autoload.php';

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

// Load config
$config = require __DIR__ . '/../config/config.php';

$dbfunc = $config['dbfunc'];
$jwt_secret = $config['jwt_secret'];
$backend_host = $config['backend_host'];

// Setup authentication class
$auth = new V\Authentication(
    $dbfunc(),
    new Sha256(),
    new Builder(),
    new Parser,
    $jwt_secret
);

// Setup slim app
$app = new \Slim\Slim([
    'debug' => true,
]);

// Setup twig
$loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
$twig = new Twig_Environment($loader, array(
    'cache' => false// __DIR__ . '/../templates_cache',
));

// Authentication middleware
$authMiddleware = function() use ($auth, $app) {
    $cookie = $app->getCookie('jwt');

    if (!$auth->verifyToken($cookie)) {
        $app->redirect('/login');
    }
};

// Register page
$app->get('/register', function() use ($twig) {
    echo $twig->render('register.phtml');
});
// Register POST
$app->post('/register', function() use ($app, $auth, $twig) {
    $username = $app->request()->post('username');
    $password = $app->request()->post('password');

    try {
        $auth->register($username, $password);
    } catch(PDOException $e) {
        $errorMessage = 'error';
        if ($e->getCode() == '23000') {
            $errorMessage = "User $username already exists";
        }
        echo $twig->render('register.phtml', ['error' => $errorMessage]);
        return;
    }

    $app->redirect('/login');
});

// Login page
$app->get('/login', function() use ($twig) {
    echo $twig->render('login.phtml');
});

// Login POST
$app->post('/login', function() use ($twig, $app, $auth) {
    $username = $app->request()->post('username');
    $password = $app->request()->post('password');

    if (!$auth->login($username, $password)) {
        $app->status(401);
        echo $twig->render('login.phtml', ['error' => 'invalfdfdid username or password']);
        return;
    }

    $app->setCookie('jwt', $auth->createTokenForUser($username));
    $app->redirect('/');
});

// Home
$app->get('/', $authMiddleware, function() use ($twig, $backend_host) {
    echo $twig->render('home.phtml', ['backend_host' => $backend_host]);
});

$app->run();
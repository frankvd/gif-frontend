<?php

require __DIR__ . '/../vendor/autoload.php';

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

// Load config
$config = require __DIR__ . '/../config/config.php';

$dbfunc = $config['dbfunc'];
$jwt_secret = $config['jwt_secret'];

// Setup slim app
$app = new \Slim\Slim([
	'debug' => true,
]);

// Setup twig
$loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
$twig = new Twig_Environment($loader, array(
    'cache' => __DIR__ . '/../templates_cache',
));

$app->post('/register', function() use ($app, $dbfunc) {
	$db = $dbfunc();

	$username = $app->request()->post('username');
	$password = $app->request()->post('password');

	$stmt = $db->prepare('INSERT INTO user VALUES(:username, :password)');
	$stmt->execute([
		':username' => $username,
		':password' => password_hash($password, PASSWORD_DEFAULT)
	]);
});

$app->get('/login', function() use ($twig) {
	$twig->render('login.phtml');
});

$app->post('/login', function() use ($app ,$dbfunc, $jwt_secret) {
	$db = $dbfunc();

	$username = $app->request()->post('username');
	$password = $app->request()->post('password');

	$stmt = $db->prepare('SELECT * FROM user WHERE username = :username');
	$stmt->execute([':username' => $username]);

	if ($stmt->rowCount() == 0) {
		return;
	}

	$row = $stmt->fetchObject();

	if (password_verify($password, $row->password)) {
		$signer = new Sha256();
		$token = (new Builder())
			->setIssuer($username) // Configures the issuer (iss claim)
            ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
            ->setExpiration(time() + 3600) // Configures the expiration time of the token (exp claim)
            ->sign($signer, $jwt_secret) // creates a signature using "testing" as key
            ->getToken(); // Retrieves the generated token
        echo $token;
	} else {
		//$app->flash('error', 'invalid username or password');
		//$app->redirect('/login');
	}
});

$app->get('/', function() use ($twig) {
	echo $twig->render('home.phtml');
});

$app->run();
<?php

$dbfunc = function() {
	return new PDO('sqlite:../data/db.sqlite');
};

return [
	'dbfunc' => $dbfunc,
	'backend_host' => 'http://localhost:3000',
	'jwt_secret' => 'super_secret_key'
];
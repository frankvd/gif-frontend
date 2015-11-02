<?php

$dbfunc = function() {
	return new PDO("mysql:dbname=DATABASE;host=127.0.0.1", "USERNAME", "PASSWORD");
};

return [
	'dbfunc' => $dbfunc,
	'jwt_secret' => 'SUPER_SECRET_TOKEN'
];
<?php

$dbfunc = function() {
	return new PDO('sqlite:../data/db.sqlite');
};

return [
	'dbfunc' => $dbfunc,
	'backed_host' => 'http://localhost:3000',
	'jwt_secret' => 'SUPER_SECRET_TOKEN'
];
<?php

require dirname(dirname(__DIR__)) . '/config/bootstrap.php';
require 'config/settings.php';
require 'config/connections.php';
require 'app/models/users.php';

$_ = array_shift($argv);
$email = array_shift($argv);
$password = array_shift($argv);

$user = new Users();
$user->cantCreateSite = true;
$user->updateAttributes(array('name' => $email, 'email' => $email,
	'password' => $password, 'active' => 1));
$user->save();

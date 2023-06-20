<?php
$db_config = [];
$db_config['dsn'] = "mysql:dbname=todo;host=localhost"; 
$db_config['user'] = 'root';
$db_config['password'] = 'mysql';

$key = 'secret56';
$iss = 'http://any-site.org';
$aud = 'http://any-site.com';
$iat = 1356999524;
$nbf = 1357000000;
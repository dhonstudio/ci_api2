<?php

/*
| -------------------------------------------------------------------
| Create production/database.php on config folder, copy this section until the end of section, set your username, password, and the name of database (db), and set production in index.php if you want to live hosting anyway
| -------------------------------------------------------------------
| PROTOTYPE:
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'project';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => '',
	'password' => '',
	'database' => '',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

$db['project'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'your_username',
	'password' => 'your_password',
	'database' => 'your_database',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

$db['project_dev'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'your_username',
	'password' => 'your_password',
	'database' => 'your_database',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
| -------------------------------------------------------------------
| The end of section
| -------------------------------------------------------------------
|
*/

include APPPATH . "config/testing/database.php";

foreach ($db as $name => $set) {
	$set['username'] = 'root';
	$set['password'] = '';
	$db[$name] = $set;
}

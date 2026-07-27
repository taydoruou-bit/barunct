<?php defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => 'localhost',
	'username' => 'admin_pos1',
	//'username' => 'root',
	'password' => '77fEx$p4',
	//'password' => '',
	//'database' => 'barun_db',
	'database' => 'admin_pos2',
	'dbdriver' => 'mysqli',
	'dbprefix' => 'scodeweb_',
	'pconnect' => FALSE,
	'db_debug' => True,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => FALSE
);
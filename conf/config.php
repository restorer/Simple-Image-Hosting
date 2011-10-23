<?php

require_once('main_config.php');

define('BASE_PATH', $mc_basepath);
define('ROOT', $mc_root);
define('DEBUG_ENABLE', $mc_debug);

if (!defined('PATH_SEPARATOR')) {
	define('PATH_SEPARATOR', strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':');
}

$cfg = array();
$cfg['http'] = $mc_http;
$cfg['https'] = $mc_https;
$cfg['root'] = $mc_root;
$cfg['ssl_root'] = $mc_root;
$cfg['sitename'] = $mc_sitename;
$cfg['db_type'] = $mc_dbtype;
$cfg['db_host'] = $mc_dbhost;
$cfg['db_user'] = $mc_dbuser;
$cfg['db_pass'] = $mc_dbpass;
$cfg['db_database'] = $mc_database;

$cfg['jsc_date_format'] = '%m/%d/%Y';
$cfg['date_format'] = 'd.m.Y';
$cfg['datetime_format'] = 'd.m.Y H:i';
$cfg['date_regexp'] = "/^(\d\d).(\d\d).(\d\d\d\d)$/";
$cfg['datetime_regexp'] = "/^(\d\d).(\d\d).(\d\d\d\d).(\d\d):(\d\d)(:(\d\d))?$/";

$cfg['send_mail'] = $mc_sendmail;
$cfg['cookie_domain'] = $mc_cookie_domain;

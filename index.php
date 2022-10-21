<?php
require_once 'src/sys/common.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

header("Access-Control-Allow-Origin: * ");
header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, Content-Type, Authorization, Content-Length, X-Requested-With, Accept");
header("Content-Type: application/json");
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') die();

$base_path = 'src/';
define('BASEPATH', $base_path);
$sys_path = 'src/sys/';
define('SYSPATH', $sys_path);

if (isset($argv[1])) $_SERVER['REQUEST_URI'] = $argv[1];
[$path, $class, $func] = destruct_url();

$test = '5';

if (!file_exists("src/app/$path$class.php")) err("Can't resolve ".$_SERVER['REQUEST_URI']);

require("src/app/$path$class.php");

if (!class_exists($class)) err("Can't resolve ".$_SERVER['REQUEST_URI']);

$app = new $class();
res($app->index($func));
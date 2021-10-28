<?php
require_once 'src/sys/common.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

$base_path = 'src/';
define('BASEPATH', $base_path);
$sys_path = 'src/sys/';
define('SYSPATH', $sys_path);

if (isset($argv[1])) $_SERVER['PATH_INFO'] = $argv[1];
[$path, $class, $func] = destruct_url();

if (file_exists("src/app/$path/$class.php"))
{
    require_once("src/app/$path/$class.php");
    if (class_exists($class)){
        $app = new $class();

        if (method_exists($class, $func)) echo $app->$func();
        else  show_error("Can't resolve 'src/app/".$_SERVER['PATH_INFO']);
    } 
    else  show_error("Can't resolve 'src/app/".$_SERVER['PATH_INFO']);
}
else show_error("Can't resolve 'src/app/".$_SERVER['PATH_INFO']);

?>
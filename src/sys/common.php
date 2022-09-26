<?php
require_once 'src/sys/log.php';
require_once 'src/sys/error.php';

function is_cli(){
	return (PHP_SAPI === 'cli' OR defined('STDIN'));
}

function destruct_url(){
	$url = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['PHP_SELF']) - 9);

	$data = parse_url($url);
	$query = isset($data['query']) ? $data['query'] : '';

	if (trim($data['path'], '/') === '' && strncmp($query, '/', 1) === 0){
		$query = explode('?', $query, 2);
		$_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
	}
	else{
		$_SERVER['QUERY_STRING'] = $query;
	}

	parse_str($_SERVER['QUERY_STRING'], $_GET);

	$new_data = explode('/', strtolower($data['path']));

	$func = trim(array_pop($new_data));
	$class = ucfirst(trim(array_pop($new_data)));
	$path = implode('/', $new_data);

	if (strlen($path)) $path.='/';

	return [$path, $class, $func];
}

function toPrefix($ip){
	return '/'.(32-log((ip2long($ip) ^ ip2long('255.255.255.255'))+1,2));
}

function toMask($prefix){
	return long2ip(((-1 << (32 - (int)$prefix))));
}

function fromIp($start, $end){
	return long2ip((ip2long($end) + 2 - ip2long($start)) ^ ip2long('255.255.255.255'));
}

function &is_loaded($class = '')
{
	static $_is_loaded = array();

	if ($class !== '') $_is_loaded[strtolower($class)] = $class;

	return $_is_loaded;
}

function &load_class($class, $param = NULL)
{
	static $classes = array();

	if (isset($classes[$class])) return $classes[$class];

	is_loaded($class);

	$classes[$class] = isset($param)
		? new $class($param)
		: new $class();
	return $classes[$class];
}

function res($obj, $code = 200){
	http_response_code($code);
	echo json_encode($obj);
}

function err($message = "Greska!", $error_code = 500){
	log_message('error', json_encode([ 'url' => $_SERVER['REQUEST_URI'], '$_POST' => $_POST, 'msg' => $message ]));
	res($message, $error_code);
	exit(1);
}

function log_message($level, $message)
{
	$log =& load_class('Log');

	$log->write_log($level, $message);
}

function _error_handler($severity, $message, $filepath, $line)
{
    $is_error = (((E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

    // Should we ignore the error? We'll get the current error_reporting
    // level and add its bits with the severity bits to find out.
    if (($severity & error_reporting()) !== $severity)  return;

    $error =& load_class('Errors');
    $error->log_exception($severity, $message, $filepath, $line);

		if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors')))
			$error->show_php_error($severity, $message, $filepath, $line);
		else 
			res("Unspecified error!", 500);

    // If the error is fatal, the execution of the script should be stopped because
    // errors can't be recovered from. Halting the script conforms with PHP's
    // default error handling. See http://www.php.net/manual/en/errorfunc.constants.php
    /*if ($is_error)*/ exit(1); // EXIT_ERROR
}

function _exception_handler($exception)
{
	$error =& load_class('Errors');
	$error->log_exception('Error', 'Exception: '.$exception->getMessage(), $exception->getFile(), $exception->getLine());
		// Should we display the error?
	if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors')))
		$error->show_exception($exception);
	else 
		res("Unspecified error!", 500);

	exit(1); // EXIT_ERROR
}

function _shutdown_handler()
{
	$last_error = error_get_last();
	if (isset($last_error) &&
	($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))){
		_error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
	}
}

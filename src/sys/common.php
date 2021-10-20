<?php
require_once 'src/sys/log.php';
require_once 'src/sys/error.php';

function is_cli()
{
	return (PHP_SAPI === 'cli' OR defined('STDIN'));
}

function clean($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            unset($data[$key]);

            $data[clean($key)] = clean($value);
        }
    } else { 
        $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
    }
    return $data;
}

function destruct_url(){

	$url = $_SERVER['PATH_INFO'];
	$data = parse_url($url);
	$new_data = explode('/', $data['path']);

	$func = trim(array_pop($new_data));
	$class = trim(array_pop($new_data));
	$path = implode('/', $new_data);

	return [$path, $class, $func];
}

function &is_loaded($class = '')
{
	static $_is_loaded = array();

	if ($class !== '')
	{
		$_is_loaded[strtolower($class)] = $class;
	}

	return $_is_loaded;
}

function &load_class($class, $param = NULL)
{
	static $classes = array();

	// Does the class exist? If so, we're done...
	if (isset($classes[$class]))
	{
		return $classes[$class];
	}

	// Keep track of what we just loaded
	is_loaded($class);

	$classes[$class] = isset($param)
		? new $class($param)
		: new $class();
	return $classes[$class];
}

function show_error($error_code = 0, $message = "Greska!")
{
	echo json_encode(['error' => true, 'msg' => $message, 'error_code' => $error_code]);
	exit(1);
}
// database connection error => 1
// query error => 2

function show_success($message = 'Success!')
{
	echo json_encode(['success' => true, 'msg' => $message]);
}

function set_status_header($code = 200, $text = '')
{
	if (is_cli()) return;

	if (empty($code) OR ! is_numeric($code))
		show_error('Status codes must be numeric', 500);

	if (empty($text))
	{
		is_int($code) OR $code = (int) $code;
		$stati = array(
				100	=> 'Continue',
				101	=> 'Switching Protocols',

				200	=> 'OK',
				201	=> 'Created',
				202	=> 'Accepted',
				203	=> 'Non-Authoritative Information',
				204	=> 'No Content',
				205	=> 'Reset Content',
				206	=> 'Partial Content',

				300	=> 'Multiple Choices',
				301	=> 'Moved Permanently',
				302	=> 'Found',
				303	=> 'See Other',
				304	=> 'Not Modified',
				305	=> 'Use Proxy',
				307	=> 'Temporary Redirect',

				400	=> 'Bad Request',
				401	=> 'Unauthorized',
				402	=> 'Payment Required',
				403	=> 'Forbidden',
				404	=> 'Not Found',
				405	=> 'Method Not Allowed',
				406	=> 'Not Acceptable',
				407	=> 'Proxy Authentication Required',
				408	=> 'Request Timeout',
				409	=> 'Conflict',
				410	=> 'Gone',
				411	=> 'Length Required',
				412	=> 'Precondition Failed',
				413	=> 'Request Entity Too Large',
				414	=> 'Request-URI Too Long',
				415	=> 'Unsupported Media Type',
				416	=> 'Requested Range Not Satisfiable',
				417	=> 'Expectation Failed',
				422	=> 'Unprocessable Entity',
				426	=> 'Upgrade Required',
				428	=> 'Precondition Required',
				429	=> 'Too Many Requests',
				431	=> 'Request Header Fields Too Large',

				500	=> 'Internal Server Error',
				501	=> 'Not Implemented',
				502	=> 'Bad Gateway',
				503	=> 'Service Unavailable',
				504	=> 'Gateway Timeout',
				505	=> 'HTTP Version Not Supported',
				511	=> 'Network Authentication Required',
		);

		if (isset($stati[$code])) $text = $stati[$code];
		else
			show_error('No status text available. Please check your status code number or supply your own message text.', 500);
    }
		
    if (strpos(PHP_SAPI, 'cgi') === 0)
	{
		header('Status: '.$code.' '.$text, TRUE);
		return;
	}

	$server_protocol = (isset($_SERVER['SERVER_PROTOCOL']) && in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.0', 'HTTP/1.1', 'HTTP/2'), TRUE))
		? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
	header($server_protocol.' '.$code.' '.$text, TRUE, $code);
}

function log_message($level, $message)
{
	$log =& load_class('Log');

	$log->write_log($level, $message);
}

function _error_handler($severity, $message, $filepath, $line)
{
    $is_error = (((E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

    // When an error occurred, set the status header to '500 Internal Server Error'
    // to indicate to the client something went wrong.
    // This can't be done within the $_error->show_php_error method because
    // it is only called when the display_errors flag is set (which isn't usually
    // the case in a production environment) or when errors are ignored because
    // they are above the error_reporting threshold.
    if ($is_error) set_status_header(500);

    // Should we ignore the error? We'll get the current error_reporting
    // level and add its bits with the severity bits to find out.
    if (($severity & error_reporting()) !== $severity)  return;

    $error =& load_class('Errors');
    $error->log_exception($severity, $message, $filepath, $line);

    // Should we display the error?
    if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors')))
        $error->show_php_error($severity, $message, $filepath, $line);

    // If the error is fatal, the execution of the script should be stopped because
    // errors can't be recovered from. Halting the script conforms with PHP's
    // default error handling. See http://www.php.net/manual/en/errorfunc.constants.php
    if ($is_error) exit(1); // EXIT_ERROR
}

function _exception_handler($exception)
{
	$error =& load_class('Errors');
	$error->log_exception('Error', 'Exception: '.$exception->getMessage(), $exception->getFile(), $exception->getLine());

	is_cli() OR set_status_header(500);
		// Should we display the error?
	if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors')))
	{
		$error->show_exception($exception);
	}

	exit(1); // EXIT_ERROR
}

function _shutdown_handler()
{
	$last_error = error_get_last();
	if (isset($last_error) &&
		($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING)))
	{
		_error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
	}
}


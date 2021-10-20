<?php
class Errors 
{
    public $ob_level;

	public $levels = array(
		E_ERROR			=>	'Error',
		E_WARNING		=>	'Warning',
		E_PARSE			=>	'Parsing Error',
		E_NOTICE		=>	'Notice',
		E_CORE_ERROR		=>	'Core Error',
		E_CORE_WARNING		=>	'Core Warning',
		E_COMPILE_ERROR		=>	'Compile Error',
		E_COMPILE_WARNING	=>	'Compile Warning',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice',
		E_STRICT		=>	'Runtime Notice'
	);

    public function __construct()
	{
		$this->ob_level = ob_get_level();
	}

    public function log_exception($severity, $message, $filepath, $line)
	{
		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;
		log_message('error', 'Severity: '.$severity.' --> '.$message.' '.$filepath.' '.$line);
	}

    public function show_error($heading, $message, $status_code = 500)
	{
		if (is_cli())
		{
			$message = "\t".(is_array($message) ? implode("\n\t", $message) : $message);
		}
		else
		{
			set_status_header($status_code);
			$message = (is_array($message) ? implode(' ', $message) : $message);
		}

		log_message('error', $message);

        return json_encode([
            'type' => 'Error',
            'message' => $heading."\n".$message,
        ], JSON_PRETTY_PRINT);
	}

    public function show_exception($exception)
	{
		echo json_encode([
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
            'filepath' => $exception->getFile(),
            'line' => $exception->getLine()
        ], JSON_PRETTY_PRINT);
	}

    public function show_php_error($severity, $message, $filepath, $line)
	{
		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;

		// For safety reasons we don't show the full file path in non-CLI requests
		if ( !is_cli())
		{
			$filepath = str_replace('\\', '/', $filepath);
			if (FALSE !== strpos($filepath, '/'))
			{
				$x = explode('/', $filepath);
				$filepath = $x[count($x)-2].'/'.end($x);
			}
		}

		echo json_encode([
            'type' => $severity,
            'message' => $message,
            'filename' => $filepath,
            'line' => $line
        ], JSON_PRETTY_PRINT);
	}
}



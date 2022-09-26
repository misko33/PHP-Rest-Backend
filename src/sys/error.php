<?php
class Errors 
{
  public $ob_level;
	public $levels = [
		E_ERROR						=>	'Error',
		E_WARNING					=>	'Warning',
		E_PARSE						=>	'Parsing Error',
		E_NOTICE					=>	'Notice',
		E_CORE_ERROR			=>	'Core Error',
		E_CORE_WARNING		=>	'Core Warning',
		E_COMPILE_ERROR		=>	'Compile Error',
		E_COMPILE_WARNING	=>	'Compile Warning',
		E_USER_ERROR			=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE			=>	'User Notice',
		E_STRICT					=>	'Runtime Notice'
	];

  public function __construct(){
		$this->ob_level = ob_get_level();
	}

  public function log_exception($severity, $message, $filepath, $line){
		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;
		log_message('error', 'Severity: '.$severity.' --> '.$message.' '.$filepath.' '.$line);
	}

  public function show_exception($exception){
		res([
			'type' => get_class($exception),
			'message' => $exception->getMessage(),
			'filepath' => $exception->getFile(),
			'line' => $exception->getLine()
		], 500);
	}

  public function show_php_error($severity, $message, $filepath, $line)
	{
		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;

		// For safety reasons we don't show the full file path in non-CLI requests
		if ( !is_cli()){
			$filepath = str_replace('\\', '/', $filepath);
			if (FALSE !== strpos($filepath, '/')){
				$x = explode('/', $filepath);
				$filepath = $x[count($x)-2].'/'.end($x);
			}
		}

		res([
			'type' => $severity,
			'message' => $message,
			'filename' => $filepath,
			'line' => $line
    	], 500);
	}
}



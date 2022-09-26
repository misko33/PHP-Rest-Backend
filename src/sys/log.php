<?php
class Log 
{
    protected $path = 'src/log/';
    protected $permission = '0755';
    protected $threshold = [1, 2];
    protected $date_format = 'Y-m-d H:i:s';
    protected $levels = ['error' => 1, 'debug' => 2, 'info' => 3];
    protected $extension = 'log';

    public function write_log($level, $msg){
        $level = strtolower($level);

        if (!isset($this->levels[$level]) || !in_array($this->levels[$level], $this->threshold))
			return FALSE;

        $filepath = $this->path.date('Ymd').'.'.$this->extension;

        if (!file_exists($filepath)) $newfile = TRUE;
        if ( ! $fp = @fopen($filepath, 'ab')) return FALSE;
        flock($fp, LOCK_EX);

        $date = date($this->date_format);

        $msg = $this->_format_line($level, $date, $msg);

        for ($written = 0, $length = strlen($msg); $written < $length; $written += $result)
			if (($result = fwrite($fp, substr($msg, $written))) === FALSE)
                break;

        flock($fp, LOCK_UN);
        fclose($fp);

        //if (isset($newfile) && $newfile === TRUE) chmod($filepath, $this->permission);

        return is_int($result);
    }

    protected function _format_line($level, $date, $message){
		return ucfirst($level).' - '.$date.' --> '.$message."\n";
	}
}
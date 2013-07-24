<?php

class LogException extends Exception
{
    protected
        $path = '',
        $yesterday,
        $fileName,
        $archiveName,
        $message = '';

    public function __construct($str='', $key = "ERROR")
    {
        $this->initVars($str, $key);
        if ($this->archiveNeeded())
            $this->archiveLog();
        if ($fp = $this->openLog()) {
            $outStr = date("d.m.y H:i") . ":\t" . $this->getFile() . " at line " . $this->getLine() . ":\t" . $this->message . "\n";
            $this->writeLog($fp, $outStr);
            $this->closeLog($fp);
        }
    }

    protected function initVars($str, $key)
    {
        $this->message .= $str;
        $this->path = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR;
        $this->yesterday = date("Y-m-") . (date("d") - 1);
        $this->fileName = $this->path . $key . ".log";
        $this->archiveName = $this->path . $this->yesterday . "_" . $key . ".zip";
    }

    protected function openLog($param = "a+")
    {
        $fp = fopen($this->fileName, $param);
        if (!$fp) {
            echo "Log opening error. File: " . $this->fileName . ", mode: " . $param . "<br>";
            return false;
        } else
            return $fp;

    }

    protected function closeLog($fp)
    {
        if ($fp)
            fclose($fp);
        else
            echo "Log closing error.";
    }

    protected function writeLog($fp, $str)
    {
        if (fwrite($fp, $str) === FALSE)
            echo "Log writing error";
    }

    protected function archiveLog()
    {
        $archive = new PclZip($this->archiveName);
        $archive->create($this->fileName, PCLZIP_OPT_REMOVE_ALL_PATH);
        if ($fp = $this->openLog("w")) {
            $this->writeLog($fp, '');
            $this->closeLog($fp);
        }
    }

    protected function archiveNeeded()
    {
        if (file_exists($this->archiveName)) {
            return false;
        } else return true;
    }

}

?>

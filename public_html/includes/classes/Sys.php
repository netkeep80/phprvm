<?php

class Sys
{
	public static $LoggingEnabled = true;
	
	private static $LogFileC = 0;
	private static $LogFileT = array();
	private static $appStartTime = 0;
	private static $appRunID = 0;
	private static $BenchMarkTime = 0;
	
	public static function init()
	{
		self::$appStartTime = time();
		self::$appRunID = rand(1,999);
		
		if ( defined('LogsDir') && (!file_exists(LogsDir) || !is_dir(LogsDir)) )
		{
			try { 
				mkdir(LogsDir,0777,true); 
			} catch(Exception $e){ }
		}
	}
	
	public static function SetBenchMarkTime($time)
	{
		self::$BenchMarkTime = $time;
	}
	
	public static function Log($data, $filename = '', $force = false, $newfile = false)
	{
		if (!self::$LoggingEnabled && !$force)
			return;
		$filename = (empty($filename)) ? 'log' : $filename;
		
		if (!isset(self::$LogFileT[$filename]))
			self::$LogFileT[$filename] = 0;
		self::$LogFileC++;
		self::$LogFileT[$filename]++;
		
		$ip = (isset($_SERVER["REMOTE_ADDR"])) ? $_SERVER["REMOTE_ADDR"] : 'NA';
		$mt = (isset($_SERVER["REQUEST_METHOD"])) ? $_SERVER["REQUEST_METHOD"] : 'NA';
		$ua = (isset($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : 'NA';
		$ru = (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : 'NA';
		
		$ua = (self::$LogFileT[$filename] > 1) ? '' : $ua;
		$ru = (self::$LogFileT[$filename] > 1) ? '' : $ru;
		
		$runTime = time() - self::$appStartTime;
		$benchTime = (self::$BenchMarkTime != 0) ? ' (bmTm:'.self::$BenchMarkTime.')' : '';
		if (self::$BenchMarkTime != 0)
			self::$BenchMarkTime = 0;
		
		$LogDate = date("m/d/Y H:i:s");
		$data = (is_array($data) || is_object($data)) ? print_r($data,1) : $data;
		$data = "----------\n" . self::$appRunID . " " . self::$LogFileC . "/" . self::$LogFileT[$filename] . " :: "
			. $LogDate . "\t" . $ip . "\t" . $mt . "\t" . $runTime . $benchTime . "\t|\t" . $ru . "\t|\t" . $ua 
			. "\n" . $data . "\n\n";
		
		$file = ($newfile) ? (date('Ymd-His') . '-' . $filename . '.txt') : (date('Y-m-d') . '-' . $filename . '.txt');
		$f = @fopen(LogsDir . '/' . $file, 'a');
		if ($f)
		{
			fwrite ($f, $data);
			@fclose($f);
		}
		
		return date('Y/m/d-H:i') . ' EST ##' . self::$appRunID . '##';
	}
	
	public static function debug($a, $end = false)
	{
		echo '<pre>';
		print_r($a);
		echo '</pre>';
		if ($end) exit;
	}
}

class ErrorHandler extends Sys
{
	
	const SendReport = false;
	
	private static $LogErrors = array();
	private static $SendErrors = array();
	private static $HideErrors = array(
		'mysql_connect',
		'mysql_select',
		'Unknown database',
	);
	private static $IgnoreErrors = array(
		'ErrorHandler',
		'Sys::Log',
	);
	private static $ExtIgnoreErrors = array();
	
	private static $DebugInfo = true;
	private static $DebugLength = 1024;
	
	private static $ControlReportingFile = 'error_stop.txt';
	
	private static $MaxCatchErrors = 10;
	private static $CatchedErrors = 0;
	
	public static function init()
	{
error_reporting(E_ALL ^ E_DEPRECATED);		
		self::$ControlReportingFile = LogsDir . '/' . self::$ControlReportingFile;
		error_reporting(6535);
		set_error_handler('ErrorHandler::catch_error');
		self::error_fatal(E_ALL^E_NOTICE);
	}
	
	public static function SetIgnore($key,$ignore)
	{
		self::$ExtIgnoreErrors[$key] = $ignore;
	}
	public static function RemoveIgnore($key)
	{
		if (isset(self::$ExtIgnoreErrors[$key]) && isset(self::$ExtIgnoreErrors[$key]))
			unset(self::$ExtIgnoreErrors[$key]);
	}
	
	public static function error_fatal($mask = null)
	{
		if(!is_null($mask))
			$GLOBALS['error_fatal'] = $mask;
		else if(!isset($GLOBALS['die_on']))
			$GLOBALS['error_fatal'] = 0;
		return $GLOBALS['error_fatal'];
	}
	
	public static function catch_error($errno, $errstr, $errfile, $errline)
	{
		$errno = $errno & error_reporting();
		
		if(!defined('E_STRICT'))
			define('E_STRICT', 2048);
		if(!defined('E_RECOVERABLE_ERROR'))
			define('E_RECOVERABLE_ERROR', 4096);
		
		$sendMailFlag = self::SendReport;
		$writeLogFlag = true;

		$error_log = "\n<b>";
		switch($errno)
		{
			case E_ERROR:               $error_log .=  "Error";                  break;
			case E_WARNING:             $error_log .=  "Warning";                break;
			case E_PARSE:               $error_log .=  "Parse Error";            break;
			case E_NOTICE:              $error_log .=  "Notice";                 break;
			case E_CORE_ERROR:          $error_log .=  "Core Error";             break;
			case E_CORE_WARNING:        $error_log .=  "Core Warning";           break;
			case E_COMPILE_ERROR:       $error_log .=  "Compile Error";          break;
			case E_COMPILE_WARNING:     $error_log .=  "Compile Warning";        break;
			case E_USER_ERROR:          $error_log .=  "User Error";             break;
			case E_USER_WARNING:        $error_log .=  "User Warning";           break;
			case E_USER_NOTICE:         $error_log .=  "User Notice";            break;
			case E_STRICT:              $error_log .=  "Strict Notice";          break;
			case E_RECOVERABLE_ERROR:   $error_log .=  "Recoverable Error";      break;
			default:                    $error_log .=  "Unknown error ($errno)"; break;
		}
		
		$error_log .= ":($errno)</b> <i>$errstr</i> in <b>$errfile</b> on line <b>$errline</b>\n";
		
		if(function_exists('debug_backtrace'))
		{
			$backtrace = debug_backtrace();
			array_shift($backtrace);
			foreach($backtrace as $i=>$l)
			{
				$error_log .= "[$i] in function <b>{$l['class']}{$l['type']}{$l['function']}</b>";
				if($l['file'])
					$error_log .= " in <b>{$l['file']}</b>";
				if($l['line'])
					$error_log .= " on line <b>{$l['line']}</b>";
				$error_log .= "<br>\n";
			}
		}
		$error_log .= "\n";
		
		if (count(self::$IgnoreErrors) > 0)
		{
			foreach (self::$IgnoreErrors as $ignore)
			{
				if (strpos($error_log, $ignore) !== false)
					return;
			}
		}
		
		if (count(self::$ExtIgnoreErrors) > 0)
		{
			foreach (self::$ExtIgnoreErrors as $ignore_key=>$ignore)
			{
				if (strpos($error_log, $ignore) !== false)
					return;
			}
		}
		
		self::$CatchedErrors++;
		if (self::$MaxCatchErrors != 0 && self::$CatchedErrors > self::$MaxCatchErrors)
			return;
		
		if (self::$MaxCatchErrors != 0)
		{
			clearstatcache();
			$stop_reporting = self::$ControlReportingFile;
			if (!file_exists($stop_reporting))
			{
				if ($f = @fopen($stop_reporting, 'w'))
					fclose($f);
			}
			else
			{
				$last_access = 0;
	
				if ($f = @fopen($stop_reporting, 'r'))
				{
					$last_access = fgets($f, 4096);
					$last_access = (int)$last_access;
					fclose($f);
				}
				$now_access  = time();
				if (($now_access - $last_access) < 10)
				{
					$sendMailFlag = false;
					$writeLogFlag = (self::$CatchedErrors > self::$MaxCatchErrors);
				}
				
				// set last access time
				if ($f = @fopen($stop_reporting, 'w'))
				{
					fwrite ($f, $now_access);
					fclose($f);
				}
			}
		}
		
		$full_debug_info = "\nDebug Info\n";
		
		$debug_keys = array('_GET', '_POST', '_SESSION', '_COOKIE', '_FILES'); // '_SERVER',
		foreach($debug_keys as $key)
		{
			$full_debug_info .= "\$$key\n";
			$full_debug_info .= "-----------------------------------------\n";
			if (isset($GLOBALS[$key]) && is_array($GLOBALS[$key]) && count($GLOBALS[$key]))
			{
				foreach ($GLOBALS[$key] as $k=>$v)
				{
					if ($key == '_FILES')
						$full_debug_info .= "$k:name(" . $_FILES[$k]['name'] . "):size(" . $_FILES[$k]['size'] . ")\n";
					else
					{
						if (is_array($v) || is_object($v))
							$full_debug_info .= "$k:(ns):" . print_r($v,true)  . "\n";
						else
							$full_debug_info .= "$k:(" . strlen($v) . "):" . substr($v, 0, self::$DebugLength) . "\n";
					}
				}
			}
			$full_debug_info .= "-----------------------------------------\n";
		}
		
		$send_log = $error_log;
		$send_log .= nl2br($full_debug_info);
		
		$error_log = strip_tags($error_log);
		if (self::$DebugInfo)
			$error_log .= $full_debug_info;
		
		if (defined("DEBUG") && DEBUG)
			echo '!!!'.$send_log;
		
		$log_id = '';
		if ($writeLogFlag)
		{
			if (!count(self::$LogErrors))
				$log_id = parent::Log($error_log,'error_log', true, true);
			else
			{
				foreach (self::$LogErrors as $tolog)
				{
					if (strpos($error_log, $tolog, 0) > 0)
					{
						$log_id = parent::Log($error_log,'error_log', true, true);
						break;
					}
				}
			}
		}
		
		if ($sendMailFlag)
		{
			$Timestamp = date("m/d/Y H:i A ");
			$send_error_log = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
	<div style="margin: 40px; border: 1px solid #0000bb; padding: 20px;font-family: Verdana, Arial;font-size: 11px;color: #333333;">
		<br><br>
		Timestamp: {$Timestamp}
		<div id="debug">
			<b>Debug info</b><br>
			<hr size="1">
			{$send_log}
		</div>
	</div>
</body>
</html>
EOF;
			$isRequiredToSend = false;
			if (!count(self::$SendErrors))
				$isRequiredToSend = true;
			else
			{
				foreach (self::$SendErrors as $error_to_send)
				{
					if (strpos($error_log, $error_to_send, 0) > 0)
					{
						$isRequiredToSend = true;
						break;
					}
				}
			}
			if ($isRequiredToSend)
			{
				doMail(
					ErrorHandlerEmailFrom,
					ErrorHandlerEmailFromName,
					ErrorHandlerEmailTo,
					"Error reporting $log_id",
					$send_error_log,
					1,
					ErrorHandlerEmailCc,
					ErrorHandlerEmailBcc
				);
			}
		}
		if (!count(self::$HideErrors))
		{
	//		self::error_page();
	//		exit;
		}
		else
		{
			foreach (self::$HideErrors as $error_to_hide)
			{
				if (strpos($error_log, $error_to_hide, 0) > 0)
				{
				//	self::error_page();
				//	exit;
				}
			}
		}
	}
	
	private static function error_page()
	{
		echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr">
<head>
<title>500 - Internal Server Error</title>
<style type="text/css">
* { font-family: helvetica, arial, sans-serif;font-size: 11px;color: #5F6565;}
html {height: 100%;margin-bottom: 1px;}
body {margin: 0px;padding: 0px;height: 100%;margin-bottom: 1px;background: #FFFFFF;font-weight: normal;}
#outline {width: 814px;margin: 0px;padding: 0px;padding-top: 60px;padding-bottom: 60px;background: #FFFFFF;}
#errorboxoutline {width: 600px;margin: 0px;padding: 0px; }
#errorboxheader {width: 600px;margin: 0px;padding: 0px;font-weight: bold;font-size: 12px;line-height: 22px;text-align: center;}
#errorboxbody {margin: 0px;padding: 10px;text-align: left;}
#techinfo {margin: 10px;padding: 10px;text-align: left;border: 1px solid #CCCCCC;color: #CCCCCC;}
#techinfo p {color: #CCCCCC;}
</style>
</head>
<body>
	<div align="center">
		<div id="outline">
		<div id="errorboxoutline">
			<div id="errorboxbody">
				<p><img src="/images/sorry.gif" border="0" /></p>
				<div id="techinfo">
					<p></p>
					<p>
						<ul>
							<li><a href="/" title="Back to Homeage">Back to Homeage</a></li>
						</ul>
					</p>
					<p></p>
				</div>
			</div>
		</div>
		</div>
	</div>
</body>
</html>

EOF;
	}
	
}

Sys::init();
ErrorHandler::init();


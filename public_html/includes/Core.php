<?

$sp_user = 'rm';
$sp_password = 'netkeep';

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != $sp_user || $_SERVER['PHP_AUTH_PW'] != $sp_password) 
{
	header('WWW-Authenticate: Basic realm="Dev Area"');
	header('HTTP/1.0 401 Unauthorized');
	exit;
}


include 'project_config.php';

include DOC_ROOT . '/includes/classes/Email/php_mailer.php';
include DOC_ROOT . '/includes/classes/Sys.php';
include DOC_ROOT . '/includes/classes/DB/DB.php';

class Core
{
	private static $_lib = array();
	
	public static function Init()
	{
		spl_autoload_register('Core::autoload');
		register_shutdown_function("Core::shutdown");
		
		self::initLibs();
		if (defined('SharedLibs') && SharedLibs != '')
		{
			self::initLibs(SharedLibs);
		}
//sys::debug( self::$_lib);
		Ip::Get();
		Settings::Load();
		Web::SessionStart();
		
		// nginx fix
		if ( isset($_SERVER['HTTP_HTTPS']) && strtolower($_SERVER['HTTP_HTTPS']) == 'on' )
			$_SERVER['HTTPS'] = 'on';
		
		if ( !isset($GLOBALS['CronFlag']) && !isset($GLOBALS['ApiFlag']) && isset($_SERVER['REQUEST_METHOD']) )
		{
			// redirect to secure domain 
			if ( Settings::Get('HTTPSOnly') && SECURE_PREFIX == 'https://' 
				&& (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') 
				&& !isset($_GET['pdf'])
			)
			{
				Web::Redirect( Web::SecureDomain() . substr($_SERVER['REQUEST_URI'],1) );
			}
			
			header("Content-type: text/html; charset=utf-8");
			
			// init auth
//			Auth::Init();
			
		}
	}
	
	private static function initLibs($dir = '')
	{
		$dir = ($dir != '') ? $dir : DOC_ROOT . '/includes/classes';
		if (!$dh = opendir($dir))
			return false;
		
		while (($obj = readdir($dh))) 
		{
			if($obj == '.' || $obj == '..' || strpos($obj, '.') === 0) 
				continue;
			if ( is_dir($dir . '/' . $obj) )
			{
				self::initLibs($dir . '/' . $obj);
				continue;
			}
			$class = str_replace('.php', '', $obj);
			self::$_lib[$class] = $dir . '/' . $obj;
		}
		closedir($dh);
	}
	
	public static function autoload($class, $dir = '')
	{
		if ( isset(self::$_lib[$class]) )
		{
			include self::$_lib[$class];
			return true;
		}
		return false;
	}
	
	public static function shutdown()
	{
		if (isset($GLOBALS["DB"]))
			$GLOBALS["DB"]->disconnect();
	}
}

Core::Init();

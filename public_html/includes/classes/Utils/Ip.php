<?

class Ip
{
	public static $Address = null;
	
	public static function Get($force = false)
	{
		if (!is_null(self::$Address) && !$force)
			return self::$Address;
		
		if (!isset($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] == '')
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		
		self::$Address = self::getIp();
		
		return self::$Address;
	}
	public static function Set($ip)
	{
		self::$Address = $ip;
	}
	
	public static function BlackListed($Ip = '')
	{
		global $DB;
		if (empty($Ip)) 
			$Ip = self::$Address;
		
		$ex = $DB->single("select count(*) from IpBlackList where Ip='" . addslashes($Ip) . "'");
		return ($ex > 0);
	}
	
	public static function UserAgent()
	{
		return (isset($_SERVER["HTTP_USER_AGENT"])) ? $_SERVER["HTTP_USER_AGENT"] : '';
	}
	
	public static function Referer()
	{
		return (isset($_SERVER["HTTP_REFERER"])) ? $_SERVER["HTTP_REFERER"] : '';
	}
	public static function RequestUri()
	{
		return (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : '';
	}
	
	private static function directIp()
	{
		if(isset($_SERVER['REMOTE_HOST']) && $_SERVER['REMOTE_HOST'])
		{
			$array = self::extractIp($_SERVER['REMOTE_HOST']);
			if ($array && count($array) >= 1)
				return $array[0]; // first IP in the list
		}
		return $_SERVER['REMOTE_ADDR'];
	}
	
	private static function getIp()
	{
		$keys = array('HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED_FOR','HTTP_FORWARDED','HTTP_CLIENT_IP','HTTP_X_REAL_IP','REMOTE_HOST');
		foreach ($keys as $key)
		{
			if (isset($_SERVER[$key]) && $_SERVER[$key])
			{
				$array = self::extractIp($_SERVER['HTTP_X_FORWARDED_FOR']);
				if ($array && count($array)) 
					return $array[0]; // first IP in the list
			}
		}
		
		return $_SERVER['REMOTE_ADDR'];
	}
	
	private static function extractIp(&$ip)
	{
		if (ereg ("^([0-9]{1,3}\.){3,3}[0-9]{1,3}", $ip, $array))
			return $array;
		return false;
	}
	
	
}


?>
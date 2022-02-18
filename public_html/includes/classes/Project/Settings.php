<?

class Settings
{
	private static $_set = array();
	private static $share_type = array();
	
	public static function Load($force = false)
	{
		if (count(self::$_set) && !$force)
			return;
		
		self::$_set['HTTPSOnly'] = false;
/*
	TODO - need table
		$dt = DB::Instance()->result("select Name,Value,Type from Settings");
		if (!is_array($dt) || !count($dt))
			return;
		foreach ($dt as $row)
		{
			if ($row['Type'] == 'int')
				$row['Value'] = (int)$row['Value'];
			else if ($row['Type'] == 'float')
				$row['Value'] = (float)$row['Value'];
			else if ($row['Type'] == 'bool')
				$row['Value'] = (strtolower(trim($row['Value'])) == 'true' || (int)$row['Value'] == 1);
			self::$_set[ $row['Name'] ] = $row['Value'];
		}
*/
	}
	
	public static function Get($key)
	{
		if (isset(self::$_set[$key])) 
			return self::$_set[$key];
		else
		{
			trigger_error('Unknown setting:' . $key, E_USER_WARNING);
			return null;
		}
	}
}


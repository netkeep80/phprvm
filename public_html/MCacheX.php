<?

class MCacheX
{
	private static $_Host = 'localhost';
	private static $_Port = '11211';
	public static $_Instances = array();
	
	private $_handle = null;
	private $_type = false;
	
	public static function Instance($host = null, $port = null)
	{
		$host = ( empty($host) ) ? self::$_Host : $host;
		$port = ( empty($port) ) ? self::$_Port : $port;
		$key = "{$host}:{$port}";
		
		if ( !isset(self::$_Instances[$key]) )
			self::$_Instances[$key] = new self($host, $port);
		
		return self::$_Instances[$key];
	}
	
	public function __construct($host, $port)
	{
		if ( class_exists('Memcached') )
		{
			$this->_type = 'Memcached';
			
			$this->_handle = new Memcached;
			$this->_handle->addServer($host, $port);
		}
		else if ( class_exists('Memcache') )
		{
			$this->_type = 'Memcache';
			
			$this->_handle = new Memcache;
			$this->_handle->addServer($host, $port);
		}
		else
		{
			trigger_error('Memcache(d) not found', E_USER_WARNING);
			return null;
		}
		
		return $this;
	}
	
	public static function Disconnect()
	{
		foreach (self::$_Instances as $instance)
		{
			if ( $instance['type'] == 'Memcached' )
				$instance['handle']->quit();
			else if ( $instance['type'] == 'Memcache' )
				$instance['handle']->close();
		}
	}
	
	public function Version()
	{
		return ($this->_handle) ? $this->m->getVersion() : null;
	}
	
	public function Stats()
	{
		if ( $this->_type == 'Memcache' || $this->_type == 'Memcached' )
			return $this->_handle->getStats();
	}
	
	public function Get($key)
	{
		return $this->_handle->get($key);
	}
	
	// compress can be used only by Memcache
	// expire - :
	// 	Expiration time of the item. If it's equal to zero, the item will never expire. 
	// 	You can also use Unix timestamp or a number of seconds starting from current time, 
	// 	but in the latter case the number of seconds may not exceed 2592000 (30 days). 
	public function Set($key, $data, $expire, $comress = false)
	{
		if ( $this->_type == 'Memcached' )
			return $this->_handle->set($key, $data, $expire);
		else if ( $this->_type == 'Memcache' )
			return $this->_handle->set($key, $data, $comress, $expire);
		
		return false;
	}
	
	public function Replace($key, $data, $expire, $comress = false)
	{
		if ( $this->_type == 'Memcached' )
			return $this->_handle->replace($key, $data, $expire);
		else if ( $this->_type == 'Memcache' )
			return $this->_handle->replace($key, $data, $comress, $expire);
		
		return false;
	}
	
	// firstly check key for exists in cache. if yes - replaces, if no - adds
	public function Store($key, $data, $expire, $comress = false)
	{
		$value = $this->Get($key);
		if ( $value === false )
		{
			$this->Set($key, $data, $expire, $comress = false);
		}
		else
		{
			$this->Replace($key, $data, $expire, $comress = false);
		}
	}
	
	public function Delete($key)
	{
		return $this->_handle->delete($key);
	}
	
	public function flush()
	{
		$this->_handle->flush();
	}
}

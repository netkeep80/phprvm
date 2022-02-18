<?

class Web
{
	public static function UriToNvp($skip, $to = 'post')
	{
		$to = strtolower(trim($to));
		$skip = str_replace('/', '\/', $skip);
		
		$uri = $_SERVER['REQUEST_URI'];
		$uri = preg_replace("/^" . $skip . "/", "", $uri);
		$uri = preg_replace("/^\//", "", $uri);
		$uri = preg_replace("/\/$/", "", $uri);
		$nvp = explode('/', $uri);
		for ($i = 0; $i < count($nvp); $i+=2)
		{
			if ($to == 'get')
				$_GET[ $nvp[$i] ] = isset($nvp[($i+1)]) ? $nvp[($i+1)] : '';
			else
				$_POST[ $nvp[$i] ] = isset($nvp[($i+1)]) ? $nvp[($i+1)] : '';
		}
	}
	
	public static function Get($key, $type = 'string', $request = '', $absolute = false)
	{
		$request = strtolower($request);
		$request = ($request != 'get' && $request != 'post') ? '' : $request;
		$value = '';
		if ($request == 'get' || $request == '')
			$value = (isset($_GET[$key])) 
				? (($_GET[$key]) ? $_GET[$key] : trim($_GET[$key]))
				: '';
		if ((!isset($_GET[$key]) && $request == '') || $request == 'post')
			$value = (isset($_POST[$key])) 
				? ((is_array($_POST[$key])) ? $_POST[$key] : trim($_POST[$key]))
				: '';
		
		if ($absolute)
		{
			if ($request == 'get' && !isset($_GET[$key]))
				return null;
			if ($request == 'post' && !isset($_POST[$key]))
				return null;
			if (!isset($_GET[$key]) && !isset($_POST[$key]))
				return null;
		}
		
		if (is_array($value))
		{
			if (get_magic_quotes_gpc())
			{
				foreach ($value as $k=>$v)
					$value[$k] = stripslashes($v);
			}
			return $value;
		}
		if ($type == 'int')
		{
			if(is_numeric($value))
				return (int)$value;
			else
				return 0;
		}
		else if ($type == 'float')
			return (float)$value;
		else if ($type == 'bool')
			return (!empty($value));
		else
			$value = trim($value);
		
		return (get_magic_quotes_gpc()) ? stripslashes($value) : $value;
	}
	
	/* utf8 support for ajax */
	public static function GetAx($key, $type = 'string', $request = '')
	{
		$str = self::Get($key, $type, $request);
		self::InputToUtf($res);
		return $str;
	}
	
	public static function InputToUtf( &$input )
	{
		if (!is_array($input))
		{
			$input = preg_replace('/%u([0-9A-F]{2})([0-9A-F]{2})/sei', 'iconv("UCS-2BE", "UTF-8", "\x$1\x$2")', $input);
			return;
		}
		
		foreach ($input as $k=>$v )
		{
			if (is_array($v))
			{
				self::InputToUtf($input[$k]);
			}
			else
			{
				$input[$k] = preg_replace('/%u([0-9A-F]{2})([0-9A-F]{2})/sei', 'iconv("UCS-2BE", "UTF-8", "\x$1\x$2")', $v); 
			}
		}
	}
	
	public static function FormCollection($request = '', $exclude = array(), $form = array())
	{
		$request = ($request != 'get' && $request != 'post') ? '' : $request;
		if ($request == 'get' || $request == '')
		{
			foreach ($_GET as $k=>$v)
			{
				$v = (!is_array($v) && get_magic_quotes_gpc()) ? stripslashes($v) : $v;
				if (!in_array($k, $exclude))
					$form[$k] = $v;
			}
		}
		if ($request == 'post' || $request == '')
		{
			foreach ($_POST as $k=>$v)
			{
				$v = (!is_array($v) && get_magic_quotes_gpc()) ? stripslashes($v) : $v;
				if (!in_array($k, $exclude))
					$form[$k] = $v;
			}
		}
		return $form;
	}
	
	public static function ExForm($form, $exclude = array())
	{
		foreach ($exclude as $key)
		{
			if (isset($form[$key]))
				unset($form[$key]);
		}
		return $form;
	}
	
	public static function SerializeRequest($request_type = '', $exclude = array(), $deimiter = '&')
	{
		$nvp = self::FormCollection($request_type, $exclude);
		
		if (!count($nvp))
			return '';
		
		$nv = array();
		foreach ($nvp as $n=>$v)
			$nv[] = $n . '=' . urlencode($v);
		
		return implode($deimiter, $nv);
	}
	
	public static function IsPost()
	{
		$rm = (isset($_SERVER["REQUEST_METHOD"])) ? strtolower($_SERVER["REQUEST_METHOD"]) : '';
		return ($rm == 'post');
	}
	
	public static function GetRawRequest()
	{
		$HTTP_RAW_POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
		$HTTP_RAW_POST_DATA = preg_replace('/\<soap\:Envelope[^\>]+\>/', '', $HTTP_RAW_POST_DATA);
		$HTTP_RAW_POST_DATA = str_replace('</soap:Envelope>', '', $HTTP_RAW_POST_DATA);
		return $HTTP_RAW_POST_DATA;
	}
	
	public static function GetNPostAction()
	{
		global $GetNPostAction;
		$GetNPostAction = '';


		if ($_SERVER["REQUEST_METHOD"] == 'GET')
		{
			$dets = explode('?', $_SERVER["REQUEST_URI"]);
			$GetNPostAction = $dets[0];
		}
		else
		{
			$GetNPostAction = $_SERVER["REQUEST_URI"];
		}

		$dets = explode('/', $GetNPostAction);
		$GetNPostAction = $dets[(count($dets)-1)];
		$GetNPostAction = str_replace(array('\'','"'), array('',''), $GetNPostAction);
	}
	
	
	/* session */
	public static function SessionStart()
	{
		if (session_id() == '')
			session_start();
	}
	public static function SessionAdd($key, $data)
	{
		if (isset($_SESSION[$key]))
		{
			if (is_array($data))
			{
				foreach ($data as $d_key=>$d_value)
					$_SESSION[$key][$d_key] = $d_value;
			}
			else
				$_SESSION[$key] = $data;
			return $_SESSION[$key];
		}
				
		$_SESSION[$key] = $data;
		return $_SESSION[$key];
	}
	
	public static function SessionReplace($key, $data)
	{
		self::SessionClear($key);
		self::SessionAdd($key, $data);
	}
	
	public static function SessionGet($key = '')
	{
		if ($key != '' && isset($_SESSION[$key]))
		{
			return $_SESSION[$key];
		}
		return null;
	}
	
	public static function SessionClear($key = '')
	{
		if ($key != '' && isset($_SESSION[$key]))
		{
			unset($_SESSION[$key]);
			return;
		}
		// clear all session keys
		$_t = $_SESSION;
		foreach ($_t as $key=>$value)
			unset($_SESSION[$key]);
	}
	
	public static function Redirect($location = '', $code = 301)
	{
		/*
		302 - default
		// 301 Moved Permanently
		header("Location: /foo.php",TRUE,301);
		// 302 Found
		header("Location: /foo.php",TRUE,302);
		header("Location: /foo.php");
		// 303 See Other
		header("Location: /foo.php",TRUE,303);
		// 307 Temporary Redirect
		header("Location: /foo.php",TRUE,307);
		*/
		
		$location = (empty($location)) ? PUBLIC_PREFIX . $_SERVER["HTTP_HOST"] : $location;
		header('Location: ' . $location, true, $code);
		session_write_close();
		exit;
	}
	
	public static function PublicDomain()
	{
		$domain = SUBDOMAIN . '.' . SITE_DOMAIN;
		return  PUBLIC_PREFIX . $domain . '/';
	}
	
	public static function SecureDomain()
	{
		$domain = SUBDOMAIN . '.' . SITE_DOMAIN;
		return  SECURE_PREFIX . $domain . '/';
	}
	
	public static function CentralDomain()
	{
		return PUBLIC_PREFIX . SUBDOMAIN . '.' . CENTRAL_SITE . '/';
	}
	
	public static function CookieDomain()
	{
		if (!isset($_SERVER["HTTP_HOST"]))
			return '';
		
		$dets = explode('.', $_SERVER["HTTP_HOST"]);
		
		if (count($dets) == 4) // IP
		{
			$isNum = true;
			foreach ($dets as $num)
			{
				if (!is_numeric($num))
				{
					$isNum = false;
					break;
				}
			}
			if ($isNum)
			{
				$CookieDomain = $_SERVER["HTTP_HOST"];
				$CookieDomain = preg_replace('/\:.*$/','', $CookieDomain);
				return $CookieDomain;
			}
		}
		
		$CookieDomain = '.' . $dets[(count($dets)-2)] . '.' . $dets[(count($dets)-1)];
		$CookieDomain = preg_replace('/\:.*$/','', $CookieDomain);
		return $CookieDomain;
	}
	
	public static function SetCookie($name, $value, $expire = 0, $isRaw = false)
	{
		if ($isRaw)
			setrawcookie($name, $value, $expire, '/', self::CookieDomain() );
		else
			setcookie($name, $value, $expire, '/', self::CookieDomain() );
	}

	public static function IsSSL()
	{
		return (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on');
	}

	
	public static function JSONPack($array, $as_array = array(), $array_item = false)
	{
		$print = array();
		foreach ($array as $k=>$v)
		{
			$kej = (is_numeric($k)) ? "" : "\"$k\":";
			if (is_array($v))
			{
				if (in_array($k,$as_array))
					$print[] = $kej."[" . self::JSONPack($v, $as_array, true)  . "]";
				else
					$print[] = $kej.self::JSONPack($v, $as_array);
			}
			else
			{
				$print[] = $kej."\"$v\"";
			}
		}
		$t = ($array_item) ? implode(",", $print) : "{" . implode(",", $print) . "}";
		return $t;
	}
	
	
	public static function HttpGet($url, $maxReadTimeout = 30)
	{
		$url = trim($url);
		
		$content = '';
		$DefaultTimeout = ini_set('default_socket_timeout', $maxReadTimeout);
		
		if ( $fd = @fopen($url, "r") )
		{
			while (!feof ($fd))
				$content .= fgets($fd, 4096);
//			$finfo = stream_get_meta_data($fd);
			fclose ($fd);
		}
		ini_set('default_socket_timeout', $DefaultTimeout);
		
		return $content;
	}
	
	
	public static function FileUpload($postKey, $destination, $saveName = '', $maxFileSize = 0, $allowedFormats = array())
	{
		$result = array(
			'full_path' => '',
			'filename' => '',
			'fileext' => '',
			'filesize' => 0,
			'success' => 0,
			'error' => '',
		);
		if (!isset($_FILES[$postKey]))
		{
			$result['error'] = 'Can\'t Upload File 1';
			return $result;
		}
		
		$name = $_FILES[$postKey]['name'];
		$size = $_FILES[$postKey]['size'];
		$source = $_FILES[$postKey]['tmp_name'];
		$ext = Utils::getFileExtension($name);
		
		$result['filesize'] = $size;
		$result['fileext'] = $ext;
		
		if (count($allowedFormats) > 0)
		{
			if (empty($ext) || !in_array($ext, $allowedFormats))
			{
				@unlink($source);
				$result['error'] = 'Not supported File Type';
				return $result;
			}
		}
		
		if ($maxFileSize > 0)
		{
			if ($_FILES[$postKey]['size'] > $maxFileSize)
			{
				@unlink($source);
				$result['error'] = 'File Size is Too Big';
				return $result;
			}
		}
		
		if (!file_exists($destination))
		{
			@unlink($source);
			$result['error'] = 'Destination folder not exists';
			return $result;
		}
		else if(!is_writeable($destination))
		{
			@unlink($source);
			$result['error'] = 'Can\'t copy uploaded file to destination folder - Access denied';
			return $result;
		}
		
		if (empty($saveName))
			$saveName = $name;
		else
		{
			$match = "/\.$ext$/i";
			$saveName = preg_replace($match, '', $saveName);
			$saveName .= '.' . $ext;
		}
		
		$destination .= (substr($destination, -1) != '/') ? '/' : '';
		if ( copy($source, $destination . $saveName) )
		{
			$result['success'] = 1;
			$result['filename'] = $saveName;
			$result['full_path'] = $destination . $saveName;
		}
		else
			$result['error'] = 'Can\'t copy uploaded file to destination folder';
		
		@unlink($source);
		
		return $result;
	}
	
	
	
	public static function Ddl(array $set)
	{
		$set['id'] = ( isset($set['id']) ) ? $set['id'] : null;
		$set['name'] = ( isset($set['name']) ) ? $set['name'] : '';
		$set['selected'] = ( isset($set['selected']) ) ? $set['selected'] : '';
		$set['def'] = ( isset($set['def']) ) ? $set['def'] : '';
		$set['def_plus'] = ( isset($set['def_plus']) ) ? $set['def_plus'] : '';
		$set['attrs'] = ( isset($set['attrs']) ) ? $set['attrs'] : '';
		$set['use_post'] = ( isset($set['use_post']) ) ? $set['use_post'] : '';
		$set['absolute_equal'] = ( isset($set['absolute_equal']) ) ? $set['absolute_equal'] : false;
		
		return self::DropDown(
			$set['id'],
			$set['nvp'],
			$set['selected'],
			$set['def'],
			$set['attrs'],
			$set['use_post'],
			$set['name'],
			$set['absolute_equal'],
			$set['def_plus']
		);
	}
	
	
	public static function DropDown($id, $collection, $selected_value = '', $default = '', $attributes = '', $use_post = false, $name = '', $absolute_equal = false, $default2 = '')
	{
		$name = ( empty($name) && !empty($id) ) ? $id : $name;
		
		$nd = array();
		if ( !empty($name) )
			$nd[] = 'name="'.$name.'"';
		if ( !empty($id) )
			$nd[] = 'id="'.$id.'"';
		
		
		$drop_down = '<select '.implode(' ', $nd).' ' . $attributes . '>';
		if (!empty($default))
			$drop_down .= '<option value="" class="def_">' . $default . '</option>';
		if (!empty($default2))
			$drop_down .= '<option value="">' . $default2 . '</option>';
		if (is_array($collection) && count($collection))
		{
			foreach ($collection as $value=>$option)
			{
				$selected = '';
				if ($use_post)
				{
					$selected = (isset($_POST[$id]) && strlen($_POST[$id]) && $_POST[$id] == $value) ? ' selected="selected"' : '';
				}
				else
				{
					if ( $absolute_equal )
						$selected = ($value === $selected_value) ? ' selected="selected"' : '';
					else
						$selected = ($value == $selected_value) ? ' selected="selected"' : '';
				}
				
				$option_attrs = '';
				$option_text = $option;
				if ( is_array($option) )
				{
					$option_attrs = ( isset($option['attrs']) ) ? $option['attrs'] : '';
					$option_text = $option['text'];
				}
				$drop_down .= '<option value="'.$value.'" '.$selected.' '.$option_attrs.'>'.$option_text.'</option>';
			}
		}
		$drop_down .= '</select>';
		return $drop_down;
	}
	
	public static function Pagination($Page, $Total, $OnPage, $MaxPages = 10, $Url = '', $Exclude = array(), $IsAjax = false)
	{
		$Pages = ceil($Total / $OnPage);
		
		$Page = (int)$Page;
		$Page = ($Page < 1) ? 1 : $Page;
		
		if ($IsAjax)
		{
			$Url = '#' . self::SerializeRequest('', $Exclude, ';');
		}
		else if (strpos($Url, '?') === false)
		{
			$query = self::SerializeRequest('', $Exclude, ';');
			if ($query != '') $Url .= '?' . $query;
		}
		$qa_get = (strpos($Url, '?') !== false) ? '&' : '?';
		if ($IsAjax) $qa_get = ';';
		
		$pagination = '';
		$from = ($Page - ($MaxPages / 2) > 1) ? $Page - ($MaxPages / 2) : 1;
		$to = ($Page + ($MaxPages / 2) < $Pages) ? $Page + ($MaxPages / 2) : $Pages;
	
		if ($from > 1)
			$pagination .= '<a href="'.$Url.$qa_get.'page=1" ref="1">First</a><em>...</em>';
		
		for ($i = $from; $i <= $to; $i++) 
		{
			if ($i == $Page)
				$pagination .= '<em class="cur_">' . $i . '</em>';
			else
				$pagination .= '<a href="'.$Url.$qa_get.'page='.$i.'" ref="'.$i.'">'.$i.'</a>';
		}
		if ($to < $Pages)
		{
			$pagination .= '<em>...</em><a href="'.$Url.$qa_get.'page='.$Pages.'" ref="'.$Pages.'">Last</a>';
		}
		return $pagination;
	}
	
	public static function eqDef(&$value, $def)
	{
		$tv = strtolower(trim($value));
		$td = strtolower(trim($def));
		if ($tv == $td)
			$value = '';
	}
	
	public static function FormatNvp(&$nvp, $format)
	{
		foreach ($nvp as $n=>$v)
			$nvp[$n] = str_replace('{value}', $v, $format);
	}
}

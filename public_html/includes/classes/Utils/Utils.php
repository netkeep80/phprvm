<?php

class Utils
{
	private $EncoderMapH = array();
	private $EncoderMapC = array();
	private $InviteEncodeMap = array();
	private $InviteDecodeMap = array();
	
	function Utils()
	{
		$this->EncoderMapH = array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f');
		$this->EncoderMapC = array('a','y','e','w','1','x','7','z','c','r','9','3','b','v','k','g');
		$this->InviteEncodeMap = array(0=>'A',1=>'B',2=>'C',3=>'D',4=>'E',5=>'F',6=>'G',7=>'H',8=>'I',9=>'J',10=>'K',11=>'L',12=>'M',13=>'N',14=>'O',15=>'P',16=>'Q',17=>'R',18=>'S',19=>'T',20=>'U',21=>'V',22=>'W',23=>'X',24=>'Y',25=>'Z');
		$this->InviteDecodeMap = array('A'=>0,'B'=>1,'C'=>2,'D'=>3,'E'=>4,'F'=>5,'G'=>6,'H'=>7,'I'=>8,'J'=>9,'K'=>10,'L'=>11,'M'=>12,'N'=>13,'O'=>14,'P'=>15,'Q'=>16,'R'=>17,'S'=>18,'T'=>19,'U'=>20,'V'=>21,'W'=>22,'X'=>23,'Y'=>24,'Z'=>25);
	}
	
	function EncryptData($source, $urlEncoded = false)
	{
		if (!CryptoServiceEnabled)
		{
			if ($urlEncoded)
				return urlencode($source);
			return $source;
		}
		$CryptoAlgorithm = (defined('CRYPTO_ALGORITHM')) ? CRYPTO_ALGORITHM : MCRYPT_RIJNDAEL_128;
		$CryptoService = new CryptoService($CryptoAlgorithm, MCRYPT_MODE_CBC, CRYPTO_KEY, CRYPTO_IV);

		if (!$CryptoService->status)
			return '';
		
		$result = $CryptoService->encrypt($source);
		$CryptoService->close();
		
		if ($urlEncoded)
			return urlencode($result);
		return $result;
	}

	function DecryptData($source)
	{
		if (!CryptoServiceEnabled)
			return $source;
		
		$source = (strlen($source) == 23) ? '+' . $source : $source;
		
		$CryptoAlgorithm = (defined('CRYPTO_ALGORITHM')) ? CRYPTO_ALGORITHM : MCRYPT_RIJNDAEL_128;
		$CryptoService = new CryptoService($CryptoAlgorithm, MCRYPT_MODE_CBC, CRYPTO_KEY, CRYPTO_IV);

		if (!$CryptoService->status)
			return '';
		
		$source = urldecode($source);
		$source = str_replace(' ', '+', $source);
		$source = str_replace('~', '+', $source);
		$result = $CryptoService->decrypt($source);
		$CryptoService->close();
		return $result;
	}

	function ComputeHash($source, $type = 'SHA512')
	{
		if (!CryptoServiceEnabled)
			return $source;

		$HashManager = new HashManager;

		if ($type == 'SHA512')
			return $HashManager -> HashSHA512($source);
	}

	function ObjectToArray($items)
	{
		$data = array();
		if (is_object($items))
		{
			$data = get_object_vars($items);
			return $data;
		}
		if (!is_array($items))
			return $items;

		foreach ($items as $item=>$info)
		{
			if (is_object($item))
			{
				$data[] = get_object_vars($item);
			}
			else //array
				$data[] = $info;
		}
		return $data;
	}
	
	function GetArrayKey($array, $needle)
	{
		if (!is_array($array))
			return null;
		foreach ($array as $k=>$v)
		{
			if ($k . '' == $needle)
				return $v;
			else if (is_array($v))
			{
				$rt = $this->GetArrayKey($v, $needle);
				if (is_array($rt))
					return $rt;
			}
		}
		return null;
	}
	
	// Insert element into array
	function ins2ary(&$ary, $element, $pos)
	{
		$ar1 = array_slice($ary, 0, $pos);
		$ar1[] = $element;
		$ary = array_merge($ar1, array_slice($ary, $pos));
	}
	
	
	function getFileExtension($filename)
	{
		$filename = trim($filename);
		$filename = strtolower($filename);
		
		if (empty($filename))
			return '';
		
		$parts = explode('.', $filename);
		if (count($parts) == 1)
			return '';
		
		$ext = $parts[(count($parts) - 1)];
		return $ext;
	}
	
	
	
	function NewGuid()
	{
		$Guid = array();
		$parts = array(8,4,4,4,12);
		$chars = '0123456789abcdef';
		foreach ($parts as $length)
		{
			$part = '';
			while (strlen($part) < $length)
				$part .= $chars[rand(0,strlen($chars)-1)];
			$Guid[] = $part;
		}
		return implode('-', $Guid);
	}
	
	function NewSSO()
	{
		return rand(17,65535);
	}
	
	function SSOToClient($sso)
	{
		$hexSSO = dechex($sso) . '';
		if (strlen($hexSSO) < 4)
		{
			$re = 4 - strlen($hexSSO);
			for ($i = 0; $i < $re; $i++)
				$hexSSO = '0' . $hexSSO;
		}
		return strtoupper($hexSSO);
	}
	
	function SSOFromClient($hexSSO)
	{
		if (strlen($hexSSO) < 4)
		{
			$re = 4 - strlen($hexSSO);
			for ($i = 0; $i < $re; $i++)
				$hexSSO = '0' . $hexSSO;
		}
		$SSO = hexdec($hexSSO);
		return (int)$SSO;
	}
	
	function RandomValue($length, $type = 'mixed')
	{
		$type = ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) ? 'mixed' : $type;

		$value = '';
		while (strlen($value) < $length)
		{
			$char = ($type == 'digits') ? rand(0,9) : chr(rand(0,255));

			if ($type == 'mixed' && eregi('^[a-z0-9]$', $char))
				$value .= $char;
			else if ($type == 'chars' && eregi('^[a-z]$', $char))
				$value .= $char;
			else if ($type == 'digits' && eregi('^[0-9]$', $char))
				$value .= $char;
		}
		$value = strtolower($value);
		return $value;
	}
	
	/* Encode and Decode Maps in Config */
	function InviteEncode($meberId)
	{
		$part2 = substr($meberId, -4);
		$part1 = substr($meberId, 0, (strlen($meberId) - 4));
		$letter0 = '';
		$part1 -= 200;

		if ($part1 > 625)
		{
			$letter0 = $this->InviteEncodeMap[(int)($part1 / 625)];
			$part1 = (int)($part1 % 625);
		}

		$letter1 = $this->InviteEncodeMap[(int)($part1 / 25)];
		$letter2 = $this->InviteEncodeMap[(int)($part1 % 25)];
		$free = (int)($this->InviteDecodeMap[$letter2] % 5);
		$code = $part2 . $free . $letter0 . $letter1 . $letter2;
		return $code;
	}

	function InviteDecode($code)
	{
		$code = trim($code);
		$code = strtoupper($code);
		$decode = '';

		if (preg_match('/^([0-9]+)([A-Z]+)$/', $code, $matches))
		{
			$part2 = $matches[1];
			$part1 = $matches[2];
			$part2 = substr($part2, 0, 4);

			$k = 1;
			$ls = $part1;
			$part1 = 0;
			for ($i = strlen($ls)-1; $i >= 0; $i--)
			{
				$part1 += $this->InviteDecodeMap[$ls[$i]] * $k;
				$k*=25;
			}
			$part1+=200;
			$decode = $part1.$part2;
		}
		return $decode;
	}
	
	function InviteToMrid($code)
	{
		$id = $this -> InviteDecode($code);
		if (is_numeric($id))
			return $this -> EncryptData($id);
		return '';
	}
	function MridToInvite($mrid)
	{
		$id = (int)$this -> DecryptData($mrid);
		if ($id > 0)
			return $this -> InviteEncode($id);
		return '';
	}
	
	
	/* new encoders */
	function CodeToId($Code)
	{
		$h = $this->EncoderMapH;
		$c = $this->EncoderMapC;
		$Code = trim(strtolower($Code));
		
		$Id = '';
		for ($i = 0; $i < strlen($Code); $i++)
		{
			$char_found = false;
			for ($t = 0; $t < count($c); $t++)
			{
				if ($Code[$i] == $c[$t])
				{
					$Id .= $h[$t];
					$char_found = true;
					break;
				}
			}
			if (!$char_found)
				return 0;
		}
		$Id = hexdec($Id);
		return $Id;
		
	}
	
	function IdToCode($Id)
	{
		$h = $this->EncoderMapH;
		$c = $this->EncoderMapC;
		$Id = dechex($Id) . '';
		
		$Code = '';
		for ($i = 0; $i < strlen($Id); $i++)
		{
			for ($t = 0; $t < count($h); $t++)
			{
				if ($Id[$i] == $h[$t])
				{
					$Code .= $c[$t];
					break;
				}
			}
		}
		return $Code;
	}
	
	function VersionIdentifier($version, $checkWith) 
	{
		$identifier = str_replace('.', '', $version);
		$identifier = str_replace(',', '', $identifier);
		$identifier = (int)sprintf("%-05s", $identifier);
		
		if ($identifier >= $checkWith)
			return true;
		return false;
	}
}

$Utils = new Utils;

?>
<?php

class XmlUtils
{
	
	function SOAPXMLExtract($data, $collectionName = '')
	{
		require_once DOC_ROOT . '/includes/classes/nuSoap/nusoap.php';
		$parser = new nusoap_parser($data, 'UTF-8', 'result');

		if (!is_array($parser->message))
			return array();
		if (empty($collectionName))
			return $parser->message;
		else
		{
			foreach ($parser->message as $idx => $info)
			{
				if (isset($info['result']) && is_array($info['result']))
				{
					//find array with key == $collectionName
					if (isset($info['result'][$collectionName]) && is_array($info['result'][$collectionName]))
						return $info['result'][$collectionName];
				}
			}
		}

		return array();
	}

	function XMLExtract($xml_data)
	{
		$structure = array();
		$exdata = array();
		$parser = xml_parser_create();
		xml_parse_into_struct($parser, $xml_data, $xml_elements, $xml_index);
		$item_count = 0;
		foreach($xml_elements as $element)
		{
			$tag        = isset($element["tag"]) ? strtolower($element["tag"]) : '';
			$type       = isset($element["type"]) ? $element["type"] : '';
			$value      = isset($element["value"]) ? $element["value"] : '';
			$level      = isset($element["level"]) ? $element["level"] : '';
			$attributes = isset($element["attributes"]) ? $element["attributes"] : '';

			if($type == "open")
			{
				$structure[$level] = $tag;
			}
			elseif($type == "close")
			{
				if ($structure[$level] == 'item')
					$item_count++;
				unset($structure[$level]);

			}
			else if($type == "complete")
			{
				$varname = "";
				for($i=1;$i<=count($structure);$i++)
				{
					$varname.= "[\"".$structure[$i]."\"]";
				}
				$value = preg_replace("/".preg_quote("\\")."/","\\\\\\\\\\\\\\",$value);
				$value = ereg_replace('"','\"',$value);
				$varname = str_replace('["item"]','["item"][' . $item_count . ']',$varname);
				eval("\$exdata".$varname."[\"".$tag."\"] = \"".$value."\";");
// ")
			}
		}
		return $exdata;
	}

	function SoapResponse($objectsData, $responseName, $resultName, $itemName, $barVersion = '', $addXML = false, $sendHTTPSNamespace = false)
	{
		global $WSErrorId, $WSErrors;
		$barURL = "";

		$WSError = (isset($WSErrors[$WSErrorId])) ? $WSErrors[$WSErrorId] : "";

		$barIdentifier = str_replace('.', '', $barVersion);
		$barIdentifier = str_replace(',', '', $barIdentifier);
		$barIdentifier = (int)sprintf("%-05s", $barIdentifier);
		
		if($sendHTTPSNamespace)
		{
			$barURL = "https://ws.addthealert.com/";
		}
		else if($barVersion == '2.2')
		{
			$barURL = "https://ws.addthealert.com/";
		}
		else if($GLOBALS['Utils']->VersionIdentifier($barVersion, 25000))
		{
			$barURL = "http://ws.addthealert.com/";
		}
		else
		{
			$barURL = "https://ws.addthealert.com/";
		}
		$data = (is_object($objectsData) || is_array($objectsData)) ? $GLOBALS["Utils"]->ObjectToArray($objectsData) : $objectsData;
		$responseNameAttribute = ' xmlns="' . $barURL . '" WSErrorId="' . $WSErrorId . '" WSError="' . $WSError . '"';

		$tree = array();
		$tree['soap:Body'] = array();
		if (!empty($resultName))
		{
			$tree['soap:Body'][$responseName.$responseNameAttribute] = array();
			$tree['soap:Body'][$responseName.$responseNameAttribute][$resultName] = $data;
		}
		else
		{
			$tree['soap:Body'][$responseName.$responseNameAttribute] = $data;
		}
		$this->SoapPackAndSend($tree, $itemName, $addXML); 
	}

	function SoapPackAndSend($reply, $itemName, $addXML = false)
	{
		$output = '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">' .
			$this->XMLPack($reply, $itemName) .
			'</soap:Envelope>';
		$this->XMLSend($output, $addXML);

	}//pack_and_send

	function GetNPostResponse($objectsData, $resultName, $addXML = false)
	{
		$data = (is_object($objectsData) || is_array($objectsData)) ? $GLOBALS["Utils"]->ObjectToArray($objectsData) : $objectsData;

		$tree = array();
		$tree[$resultName] = $data;

		$this->GetNPostPackAndSend($tree, '', $addXML); //XMLSend($this->XMLPack($tree));
	}

	function GetNPostPackAndSend($reply, $itemName, $addXML = false)
	{
		$output = $this->XMLPack($reply, $itemName);
		$this->XMLSend($output, $addXML);

	}//pack_and_send

	function XMLPack($reply, $itemName)
	{
		$output = '';
		foreach ($reply as $key=>$val)
		{
			if (!is_array($val) && !is_object($val) && $val === null)
			{
				$output .= '';
			}
			else if (!is_array($val) && !is_object($val) && strlen($val) == 0)
			{
				$output .= (is_numeric($key) && !empty($itemName)) ? "<".$itemName."/>" : "<".$key."/>";
			}
			else
			{
				$output .= (is_numeric($key) && !empty($itemName)) ? "<".$itemName.">" : "<".$key.">";

				if (is_array($val) || is_object($val))
					$output .= $this->XMLPack($val, $itemName);
				else
				{
					$val = ($val === 0) ? '0' : $val;
					if ($val === '0')
						$output .= '0';
					else
						$output .= (empty($val)) ? '' : htmlspecialchars($val);
				}

				$closeKey = (is_numeric($key) && !empty($itemName)) ? $itemName : $key;
				// remove attributes in close key
				if (strpos($closeKey, ' ') !== false)
				{
					$dets = explode(' ', $closeKey);
					$closeKey = $dets[0];
				}
				$output .= "</".$closeKey.">";
			}
		}

		return $output;

	}//pack

	function XMLSend($output, $encoding = 'utf-8', $addXML = false)
	{
		Header("Content-Type:text/xml; charset: ".$encoding);
		if($addXML)
			$output = '<?xml version="1.0" encoding="' .$encoding .'"?>' . $output;
		echo $output;
	}//send


	function SoapAction()
	{
		global $XML;
		global $SoapAction;
		global $HTTP_RAW_POST_DATA;
		$XML = array();
		$SoapAction = 'wsdl';
		$HTTP_RAW_POST_DATA = $this->XMLGetRaw();
		// copy into post var for detect error if occured
		$_POST['HTTP_RAW_POST_DATA_COPY'] = $HTTP_RAW_POST_DATA;
		
		if (empty($HTTP_RAW_POST_DATA))
			return;

		$XML = $this->XMLExtractEx($HTTP_RAW_POST_DATA);
		$XML = $this->_soap_body($XML);
		
		if (isset($_SERVER['HTTP_SOAPACTION']))
		{
			$dets = explode('/', $_SERVER['HTTP_SOAPACTION']);
			$SoapAction = $dets[(count($dets)-1)];
			$SoapAction = str_replace(array('\'','"'), array('',''), $SoapAction);
		}
		else
		{
			foreach ($XML as $key=>$value)
			{
				if (!is_numeric($key))
				{
					$SoapAction = $key;
					break;
				}
			}
		}

	}
	function _soap_body($xml)
	{
		foreach ($xml as $k=>$v)
		{
			$k = strtolower($k);
			if (strpos($k, ':body') !== false)
				return $v;
			if (is_array($v))
				return $this->_soap_body($v);
		}
	}

	/*
	*  other XML lib
	*  all keys not changed
	*/
	// XML to Array
	function XMLExtractEx(&$string, $collectionName = '')
	{
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($parser, $string, $vals, $index);
		xml_parser_free($parser);

		$mnary = array();
		$ary = &$mnary;
		foreach ($vals as $r)
		{
			$t = $r['tag'];
			if ($r['type']=='open')
			{
				if (isset($ary[$t]))
				{
					if (isset($ary[$t][0]))
						$ary[$t][] = array();
					else
						$ary[$t] = array($ary[$t], array());
					$cv = &$ary[$t][count($ary[$t])-1];
				}
				else
					$cv=&$ary[$t];
				if (isset($r['attributes']))
				{
					foreach ($r['attributes'] as $k=>$v)
						$cv['attributes'][$k] = $v;
				}
				$cv = array();
				$cv['_p'] = &$ary;
				$ary = &$cv;
			}
			else if ($r['type']=='complete')
			{
				if (isset($ary[$t])) // same as open
				{
					if (isset($ary[$t][0]) && is_array($ary[$t]))
						$ary[$t][] = array();
					else
						$ary[$t] = array($ary[$t], array());
					$cv = &$ary[$t][count($ary[$t])-1];
				}
				else
					$cv=&$ary[$t];
				if (isset($r['attributes']))
				{
					foreach ($r['attributes'] as $k=>$v)
						$cv['attributes'][$k] = $v;
				}
				$cv = (isset($r['value']) ? $r['value'] : '');

			}
			else if ($r['type']=='close')
			{
				$ary = &$ary['_p'];
			}
		}

		$this->_del_p($mnary);

		if (empty($collectionName))
			return $mnary;
		else
			return $this->_find_baranch($mnary, $collectionName);
	}

	function _find_baranch($tree, $find_name, $lvl = 0)
	{
		foreach ($tree as $name => $values)
		{
			if ($name == $find_name)
			{
				return $values;
			}
			else
			{
				if (is_array($values))
				{
					$rets = $this->_find_baranch($values, $find_name, ($lvl + 1));
					if (is_array($rets))
						return $rets;
				}
			}
		}
		return '';
	}

	// _Internal: Remove recursion in result array
	function _del_p(&$ary)
	{
		foreach ($ary as $k=>$v)
		{
			if ($k === '_p')
				unset($ary[$k]);
			else if (is_array($ary[$k]))
				$this->_del_p($ary[$k]);
		}
	}

	// Array to XML
	function XMLPackEx($cary, $d = 0, $forcetag = '')
	{
		$res = array();
		foreach ($cary as $tag=>$r)
		{
			if (isset($r[0]))
			{
				$res[] = $this->XMLPackEx($r, $d, $tag);
			}
			else
			{
				if ($forcetag)
					$tag=$forcetag;
				$sp = str_repeat("\t", $d);
				$res[] = "$sp<$tag";
				if (isset($r['attributes']))
				{
					foreach ($r['attributes'] as $at=>$av)
						$res[] = " $at=\"$av\"";}
					$res[] = ">" . ((isset($r['collection'])) ? "\n" : '');
					if (isset($r['collection']))
						$res[] = $this->XMLPackEx($r['collection'], $d+1);
					else if (isset($r['value']))
						$res[] = $r['value'];
					$res[] = (isset($r['collection']) ? $sp : '') . "</$tag>\n";
			}
		}
		return implode('', $res);
	}
	
}

$XmlUtils = new XmlUtils;

?>
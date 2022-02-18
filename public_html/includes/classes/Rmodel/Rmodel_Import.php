<?php

class Rmodel_Import
{
	public static function XmlToDb($file)
	{
		global $DB;
		
		require DOC_ROOT . '/includes/classes/Utils/XmlUtils.php';

		if (!file_exists($file))
			return array('Status' => 0, 'Msg' => "File '$file' not exists");
		
		$data = file_get_contents($file);
		$data = preg_replace('/^\<\?xml[^\>]+\>/','',$data);

		// for CP1251
		$data = iconv('CP1251','UTF-8',$data);

		if ( empty($data) )
			return array('Status' => 0, 'Msg' => "Unable to read file '$file'");
		$xml = $XmlUtils->XMLExtractEx($data,'Entity');

		if (!count($xml))
			return array('Status' => 0, 'Msg' => "Elements not found");
		
		$DB->execute("truncate table RModel");
		$import_cnt=0;
		foreach ($xml as $row)
		{
			$dt = array();
			if (isset($row['Ent']))
				$dt['Ent'] = (int)$row['Ent'];
			if (isset($row['Sub']))
				$dt['Sub'] = (int)$row['Sub'];
			if (isset($row['Rel']))
				$dt['Rel'] = (int)$row['Rel'];
			if (isset($row['Obj']))
				$dt['Obj'] = (int)$row['Obj'];
			if (isset($row['EntName']))
				$dt['EntName'] = $row['EntName'];
			if (isset($row['Val']))
				$dt['Val'] = $row['Val'];
			
			if (count($dt))
			{
				$DB->exec_query($dt, 'RModel');
				$import_cnt++;
			}
		}
		return array('Status' => 1, 'Msg' => "Imported $import_cnt record(s).");
	}
	
}



?>
<?

$GlobalAjaxFlag = true;
include_once 'includes/Core.php';

$action = Web::Get('get');
$action = ( empty($action) ) ? Web::Get('action') : $action;

ini_set('display_errors', 'on');
ob_start("ajax_fatal_error_handler");

Sys::Log(print_r($_POST,1), 'axcall');

$result = array();
$html = '';
switch($action)
{
	case 'add':
		// init vars
		$result['status'] = false;
		$result['message'] = '';
		
		// here you can call some class::method 
		// or ( but not good ) insert into database right here
		$EntId = Web::GetAx('EntId');
		$SubId = Web::GetAx('SubId');
		$RelId = Web::GetAx('RelId');
		$ObjId = Web::GetAx('ObjId');
		$PHPView = Web::GetAx('PHPView');
		$EngView = Web::GetAx('EngView');
		$RusView = Web::GetAx('RusView');
		
		// here can be validation
		// on error set status=false, message=error message and break
		$dti = array(
			'EntId' => $EntId,
			'SubId' => $SubId,
			'RelId' => $RelId,
			'ObjId' => $ObjId,
			'PHPView' => $PHPView,
			'EngView' => $EngView,
			'RusView' => $RusView,
		);
		$primary_key = $DB->exec_query($dti, 'rmodel');
		
		if ( $primary_key < 0 )
		{
			$result['status'] = false;
			$result['message'] = 'insert fails:'.$primary_key;
			break;
		}
		
		$result['status'] = true;
		$result['message'] = 'success added record';
		break;
		
	case 'edit':
		// init vars
		$result['status'] = false;
		$result['message'] = '';
		
		// here you can call some class::method 
		// or ( but not good ) insert into database right here
		$EntId = Web::GetAx('EntId');
		$SubId = Web::GetAx('SubId');
		$RelId = Web::GetAx('RelId');
		$ObjId = Web::GetAx('ObjId');
		$PHPView = Web::GetAx('PHPView');
		$EngView = Web::GetAx('EngView');
		$RusView = Web::GetAx('RusView');
		
		// here can be validation
		// on error set status=false, message=error message and break
		$dti = array(
			'SubId' => $SubId,
			'RelId' => $RelId,
			'ObjId' => $ObjId,
			'PHPView' => $PHPView,
			'EngView' => $EngView,
			'RusView' => $RusView,
		);
		$where = "EntId = '".$EntId."'";
		$primary_key = $DB->exec_query($dti, 'rmodel', $where);
		
		if ( $primary_key < 0 )
		{
			$result['status'] = false;
			$result['message'] = 'update fails:'.$primary_key.' where: '.$where;
			break;
		}
		
		if ( $primary_key == 0 )
		{
			$result['status'] = true;
			$result['message'] = 'No changes detected';
			break;
		}
		
		$result['status'] = true;
		$result['message'] = '';//'success update record';
		break;
		
	case 'delete':
		// init vars
		$result['status'] = false;
		$result['message'] = '';
		
		// here you can call some class::method 
		// or ( but not good ) insert into database right here
		$EntId = Web::GetAx('EntId', 'int');
		
		if ( $EntId < 0 )
		{
			$result['status'] = false;
			$result['message'] = 'Invalid EntId';
			break;
		}
		// here can be validation
		// on error set status=false, message=error message and break
		$sql_query = "delete from rmodel where EntId = '".$EntId."'";
		$primary_key = $DB->execute($sql_query);
		
		if ( $primary_key < 0 )
		{
			$result['status'] = false;
			$result['message'] = 'delete fails:'.$primary_key.' query:'.$sql_query;
			break;
		}
		
		if ( $primary_key == 0 )
		{
			$result['status'] = true;
			$result['message'] = 'No entity found with EntId:'.$EntId;
			break;
		}
		
		$result['status'] = true;
		$result['message'] = 'success delete record';
		break;
		
	case 'getentbysubid':
		// init vars
		$result['status'] = false;
		$result['message'] = '';
		
		// here you can call some class::method 
		// or ( but not good ) insert into database right here
		$EntId = Web::GetAx('id');
		
		// here can be validation
		// on error set status=false, message=error message and break
		$dtree = Rmodel::GetEntBySubId($EntId);
		$result['status'] = true;
		
		if ( !count($dtree) )
		{
			$result['html'] = '[Click to add]';
			break;
		}
		$result['html'] = '<ul class="tree" type="disc">';
		foreach ($dtree as $row)
		{
			$id = $row['Id'];
			$result['html'] .= '<li>';
			$result['html'] .= '<a href="/Entity_Edit.php?id='.$id.'">E </a>';
			$result['html'] .= '<a href="/Entity_PHPView.php?id='.$id.'">V </a>';
			$result['html'] .= '<a href="/Entity_PHPDebug.php?id='.$id.'">D </a>';
			$result['html'] .= '<a class="a-tree" ref="'.$id.'"><b>^</b></a>';
			$result['html'] .= '<a class="el" ref="'.$id.'">['.$row['EngView'].']</a>';
			$result['html'] .= '<div id="tree-place-'.$id.'"></div>';
			$result['html'] .= '</li>';
		}
		$result['html'] .= '</ul>';
		break;
		
	case 'test':
		$result['status'] = true;
		$result['message'] = 'test ok';
		break;
	
	default:
		$result['status'] = false;
		$result['message'] = '';
		
		break;
}

$json = json_encode($result);
sys::log($json, 'axcall');
echo $json;

// catch ajax errors
function ajax_fatal_error_handler($buffer)
{
	if (preg_match("|(Parse error</b>:)(.+)(<br)|", $buffer, $regs) ) 
	{
		Sys::Log($buffer, 'fatalax');
		return '{"status":false,"message":"Internal Error"}';
	}
	if (preg_match("|(Fatal error</b>:)(.+)(<br)|", $buffer, $regs) ) 
	{
		Sys::Log($buffer, 'fatalax');
		return '{"status":false,"message":"Internal Error"}';
	}
	return $buffer;
}

ob_end_flush();
